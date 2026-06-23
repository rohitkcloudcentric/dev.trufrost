<?php

/**
 * API Handler for Salesforce Integration
 * This file handles all Salesforce API calls securely from the server side
 */

// ==========================================
// LOAD WORDPRESS CORE (OR MOCK FOR STANDALONE)
// ==========================================
// Find wp-load.php to load WordPress core
$wp_load_path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';

if (!file_exists($wp_load_path)) {
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
}

if (!file_exists($wp_load_path)) {
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/dev.trufrost/wp-load.php';
}

if (!file_exists($wp_load_path)) {
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
}

if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
    global $wpdb;
} else {
    // Standalone / local testing mockup for WordPress database using PHP Sessions
    class MockWPDB
    {
        public $last_error = '';

        public function prepare($query, ...$args)
        {
            $query = str_replace('%s', "'%s'", $query);
            return vsprintf($query, $args);
        }

        public function query($query)
        {
            return true;
        }

        public function get_var($query)
        {
            if (strpos($query, 'COUNT(*)') !== false) {
                if (!isset($_SESSION['mock_otp_db'])) {
                    return 0;
                }
                preg_match_all("/'([^']+)'/", $query, $matches);
                $strings = $matches[1] ?? [];

                $count = 0;
                foreach ($_SESSION['mock_otp_db'] as $row) {
                    if (strpos($query, 'mobile_number =') !== false) {
                        $matched_mobile = false;
                        foreach ($strings as $str) {
                            if (strlen($str) === 10 && preg_match('/^[0-9]+$/', $str) && $row['mobile_number'] === $str) {
                                $matched_mobile = true;
                                break;
                            }
                        }
                        if (!$matched_mobile) continue;
                    }
                    if ($row['is_used'] == 0) {
                        $count++;
                    }
                }
                return $count;
            }
            return 0; // Bypass rate limiting
        }

        public function get_row($query)
        {
            if (!isset($_SESSION['mock_otp_db'])) {
                $_SESSION['mock_otp_db'] = [];
            }

            // Extract single quoted strings from the query
            preg_match_all("/'([^']+)'/", $query, $matches);
            $strings = $matches[1] ?? [];

            $is_verified_required = null;
            if (preg_match('/is_verified\s*=\s*(0|1)/i', $query, $m)) {
                $is_verified_required = (int)$m[1];
            }
            $is_used_required = null;
            if (preg_match('/is_used\s*=\s*(0|1)/i', $query, $m)) {
                $is_used_required = (int)$m[1];
            }

            foreach ($_SESSION['mock_otp_db'] as $row) {
                // If query checks for mobile_number, does it match?
                if (strpos($query, 'mobile_number =') !== false) {
                    $matched_mobile = false;
                    foreach ($strings as $str) {
                        if (strlen($str) === 10 && preg_match('/^[0-9]+$/', $str) && $row['mobile_number'] === $str) {
                            $matched_mobile = true;
                            break;
                        }
                    }
                    if (!$matched_mobile) continue;
                }

                // If query checks for access_token, does it match?
                if (strpos($query, 'access_token =') !== false) {
                    $matched_token = false;
                    foreach ($strings as $str) {
                        if (strlen($str) === 64 && $row['access_token'] === $str) {
                            $matched_token = true;
                            break;
                        }
                    }
                    if (!$matched_token) continue;
                }

                // Check is_verified
                if ($is_verified_required !== null && $row['is_verified'] != $is_verified_required) {
                    continue;
                }

                // Check is_used
                if ($is_used_required !== null && $row['is_used'] != $is_used_required) {
                    continue;
                }

                return (object)$row;
            }
            return null;
        }

        public function insert($table, $data, $format = null)
        {
            if (!isset($_SESSION['mock_otp_db'])) {
                $_SESSION['mock_otp_db'] = [];
            }
            $data['id'] = count($_SESSION['mock_otp_db']) + 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['attempts'] = 0;
            $_SESSION['mock_otp_db'][] = $data;
            return true;
        }

        public function update($table, $data, $where, $format = null, $where_format = null)
        {
            if (!isset($_SESSION['mock_otp_db'])) {
                return false;
            }
            $updated = false;
            foreach ($_SESSION['mock_otp_db'] as &$row) {
                $match = true;
                foreach ($where as $k => $v) {
                    if (!isset($row[$k]) || $row[$k] != $v) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    foreach ($data as $k => $v) {
                        $row[$k] = $v;
                    }
                    $updated = true;
                }
            }
            return $updated;
        }
    }

    $GLOBALS['wpdb'] = new MockWPDB();
    $wpdb = $GLOBALS['wpdb'];
}

