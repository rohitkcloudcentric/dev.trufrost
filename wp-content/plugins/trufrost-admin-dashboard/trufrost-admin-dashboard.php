<?php
/*
Plugin Name: Trufrost Admin Dashboard
Description: Advanced Admin Dashboard module to manage OTP Verifications and Service Requests.
Version: 1.0
Author: Antigravity
Author URI: https://github.com/google-deepmind
License: GPL2
*/

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// Define Constants
define('TRUFROST_DASHBOARD_DIR', plugin_dir_path(__FILE__));
define('TRUFROST_DASHBOARD_URL', plugin_dir_url(__FILE__));

/**
 * Register Admin Menu Page
 */
add_action('admin_menu', 'trufrost_admin_dashboard_menu');
function trufrost_admin_dashboard_menu()
{
    add_menu_page(
        __('Trufrost Dashboard', 'trufrost-admin-dashboard'),
        __('Trufrost CRM', 'trufrost-admin-dashboard'),
        'manage_options',
        'trufrost-dashboard',
        'trufrost_admin_dashboard_page',
        'dashicons-shield',
        30
    );
}

/**
 * Enqueue Styles and Scripts only on the Trufrost Dashboard page
 */
add_action('admin_enqueue_scripts', 'trufrost_admin_dashboard_enqueue_assets');
function trufrost_admin_dashboard_enqueue_assets($hook)
{
    // Only load on our plugin dashboard page
    if ($hook !== 'toplevel_page_trufrost-dashboard') {
        return;
    }

    // Bootstrap 5 and Icons from CDN (Standard for clean styling)
    wp_enqueue_style('bootstrap-5-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3');
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css', array(), '1.11.3');

    // Custom CSS
    wp_enqueue_style('trufrost-dashboard-css', TRUFROST_DASHBOARD_URL . 'assets/css/dashboard.css', array('bootstrap-5-css'), filemtime(TRUFROST_DASHBOARD_DIR . 'assets/css/dashboard.css'));

    // Bootstrap 5 Bundle JS
    wp_enqueue_script('bootstrap-5-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true);

    // Custom JS
    wp_enqueue_script('trufrost-dashboard-js', TRUFROST_DASHBOARD_URL . 'assets/js/dashboard.js', array('jquery', 'bootstrap-5-js'), '1.0.0', true);

    // Localize Script for AJAX Nonces and URLs
    wp_localize_script('trufrost-dashboard-js', 'trufrostDashboard', array(
        'ajaxurl'  => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('trufrost_dashboard_nonce'),
        'timezone' => wp_timezone_string()
    ));
}

/**
 * Helper to get the correct table names
 */
function trufrost_get_db_tables()
{
    global $wpdb;
    return array(
        'otp' => 'otp_verifications', // literal name as created in api-handler.php
        'sr'  => $wpdb->prefix . 'service_requests' // prefixed wpxj_service_requests
    );
}

/**
 * Render the Admin Dashboard HTML Page
 */
function trufrost_admin_dashboard_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'trufrost-admin-dashboard'));
    }

    // Include the dashboard view file
    include_once TRUFROST_DASHBOARD_DIR . 'admin/views/dashboard-view.php';
}

/**
 * AJAX Handler: Get Dashboard Overview Stats
 */