// Configure session settings BEFORE starting session
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
ini_set('session.cookie_lifetime', 1800); // 30 minutes
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display_errors to prevent HTML in JSON response
ini_set('log_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Salesforce Configuration
define('SF_BASE_URL', 'https://trufrostandbutler--qa.sandbox.lightning.force.com');
define('SF_API_URL', 'https://trufrostandbutler--qa.sandbox.my.salesforce.com');
define('SF_GRANT_TYPE', 'client_credentials');
define('SF_CLIENT_ID', '3MVG9LQU2EgIG3GDe3NLi5g2PkBG2WXN3Xevy9qCE0qCyVTnveY4NiwodJW2vlmZhfZ9qj0ktpOfVVAS9Qiwv');
define('SF_CLIENT_SECRET', '7F7450008D27AE1220C78FF2D345B59CB34F5C07A04FF9AD712D31BB275E5FB8');

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Database table name (without prefix since it's already created)
define('OTP_TABLE', 'otp_verifications');

// API Log directory
define('API_LOG_DIR', __DIR__ . '/APILog');

/**
 * Log API request/response to JSON file
 */
function log_api_response($prefix, $data, $reqId)
{
    try {
        // Ensure APILog directory exists
        if (!file_exists(API_LOG_DIR)) {
            mkdir(API_LOG_DIR, 0755, true);
        }

        // Generate filename with timestamp and request ID
        $timestamp = date('Y-m-d_H-i-s');
        $filename = $prefix . '_' . $reqId . '_' . $timestamp . '.json';
        $filepath = API_LOG_DIR . '/' . $filename;

        // Prepare log data
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => $reqId,
            'prefix' => $prefix,
            'data' => $data
        ];

        // Write to JSON file
        $jsonContent = json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($filepath, $jsonContent);

        error_log('API Log saved: ' . $filepath);
        return $filepath;
    } catch (Exception $e) {
        error_log('Failed to log API response: ' . $e->getMessage());
        return false;
    }
}

/**
 * Generate a secure access token
 */
function generateAccessToken()
{
    return bin2hex(random_bytes(32));
}

/**
 * Clean up expired OTP records
 */
function cleanupExpiredOTPs()
{
    global $wpdb;
    $wpdb->query($wpdb->prepare("DELETE FROM " . OTP_TABLE . " WHERE expires_at < %s", date('Y-m-d H:i:s')));
}

/**
 * Check if verification session is valid
 */
function checkVerificationSession()
{
    global $wpdb;

    error_log('=== Checking Verification Session ===');
    error_log('Session Status: ' . session_status());
    error_log('Session ID: ' . session_id());
    error_log('Session Data: ' . json_encode($_SESSION));

    if (!isset($_SESSION['service_request_token']) || !isset($_SESSION['verified_mobile'])) {
        error_log('✗ No session token or verified mobile found');
        return false;
    }

    $token = $_SESSION['service_request_token'];
    $mobile = $_SESSION['verified_mobile'];
    $current_time = date('Y-m-d H:i:s');

    error_log('Token: ' . substr($token, 0, 20) . '...');
    error_log('Mobile: ' . $mobile);

    // Check if token exists and is still valid
    $verification = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . OTP_TABLE . " 
        WHERE access_token = %s 
        AND mobile_number = %s 
        AND is_verified = 1 
        AND is_used = 0 
        AND expires_at > %s",
        $token,
        $mobile,
        $current_time
    ));

    if ($verification) {
        error_log('✓ Session is valid');
        error_log('Salesforce Token: ' . (isset($verification->salesforce_token) ? substr($verification->salesforce_token, 0, 20) . '...' : 'NULL'));

        // Update session timeout
        $_SESSION['session_start'] = time();
        return [
            'valid' => true,
            'mobile' => $mobile,
            'salesforce_token' => $verification->salesforce_token
        ];
    }

    error_log('✗ Session verification failed - record not found or expired');
    error_log('Query check: access_token = ' . $token . ' AND mobile_number = ' . $mobile . ' AND expires_at > ' . $current_time);

    // Invalid or expired - clear session
    unset($_SESSION['service_request_token']);
    unset($_SESSION['verified_mobile']);
    unset($_SESSION['salesforce_token']);

    return false;
}

/**
 * Get OAuth Access Token from Salesforce
 * Using Client Credentials OAuth Flow
 */
function getOAuthToken()
{
    // IMPORTANT: Use the API URL (my.salesforce.com), not the base URL (lightning.force.com)
    $url = SF_API_URL . '/services/oauth2/token';

    // Client credentials flow - only requires client_id and client_secret
    $postData = [
        'grant_type' => SF_GRANT_TYPE,
        'client_id' => SF_CLIENT_ID,
        'client_secret' => SF_CLIENT_SECRET
    ];

    error_log('=== OAuth Token Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload: ' . json_encode($postData));
    error_log('Grant Type: ' . SF_GRANT_TYPE);
    error_log('Client ID: ' . substr(SF_CLIENT_ID, 0, 20) . '...');

    // Generate request ID for logging
    $reqId = substr(md5(json_encode($postData) . microtime(true)), 0, 10);

    // Log OAuth request
    $requestPath = log_api_response('1_salesforce_oauth_request', $postData, $reqId);
    if ($requestPath) {
        error_log('Salesforce OAuth request saved: ' . $requestPath);
    }

    $ch = curl_init();

    // Set URL (NO parameters in URL)
    curl_setopt($ch, CURLOPT_URL, $url);

    // Set POST method
    curl_setopt($ch, CURLOPT_POST, true);

    // Set POST data in body (CRITICAL!)
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    // Return response as string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // SSL settings
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // Timeout settings
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    // Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

    curl_close($ch);

    // Log response
    error_log('HTTP Code: ' . $httpCode);
    error_log('Effective URL: ' . $effectiveUrl);
    error_log('cURL Error: ' . ($curlError ? $curlError : 'None'));
    error_log('Response HTTP Code: ' . $httpCode);
    error_log('Response Length: ' . strlen($response));
    error_log('Response (Full): ' . $response);
    error_log('Response Preview: ' . substr($response, 0, 200));

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'effective_url' => $effectiveUrl,
        'response' => $response,
        'response_length' => strlen($response)
    ];

    // Log OAuth response
    $responsePath = log_api_response('1_salesforce_oauth_response', $responseData, $reqId);
    if ($responsePath) {
        error_log('Salesforce OAuth response saved: ' . $responsePath);
    }

    // Parse response
    if ($httpCode === 200 && !empty($response)) {
        $data = json_decode($response, true);

        if (is_array($data) && isset($data['access_token'])) {
            error_log('✓ OAuth Success - Token obtained');
            error_log('Token: ' . substr($data['access_token'], 0, 50) . '...');
            return $data['access_token'];
        } else {
            error_log('✗ OAuth Failed - No access_token in response');
            error_log('Response Data: ' . json_encode($data));
            return null;
        }
    } else {
        error_log('✗ OAuth Failed - HTTP ' . $httpCode);
        if (!empty($response)) {
            $data = json_decode($response, true);
            if (is_array($data)) {
                error_log('Error Details: ' . json_encode($data));
            }
        }
        return null;
    }
}

/**
 * Send OTP using WhatsApp (Meta Graph API)
 */