add_action('wp_ajax_trufrost_get_overview_stats', 'trufrost_get_overview_stats_callback');
function trufrost_get_overview_stats_callback()
{
    // Nonce Check
    check_ajax_referer('trufrost_dashboard_nonce', 'security');

    // Admin Capability Check
    if (! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Forbidden', 'trufrost-admin-dashboard')), 403);
    }

    global $wpdb;
    $tables = trufrost_get_db_tables();
    $otp_table = $tables['otp'];
    $sr_table = $tables['sr'];

    $today_start = date('Y-m-d 00:00:00');
    $today_end   = date('Y-m-d 23:59:59');

    // 1. Total & Today's Service Requests
    $total_sr = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$sr_table}`");
    $today_sr = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM `{$sr_table}` WHERE created_at BETWEEN %s AND %s",
        $today_start,
        $today_end
    ));

    // 2. Total & Today's OTP Verifications
    $total_otp = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$otp_table}`");
    $today_otp = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM `{$otp_table}` WHERE created_at BETWEEN %s AND %s",
        $today_start,
        $today_end
    ));

    // 3. Verified vs Failed/Unused OTP Count
    // Verified OTP Count (is_verified = 1)
    $verified_otp = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$otp_table}` WHERE is_verified = 1");

    // Failed/Unused OTP Count (is_verified = 0 or attempts > 0 or expired)
    // Here we count failed/unused as those that are NOT verified (is_verified = 0)
    $failed_unused_otp = (int) $wpdb->get_var("SELECT COUNT(*) FROM `{$otp_table}` WHERE is_verified = 0");

    wp_send_json_success(array(
        'total_service_requests' => $total_sr,
        'today_service_requests' => $today_sr,
        'total_otp_verifications' => $total_otp,
        'today_otp_verifications' => $today_otp,
        'verified_otp_count'      => $verified_otp,
        'failed_unused_otp_count' => $failed_unused_otp
    ));
}

/**
 * AJAX Handler: Get Paginated, Filtered and Sorted Service Requests
 */
add_action('wp_ajax_trufrost_get_service_requests', 'trufrost_get_service_requests_callback');
function trufrost_get_service_requests_callback()
{
    check_ajax_referer('trufrost_dashboard_nonce', 'security');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Forbidden', 'trufrost-admin-dashboard')), 403);
    }

    global $wpdb;
    $tables = trufrost_get_db_tables();
    $table = $tables['sr'];

    // Pagination Parameters
    $limit  = isset($_POST['limit']) ? max(1, intval($_POST['limit'])) : 10;
    $page   = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Sorting parameters
    $allowed_columns = array('id', 'mobile_number', 'customer_name', 'email', 'salesforce_status', 'created_at');
    $orderby = isset($_POST['orderby']) && in_array($_POST['orderby'], $allowed_columns, true) ? $_POST['orderby'] : 'id';
    $order   = isset($_POST['order']) && strtolower($_POST['order']) === 'asc' ? 'ASC' : 'DESC';

    // Filters parameters
    $where_clauses = array();
    $params        = array();

    // 1. Mobile Number search
    if (! empty($_POST['mobile_number'])) {
        $where_clauses[] = "mobile_number LIKE %s";
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_POST['mobile_number'])) . '%';
    }

    // 2. Customer Name search
    if (! empty($_POST['customer_name'])) {
        $where_clauses[] = "customer_name LIKE %s";
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_POST['customer_name'])) . '%';
    }

    // 3. Email search
    if (! empty($_POST['email'])) {
        $where_clauses[] = "email LIKE %s";
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_POST['email'])) . '%';
    }

    // 4. Salesforce status filter
    if (! empty($_POST['salesforce_status'])) {
        $where_clauses[] = "salesforce_status = %s";
        $params[] = sanitize_text_field($_POST['salesforce_status']);
    }

    // 5. Date Range filter (From Date - To Date)
    if (! empty($_POST['date_from'])) {
        $where_clauses[] = "created_at >= %s";
        $params[] = sanitize_text_field($_POST['date_from']) . ' 00:00:00';
    }
    if (! empty($_POST['date_to'])) {
        $where_clauses[] = "created_at <= %s";
        $params[] = sanitize_text_field($_POST['date_to']) . ' 23:59:59';
    }

    // 6. Global Search
    if (! empty($_POST['search_global'])) {
        $search = '%' . $wpdb->esc_like(sanitize_text_field($_POST['search_global'])) . '%';
        $where_clauses[] = "(id LIKE %s OR mobile_number LIKE %s OR customer_name LIKE %s OR email LIKE %s OR salesforce_status LIKE %s OR form_data LIKE %s OR salesforce_response LIKE %s)";
        $params = array_merge($params, array($search, $search, $search, $search, $search, $search, $search));
    }

    // Build query
    $where_sql = '';
    if (! empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Query Total Records matching filters
    $count_query = "SELECT COUNT(*) FROM `{$table}` {$where_sql}";
    if (! empty($params)) {
        $total_items = (int) $wpdb->get_var($wpdb->prepare($count_query, $params));
    } else {
        $total_items = (int) $wpdb->get_var($count_query);
    }

    // Query records with pagination
    $data_query = "SELECT * FROM `{$table}` {$where_sql} ORDER BY `{$orderby}` {$order} LIMIT %d OFFSET %d";
    $query_params = array_merge($params, array($limit, $offset));
    $results = $wpdb->get_results($wpdb->prepare($data_query, $query_params), ARRAY_A);

    wp_send_json_success(array(
        'items'       => $results,
        'total_items' => $total_items,
        'page'        => $page,
        'limit'       => $limit,
        'pages'       => ceil($total_items / $limit)
    ));
}

/**
 * AJAX Handler: Get Paginated, Filtered and Sorted OTP Verifications
 */
add_action('wp_ajax_trufrost_get_otp_verifications', 'trufrost_get_otp_verifications_callback');
function trufrost_get_otp_verifications_callback()
{
    check_ajax_referer('trufrost_dashboard_nonce', 'security');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Forbidden', 'trufrost-admin-dashboard')), 403);
    }

    global $wpdb;
    $tables = trufrost_get_db_tables();
    $table = $tables['otp'];

    // Pagination Parameters
    $limit  = isset($_POST['limit']) ? max(1, intval($_POST['limit'])) : 10;
    $page   = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $offset = ($page - 1) * $limit;

    // Sorting parameters
    $allowed_columns = array('id', 'otp_code', 'mobile_number', 'is_verified', 'is_used', 'attempts', 'created_at', 'expires_at');
    $orderby = isset($_POST['orderby']) && in_array($_POST['orderby'], $allowed_columns, true) ? $_POST['orderby'] : 'id';
    $order   = isset($_POST['order']) && strtolower($_POST['order']) === 'asc' ? 'ASC' : 'DESC';

    // Filters parameters
    $where_clauses = array();
    $params        = array();

    // 1. Mobile Number search
    if (! empty($_POST['mobile_number'])) {
        $where_clauses[] = "mobile_number LIKE %s";
        $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_POST['mobile_number'])) . '%';
    }

    // 2. OTP Status filter (Verified / Not Verified)
    if (isset($_POST['is_verified']) && $_POST['is_verified'] !== '') {
        $where_clauses[] = "is_verified = %d";
        $params[] = intval($_POST['is_verified']);
    }

    // 3. Used Status filter (Used / Unused)
    if (isset($_POST['is_used']) && $_POST['is_used'] !== '') {
        $where_clauses[] = "is_used = %d";
        $params[] = intval($_POST['is_used']);
    }

    // 4. Attempts Count
    if (isset($_POST['attempts']) && $_POST['attempts'] !== '') {
        $where_clauses[] = "attempts = %d";
        $params[] = intval($_POST['attempts']);
    }

    // 5. Date Range filter (From Date - To Date)
    if (! empty($_POST['date_from'])) {
        $where_clauses[] = "created_at >= %s";
        $params[] = sanitize_text_field($_POST['date_from']) . ' 00:00:00';
    }
    if (! empty($_POST['date_to'])) {
        $where_clauses[] = "created_at <= %s";
        $params[] = sanitize_text_field($_POST['date_to']) . ' 23:59:59';
    }

    // 6. Global Search
    if (! empty($_POST['search_global'])) {
        $search = '%' . $wpdb->esc_like(sanitize_text_field($_POST['search_global'])) . '%';
        $where_clauses[] = "(id LIKE %s OR mobile_number LIKE %s OR otp_code LIKE %s OR access_token LIKE %s OR salesforce_token LIKE %s)";
        $params = array_merge($params, array($search, $search, $search, $search, $search));
    }

    // Build query
    $where_sql = '';
    if (! empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Query Total Records matching filters
    $count_query = "SELECT COUNT(*) FROM `{$table}` {$where_sql}";
    if (! empty($params)) {
        $total_items = (int) $wpdb->get_var($wpdb->prepare($count_query, $params));
    } else {
        $total_items = (int) $wpdb->get_var($count_query);
    }

    // Query records with pagination
    $data_query = "SELECT * FROM `{$table}` {$where_sql} ORDER BY `{$orderby}` {$order} LIMIT %d OFFSET %d";
    $query_params = array_merge($params, array($limit, $offset));
    $results = $wpdb->get_results($wpdb->prepare($data_query, $query_params), ARRAY_A);

    wp_send_json_success(array(
        'items'       => $results,
        'total_items' => $total_items,
        'page'        => $page,
        'limit'       => $limit,
        'pages'       => ceil($total_items / $limit)
    ));
}

/**
 * Handle CSV Export via Direct Action (GET)
 */
add_action('admin_init', 'trufrost_handle_csv_export');
function trufrost_handle_csv_export()
{
    if (! isset($_GET['trufrost_export'])) {
        return;
    }

    // Access Controls
    if (! current_user_can('manage_options')) {
        wp_die(__('Forbidden', 'trufrost-admin-dashboard'));
    }

    // Security Nonce Verification
    $nonce = isset($_GET['security']) ? $_GET['security'] : '';
    if (! wp_verify_nonce($nonce, 'trufrost_dashboard_nonce')) {
        wp_die(__('Security token expired. Please refresh the dashboard and try again.', 'trufrost-admin-dashboard'));
    }

    global $wpdb;
    $tables = trufrost_get_db_tables();
    $export_type = sanitize_text_field($_GET['trufrost_export']);

    $where_clauses = array();
    $params        = array();

    if ($export_type === 'service-requests') {
        $table = $tables['sr'];

        // Apply filters
        if (! empty($_GET['mobile_number'])) {
            $where_clauses[] = "mobile_number LIKE %s";
            $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['mobile_number'])) . '%';
        }
        if (! empty($_GET['customer_name'])) {
            $where_clauses[] = "customer_name LIKE %s";
            $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['customer_name'])) . '%';
        }
        if (! empty($_GET['email'])) {
            $where_clauses[] = "email LIKE %s";
            $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['email'])) . '%';
        }
        if (! empty($_GET['salesforce_status'])) {
            $where_clauses[] = "salesforce_status = %s";
            $params[] = sanitize_text_field($_GET['salesforce_status']);
        }
        if (! empty($_GET['date_from'])) {
            $where_clauses[] = "created_at >= %s";
            $params[] = sanitize_text_field($_GET['date_from']) . ' 00:00:00';
        }
        if (! empty($_GET['date_to'])) {
            $where_clauses[] = "created_at <= %s";
            $params[] = sanitize_text_field($_GET['date_to']) . ' 23:59:59';
        }
        if (! empty($_GET['search_global'])) {
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search_global'])) . '%';
            $where_clauses[] = "(id LIKE %s OR mobile_number LIKE %s OR customer_name LIKE %s OR email LIKE %s OR salesforce_status LIKE %s OR form_data LIKE %s OR salesforce_response LIKE %s)";
            $params = array_merge($params, array($search, $search, $search, $search, $search, $search, $search));
        }

        $where_sql = '';
        if (! empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $data_query = "SELECT * FROM `{$table}` {$where_sql} ORDER BY id DESC";
        $results = ! empty($params) ? $wpdb->get_results($wpdb->prepare($data_query, $params), ARRAY_A) : $wpdb->get_results($data_query, ARRAY_A);

        // Generate CSV File
        $filename = 'service-requests-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV Header row
        fputcsv($output, array(
            'ID',
            'Mobile Number',
            'Customer Name',
            'Email',
            'Salesforce Status',
            'Salesforce Response',
            'Form Data',
            'Created Date'
        ));

        if (! empty($results)) {
            foreach ($results as $row) {
                fputcsv($output, array(
                    $row['id'],
                    $row['mobile_number'],
                    $row['customer_name'],
                    $row['email'],
                    $row['salesforce_status'],
                    $row['salesforce_response'],
                    $row['form_data'],
                    $row['created_at']
                ));
            }
        }
        fclose($output);
        exit;
    } elseif ($export_type === 'otp-verifications') {
        $table = $tables['otp'];

        // Apply filters
        if (! empty($_GET['mobile_number'])) {
            $where_clauses[] = "mobile_number LIKE %s";
            $params[] = '%' . $wpdb->esc_like(sanitize_text_field($_GET['mobile_number'])) . '%';
        }
        if (isset($_GET['is_verified']) && $_GET['is_verified'] !== '') {
            $where_clauses[] = "is_verified = %d";
            $params[] = intval($_GET['is_verified']);
        }
        if (isset($_GET['is_used']) && $_GET['is_used'] !== '') {
            $where_clauses[] = "is_used = %d";
            $params[] = intval($_GET['is_used']);
        }
        if (isset($_GET['attempts']) && $_GET['attempts'] !== '') {
            $where_clauses[] = "attempts = %d";
            $params[] = intval($_GET['attempts']);
        }
        if (! empty($_GET['date_from'])) {
            $where_clauses[] = "created_at >= %s";
            $params[] = sanitize_text_field($_GET['date_from']) . ' 00:00:00';
        }
        if (! empty($_GET['date_to'])) {
            $where_clauses[] = "created_at <= %s";
            $params[] = sanitize_text_field($_GET['date_to']) . ' 23:59:59';
        }
        if (! empty($_GET['search_global'])) {
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search_global'])) . '%';
            $where_clauses[] = "(id LIKE %s OR mobile_number LIKE %s OR otp_code LIKE %s OR access_token LIKE %s OR salesforce_token LIKE %s)";
            $params = array_merge($params, array($search, $search, $search, $search, $search));
        }

        $where_sql = '';
        if (! empty($where_clauses)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
        }

        $data_query = "SELECT * FROM `{$table}` {$where_sql} ORDER BY id DESC";
        $results = ! empty($params) ? $wpdb->get_results($wpdb->prepare($data_query, $params), ARRAY_A) : $wpdb->get_results($data_query, ARRAY_A);

        // Generate CSV File
        $filename = 'otp-verifications-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // CSV Header row
        fputcsv($output, array(
            'ID',
            'OTP Code',
            'Mobile Number',
            'Access Token',
            'Salesforce Token',
            'Is Verified',
            'Is Used',
            'Attempts',
            'Created Date',
            'Expiry Date'
        ));

        if (! empty($results)) {
            foreach ($results as $row) {
                fputcsv($output, array(
                    $row['id'],
                    $row['otp_code'],
                    $row['mobile_number'],
                    $row['access_token'],
                    $row['salesforce_token'],
                    $row['is_verified'] ? 'Yes' : 'No',
                    $row['is_used'] ? 'Yes' : 'No',
                    $row['attempts'],
                    $row['created_at'],
                    $row['expires_at']
                ));
            }
        }
        fclose($output);
        exit;
    }
}