function sendWhatsAppOTP($mobileNumber, $otpCode)
{
    $graphUrl = defined('META_GRAPH_URL') ? META_GRAPH_URL : 'https://graph.facebook.com';
    $apiVersion = defined('META_API_VERSION') ? META_API_VERSION : 'v19.0';
    $phoneNumberId = defined('META_PHONE_NUMBER_ID') ? META_PHONE_NUMBER_ID : '';
    $accessToken = defined('META_USER_ACCESS_TOKEN') ? META_USER_ACCESS_TOKEN : '';
    $appSecret = defined('META_APP_SECRET') ? META_APP_SECRET : '';

    if (empty($phoneNumberId) || empty($accessToken)) {
        error_log('WhatsApp Configuration Missing in wp-config.php');
        return [
            'success' => false,
            'message' => 'WhatsApp API details are not fully configured in wp-config.php'
        ];
    }

    // Format number (E.164 without +)
    $phone = preg_replace('/\D+/', '', $mobileNumber);
    if (strlen($phone) === 10) {
        $phone = '91' . $phone; // default India code
    }

    $url = "{$graphUrl}/{$apiVersion}/{$phoneNumberId}/messages";

    // Add appsecret_proof for enhanced security if app secret is provided
    if (!empty($appSecret)) {
        $appsecretProof = hash_hmac('sha256', $accessToken, $appSecret);
        $url .= '?appsecret_proof=' . $appsecretProof;
    }

    $payload = [
        "messaging_product" => "whatsapp",
        "to" => $phone,
        "type" => "template",
        "template" => [
            "name" => "otp",
            "language" => [
                "code" => "en_US"
            ],
            "components" => [
                [
                    "type" => "body",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => (string)$otpCode
                        ]
                    ]
                ],
                [
                    "type" => "button",
                    "sub_type" => "url",
                    "index" => "0",
                    "parameters" => [
                        [
                            "type" => "text",
                            "text" => (string)$otpCode
                        ]
                    ]
                ]
            ]
        ]
    ];

    $headers = [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log('WhatsApp OTP Send Curl Error: ' . $curlError);
        return [
            'success' => false,
            'message' => 'Network error sending WhatsApp OTP.'
        ];
    }

    $responseDecoded = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        error_log('WhatsApp OTP Sent Successfully to ' . $phone . '. Response: ' . $response);
        return [
            'success' => true,
            'message' => 'OTP sent successfully on WhatsApp!',
            'meta_response' => $responseDecoded
        ];
    } else {
        error_log('WhatsApp OTP Send Failed. HTTP Code: ' . $httpCode . ' Response: ' . $response);
        return [
            'success' => false,
            'message' => 'Failed to send WhatsApp OTP: ' . ($responseDecoded['error']['message'] ?? 'Unknown API Error'),
            'meta_error' => $responseDecoded
        ];
    }
}

/**
 * Send OTP to mobile number
 */
function sendOTP($mobileNumber)
{
    global $wpdb;

    // Clean up expired OTPs
    cleanupExpiredOTPs();

    // Check for rate limiting (max 5 OTPs per mobile per hour)
    $recent_otps = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM " . OTP_TABLE . " 
        WHERE mobile_number = %s 
        AND created_at > %s
        AND is_used = 0",
        $mobileNumber,
        date('Y-m-d H:i:s', strtotime('-1 hour'))
    ));

    if ($recent_otps >= 5) {
        return [
            'success' => false,
            'message' => 'Too many OTP requests. Please try again after 1 hour.'
        ];
    }

    // Generate a random 4-digit OTP
    $otp = rand(1000, 9999);

    // Generate access token
    $accessToken = generateAccessToken();

    // Calculate expiration (10 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Check if there's an existing OTP for this mobile number (verified or unverified, but not used)
    $existingOtp = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . OTP_TABLE . " 
        WHERE mobile_number = %s 
        AND is_used = 0 
        AND expires_at > %s
        ORDER BY created_at DESC 
        LIMIT 1",
        $mobileNumber,
        date('Y-m-d H:i:s')
    ));

    if ($existingOtp) {
        // Update existing OTP record instead of creating a new one
        $updated = $wpdb->update(
            OTP_TABLE,
            [
                'otp_code' => $otp,
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'attempts' => 0,
                'is_verified' => 0,
                'is_used' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ],
            ['id' => $existingOtp->id],
            ['%s', '%s', '%s', '%d', '%s'],
            ['%d']
        );

        if (!$updated) {
            error_log('Failed to update OTP: ' . $wpdb->last_error);
            return [
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.'
            ];
        }

        error_log('OTP updated for ' . $mobileNumber . ': ' . $otp);
    } else {
        // Insert new OTP record
        $inserted = $wpdb->insert(
            OTP_TABLE,
            [
                'mobile_number' => $mobileNumber,
                'otp_code' => $otp,
                'access_token' => $accessToken,
                'expires_at' => $expiresAt,
                'is_verified' => 0,
                'is_used' => 0,
                'attempts' => 0
            ],
            ['%s', '%s', '%s', '%s', '%d', '%d', '%d']
        );

        if (!$inserted) {
            error_log('Failed to insert OTP: ' . $wpdb->last_error);
            return [
                'success' => false,
                'message' => 'Failed to generate OTP. Please try again.'
            ];
        }

        error_log('OTP generated for ' . $mobileNumber . ': ' . $otp);
    }

    // Integrate with WhatsApp API to send OTP
    $whatsappResult = sendWhatsAppOTP($mobileNumber, $otp);

    if (!$whatsappResult['success']) {
        return [
            'success' => false,
            'message' => $whatsappResult['message']
        ];
    }

    return [
        'success' => true,
        'message' => 'OTP sent successfully on WhatsApp!',
        'otp' => $otp // Keep for testing as configured in frontend
    ];
}

/**
 * Verify OTP
 */
function verifyOTP($mobileNumber, $otp)
{
    global $wpdb;

    // Find the most recent OTP for this mobile number
    $verification = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM " . OTP_TABLE . " 
        WHERE mobile_number = %s 
        AND is_verified = 0 
        AND is_used = 0 
        AND expires_at > %s 
        ORDER BY created_at DESC 
        LIMIT 1",
        $mobileNumber,
        date('Y-m-d H:i:s')
    ));

    if (!$verification) {
        return [
            'success' => false,
            'message' => 'OTP not found or expired. Please request a new one.'
        ];
    }

    // Check attempts (max 5 attempts)
    if ($verification->attempts >= 5) {
        return [
            'success' => false,
            'message' => 'Too many failed attempts. Please request a new OTP.'
        ];
    }

    // Verify OTP
    if ($verification->otp_code != $otp) {
        // Increment attempts
        $wpdb->update(
            OTP_TABLE,
            ['attempts' => $verification->attempts + 1],
            ['id' => $verification->id],
            ['%d'],
            ['%d']
        );

        $remaining = 5 - ($verification->attempts + 1);
        return [
            'success' => false,
            'message' => 'Invalid OTP. ' . $remaining . ' attempts remaining.'
        ];
    }

    // OTP is correct - mark as verified
    $wpdb->update(
        OTP_TABLE,
        ['is_verified' => 1],
        ['id' => $verification->id],
        ['%d'],
        ['%d']
    );

    // Set session variables
    $_SESSION['service_request_token'] = $verification->access_token;
    $_SESSION['verified_mobile'] = $mobileNumber;
    $_SESSION['session_start'] = time();

    // Explicitly save session
    session_write_close();
    session_start();

    error_log('OTP verified successfully for: ' . $mobileNumber);
    error_log('Session saved with token: ' . substr($verification->access_token, 0, 20) . '...');

    return [
        'success' => true,
        'message' => 'OTP verified successfully',
        'access_token' => $verification->access_token
    ];
}

/**
 * Search mobile number in Salesforce
 */
function searchMobile($mobileNumber, $accessToken)
{
    $url = SF_API_URL . '/services/apexrest/web_mobileSearch';

    $requestData = ['mobileNumber' => $mobileNumber];
    $data = json_encode($requestData);

    error_log('=== Mobile Search Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload: ' . $data);
    error_log('Mobile Number: ' . $mobileNumber);
    error_log('Access Token: ' . substr($accessToken, 0, 50) . '...');

    // Generate request ID for logging
    $reqId = substr(md5(json_encode($requestData) . microtime(true)), 0, 10);

    // Log mobile search request
    $requestPath = log_api_response('2_salesforce_mobile_search_request', $requestData, $reqId);
    if ($requestPath) {
        error_log('Salesforce mobile search request saved: ' . $requestPath);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    error_log('HTTP Code: ' . $httpCode);
    error_log('Effective URL: ' . $effectiveUrl);
    error_log('cURL Error: ' . ($curlError ? $curlError : 'None'));
    error_log('Response HTTP Code: ' . $httpCode);
    error_log('Response Length: ' . strlen($response));
    error_log('Response (Full): ' . $response);
    error_log('Response (Preview): ' . substr($response, 0, 500));

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'effective_url' => $effectiveUrl,
        'response' => $response,
        'response_length' => strlen($response)
    ];

    // Log mobile search response
    $responsePath = log_api_response('2_salesforce_mobile_search_response', $responseData, $reqId);
    if ($responsePath) {
        error_log('Salesforce mobile search response saved: ' . $responsePath);
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        error_log('✓ Mobile Search Success');
        return $result;
    }

    error_log('✗ Mobile Search Failed - HTTP ' . $httpCode);
    return [
        'success' => false,
        'message' => 'Failed to search mobile number',
        'httpCode' => $httpCode,
        'response' => $response
    ];
}

/**
 * Get all products from Salesforce
 */
function getProducts($accessToken)
{
    $url = SF_API_URL . '/services/apexrest/web_sendProduct';
    $requestData = []; // Empty request body
    $payload = '{}'; // Empty JSON body

    error_log('=== Get Products Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload: ' . $payload);
    error_log('Access Token: ' . substr($accessToken, 0, 50) . '...');

    // Generate request ID for logging
    $reqId = substr(md5(json_encode($requestData) . microtime(true)), 0, 10);

    // Log get products request
    $requestPath = log_api_response('3_salesforce_get_products_request', $requestData, $reqId);
    if ($requestPath) {
        error_log('Salesforce get products request saved: ' . $requestPath);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Empty body
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    error_log('HTTP Code: ' . $httpCode);
    error_log('Effective URL: ' . $effectiveUrl);
    error_log('cURL Error: ' . ($curlError ? $curlError : 'None'));
    error_log('Response HTTP Code: ' . $httpCode);
    error_log('Response Length: ' . strlen($response));
    error_log('Response Preview: ' . substr($response, 0, 500));

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'effective_url' => $effectiveUrl,
        'response' => $response,
        'response_length' => strlen($response)
    ];

    // Log get products response
    $responsePath = log_api_response('3_salesforce_get_products_response', $responseData, $reqId);
    if ($responsePath) {
        error_log('Salesforce get products response saved: ' . $responsePath);
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        error_log('✓ Get Products Success');
        return $result;
    }

    error_log('✗ Get Products Failed - HTTP ' . $httpCode);
    return [
        'success' => false,
        'message' => 'Failed to get products',
        'httpCode' => $httpCode
    ];
}

/**
 * Register Case, Account, and Asset in Salesforce
 */
function registerCaseAccountAsset($data, $accessToken)
{
    $url = SF_API_URL . '/services/apexrest/web_RegisterCaseAccountAsset';

    $postData = json_encode($data);

    error_log('=== Register Case/Account/Asset Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload (Full): ' . $postData);
    error_log('API Payload (Preview): ' . substr($postData, 0, 200) . '...');
    error_log('Access Token: ' . substr($accessToken, 0, 50) . '...');

    // Generate request ID for logging
    $reqId = substr(md5(json_encode($data) . microtime(true)), 0, 10);

    // Log register case/account/asset request
    $requestPath = log_api_response('4_salesforce_register_case_request', $data, $reqId);
    if ($requestPath) {
        error_log('Salesforce register case request saved: ' . $requestPath);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    error_log('HTTP Code: ' . $httpCode);
    error_log('Effective URL: ' . $effectiveUrl);
    error_log('cURL Error: ' . ($curlError ? $curlError : 'None'));
    error_log('Response HTTP Code: ' . $httpCode);
    error_log('Response Length: ' . strlen($response));
    error_log('Response (Full): ' . $response);
    error_log('Response (Preview): ' . substr($response, 0, 500));

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'effective_url' => $effectiveUrl,
        'response' => $response,
        'response_length' => strlen($response)
    ];

    // Log register case/account/asset response
    $responsePath = log_api_response('4_salesforce_register_case_response', $responseData, $reqId);
    if ($responsePath) {
        error_log('Salesforce register case response saved: ' . $responsePath);
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        error_log('✓ Register Case/Account/Asset Success');
        return $result;
    }

    error_log('✗ Register Case/Account/Asset Failed - HTTP ' . $httpCode);
    return [
        'success' => false,
        'message' => 'Failed to register case/account/asset',
        'httpCode' => $httpCode,
        'response' => $response
    ];
}

/**
 * Get Service Request Status
 */
function getServiceRequestStatus($mobileNumber, $accessToken)
{
    $url = SF_API_URL . '/services/apexrest/web_serviceRequestStatus';

    $requestData = ['mobileNumber' => $mobileNumber];
    $data = json_encode($requestData);

    error_log('=== Service Request Status Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload: ' . $data);
    error_log('Mobile Number: ' . $mobileNumber);
    error_log('Access Token: ' . substr($accessToken, 0, 50) . '...');

    // Generate request ID for logging
    $reqId = substr(md5(json_encode($requestData) . microtime(true)), 0, 10);

    // Log service request status request
    $requestPath = log_api_response('5_salesforce_service_status_request', $requestData, $reqId);
    if ($requestPath) {
        error_log('Salesforce service status request saved: ' . $requestPath);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development only
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    error_log('HTTP Code: ' . $httpCode);
    error_log('Effective URL: ' . $effectiveUrl);
    error_log('cURL Error: ' . ($curlError ? $curlError : 'None'));
    error_log('Response HTTP Code: ' . $httpCode);
    error_log('Response Length: ' . strlen($response));
    error_log('Response (Full): ' . $response);
    error_log('Response (Preview): ' . substr($response, 0, 500));

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'effective_url' => $effectiveUrl,
        'response' => $response,
        'response_length' => strlen($response)
    ];

    // Log service request status response
    $responsePath = log_api_response('5_salesforce_service_status_response', $responseData, $reqId);
    if ($responsePath) {
        error_log('Salesforce service status response saved: ' . $responsePath);
    }

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        error_log('✓ Service Request Status Success');
        return $result;
    }

    error_log('✗ Service Request Status Failed - HTTP ' . $httpCode);
    return [
        'success' => false,
        'message' => 'Failed to get service request status',
        'httpCode' => $httpCode
    ];
}

/**
 * Search GST or PAN in Salesforce
 */
function searchGSTPAN($gstinOrPan, $accessToken)
{
    $url = SF_API_URL . '/services/apexrest/web_searchByGSTPAN';

    $requestData = ['gstinOrPan' => $gstinOrPan];
    $data = json_encode($requestData);

    error_log('=== GST/PAN Search Request ===');
    error_log('Base URL: ' . SF_API_URL);
    error_log('Full Endpoint URL: ' . $url);
    error_log('Request Method: POST');
    error_log('API Payload: ' . $data);

    $reqId = substr(md5(json_encode($requestData) . microtime(true)), 0, 10);

    // Log request
    log_api_response('6_salesforce_gstpan_search_request', $requestData, $reqId);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log('Response HTTP Code: ' . $httpCode);

    // Prepare response data for logging
    $responseData = [
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'response' => $response
    ];
    log_api_response('6_salesforce_gstpan_search_response', $responseData, $reqId);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }

    return [
        'success' => false,
        'message' => 'Failed to search GST/PAN',
        'httpCode' => $httpCode,
        'response' => $response
    ];
}

// Main request handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'checkSession':
            $session = checkVerificationSession();

            if ($session && $session['valid']) {
                // Fast validation - return session validity immediately without fetching customer data
                // Customer data will be fetched separately to avoid slow Salesforce API calls
                echo json_encode([
                    'success' => true,
                    'verified' => true,
                    'mobile' => $session['mobile'],
                    'hasToken' => !empty($session['salesforce_token'] ?? $_SESSION['salesforce_token'] ?? null),
                    'customerData' => null  // Fetch separately to avoid 4-5 second delay
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'verified' => false
                ]);
            }
            break;

        case 'getCustomerData':
            // Separate API call to fetch customer data without blocking session validation
            $session = checkVerificationSession();

            if ($session && $session['valid']) {
                $salesforceToken = $session['salesforce_token'] ?? $_SESSION['salesforce_token'] ?? null;
                $mobile = $session['mobile'];

                if ($salesforceToken && $mobile) {
                    $mobileData = searchMobile($mobile, $salesforceToken);

                    echo json_encode([
                        'success' => true,
                        'customerData' => $mobileData
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'customerData' => null
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Session invalid'
                ]);
            }
            break;

        case 'sendOTP':
            $mobileNumber = $input['mobileNumber'] ?? '';
            if (empty($mobileNumber) || !preg_match('/^[0-9]{10}$/', $mobileNumber)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid mobile number'
                ]);
                exit;
            }

            $result = sendOTP($mobileNumber);
            echo json_encode($result);
            break;

        case 'verifyOTP':
            $mobileNumber = $input['mobileNumber'] ?? '';
            $otp = $input['otp'] ?? '';

            if (empty($mobileNumber) || empty($otp)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mobile number and OTP are required'
                ]);
                exit;
            }

            $result = verifyOTP($mobileNumber, $otp);

            // If OTP is verified, get OAuth token and search mobile
            if ($result['success']) {
                error_log('OTP verified successfully for: ' . $mobileNumber);

                $accessToken = getOAuthToken();

                if ($accessToken) {
                    error_log('OAuth token obtained, calling mobile search API');
                    $mobileData = searchMobile($mobileNumber, $accessToken);
                    $result['customerData'] = $mobileData;

                    // Store Salesforce token in database
                    $wpdb->update(
                        OTP_TABLE,
                        ['salesforce_token' => $accessToken],
                        ['access_token' => $result['access_token']],
                        ['%s'],
                        ['%s']
                    );

                    // Store access token in session for later use
                    $_SESSION['salesforce_token'] = $accessToken;
                } else {
                    error_log('Failed to obtain OAuth token');
                    $result['success'] = false;
                    $result['message'] = 'Failed to authenticate with Salesforce';
                    $result['debug'] = 'Check PHP error logs for OAuth details';
                }
            }

            echo json_encode($result);
            break;

        case 'getProducts':
            $accessToken = $_SESSION['salesforce_token'] ?? null;

            if (!$accessToken) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No access token found. Please verify OTP first.'
                ]);
                exit;
            }

            $result = getProducts($accessToken);
            echo json_encode($result);
            break;

        case 'submitServiceRequest':
            $accessToken = $_SESSION['salesforce_token'] ?? null;

            if (!$accessToken) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No access token found. Please verify OTP first.'
                ]);
                exit;
            }

            $formData = $input['formData'] ?? [];

            if (empty($formData)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Form data is required'
                ]);
                exit;
            }

            // Validate GST and PAN if provided
            if (isset($formData['gstin']) || isset($formData['pan'])) {
                $gstin = $formData['gstin'] ?? '';
                $pan = $formData['pan'] ?? '';

                // GST Validation
                if ($gstin) {
                    $gstRegex = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/';
                    if (!preg_match($gstRegex, strtoupper($gstin))) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid GST Number format. Required: 15 characters (e.g., 22AAAAA0000A1Z5)'
                        ]);
                        exit;
                    }
                    $formData['gstin'] = strtoupper($gstin);
                }

                // PAN Validation
                if ($pan) {
                    $panRegex = '/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
                    if (!preg_match($panRegex, strtoupper($pan))) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Invalid PAN Number format. Required: 10 characters (e.g., ABCDE1234F)'
                        ]);
                        exit;
                    }
                    $formData['pan'] = strtoupper($pan);
                }

                // Validate PAN matches GST (positions 3-12 of GST should match PAN)
                if ($gstin && $pan) {
                    $gstPan = strtoupper(substr($gstin, 2, 10));
                    $panUpper = strtoupper($pan);

                    if ($gstPan !== $panUpper) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'PAN Number does not match the PAN embedded in GST Number (positions 3-12)'
                        ]);
                        exit;
                    }
                }
            }

            $result = registerCaseAccountAsset($formData, $accessToken);

            // Note: We do NOT clear the session here because multiple product cards
            // may submit in parallel (Promise.all). Session cleanup happens naturally
            // via the 30-minute session timeout, or the user can start over by reloading.

            echo json_encode($result);
            break;

        case 'getServiceRequestStatus':
            $accessToken = $_SESSION['salesforce_token'] ?? null;
            $mobileNumber = $input['mobileNumber'] ?? '';

            if (!$accessToken) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No access token found. Please verify OTP first.'
                ]);
                exit;
            }

            if (empty($mobileNumber)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Mobile number is required'
                ]);
                exit;
            }

            $result = getServiceRequestStatus($mobileNumber, $accessToken);
            echo json_encode($result);
            break;

        case 'searchByGSTPAN':
            $accessToken = $_SESSION['salesforce_token'] ?? null;
            $gstinOrPan = $input['gstinOrPan'] ?? '';

            if (!$accessToken) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No access token found. Please verify OTP first.'
                ]);
                exit;
            }

            if (empty($gstinOrPan)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'GSTIN or PAN is required'
                ]);
                exit;
            }

            $result = searchGSTPAN($gstinOrPan, $accessToken);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
