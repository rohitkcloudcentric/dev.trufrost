<?php
//Template Name: Service Request Template
get_header();
?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/template/style.css">

<body>
    <!-- Main Content -->
    <main class="main-content">
        <div class="container service-shell">

            <div class="form-header">
                <div class="header-kicker"> Trufrost Service CRM</div>
                <div class="header-title d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h1>Raise a Service Request</h1>
                        <p>Log your issue and our service team will assist you within <strong>24 hours</strong>.</p>
                    </div>
                    <!-- <span class="response-badge"><i class="bi bi-lightning-charge-fill"></i> Priority routing enabled</span> -->
                </div>
                <span class="mandatory-note">* Marked fields are mandatory</span>
            </div>


            <div class="progress-panel" aria-label="Service request progress">
                <div class="progress-step is-active" data-step="verify">
                    <span class="step-icon"><i class="bi bi-phone"></i></span>
                    <span>Verify</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="customer">
                    <span class="step-icon"><i class="bi bi-person-vcard"></i></span>
                    <span>Customer</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="service">
                    <span class="step-icon"><i class="bi bi-tools"></i></span>
                    <span>Service</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="submit">
                    <span class="step-icon"><i class="bi bi-send-check"></i></span>
                    <span>Submit</span>
                </div>
            </div>

            <div id="toastRegion" class="toast-region" aria-live="polite" aria-atomic="true"></div>

            <form id="serviceRequestForm" novalidate>

                <!-- Section 1: Customer Information -->
                <section class="form-section">
                    <h3 id="sectionTitle">Please enter your mobile number for verification</h3>

                    <!-- OTP Verification Container (Initially Hidden) -->
                    <div id="otpVerificationContainer" class="otp-verification-container hidden">
                        <div class="form-group full-width verification-field">
                            <label for="mobileNumber">Mobile Number <span class="required-asterisk">*</span> <i class="bi bi-info-circle help-icon" data-bs-toggle="tooltip" data-bs-title="OTP verification is required before creating a service request."></i></label>
                            <div class="input-group input-group-equal">
                                <div class="field-with-error">
                                    <input type="tel" id="mobileNumber" name="mobileNumber" required
                                        placeholder="Enter 10-digit number" pattern="[0-9]{10}" maxlength="10">
                                    <span class="error-message" id="mobileNumber-error"></span>
                                </div>
                                <button type="button" class="btn btn-secondary" id="sendOtpBtn"><i class="bi bi-chat-dots"></i> Send OTP</button>
                            </div>
                        </div>

                        <!-- OTP Field (Hidden by default) -->
                        <div class="form-group otp-group hidden full-width verification-field" id="otpGroup">
                            <label for="otp">Enter OTP <span class="required-asterisk">*</span></label>
                            <div class="input-group input-group-equal">
                                <div class="field-with-error">
                                    <input type="text" id="otp" name="otp" placeholder="Enter OTP" maxlength="4">
                                    <span class="error-message" id="otp-error"></span>
                                </div>
                                <button type="button" class="btn btn-secondary" id="verifyOtpBtn">Verify OTP</button>
                            </div>
                        </div>
                    </div>

                    <!-- GST/PAN Search Section (Scenario 2) -->
                    <div id="gstPanSearchSection" class="hidden gst-pan-search-wrapper">
                        <h4><i class="bi bi-building-check"></i> Mobile number not registered</h4>
                        <p>Please search your business details using GSTIN or PAN.</p>
                        <label for="gstinOrPanInput">GSTIN or PAN Number <span class="required-asterisk">*</span></label>
                        <div class="input-group input-group-equal">
                            <div class="field-with-error">
                                <input type="text" id="gstinOrPanInput" name="gstinOrPanInput" placeholder="Enter GSTIN or PAN" maxlength="15">
                                <span id="gstinOrPanSearchError" class="error-message"></span>
                            </div>
                            <button type="button" class="btn btn-secondary" id="gstinOrPanSearchBtn"> Search GSTIN Or PAN</button>
                        </div>
                    </div>

                    <!-- Rest of the form fields (Hidden until OTP/GST verification) -->
                    <div id="restOfCustomerInfo" class="hidden form-grid">

                        <!-- Mobile Number Display (After OTP Verification) -->
                        <div id="mobileNumberDisplaySection" class="hidden">
                            <div class="form-group full-width">
                                <label for="mobileNumberReadonly">Mobile Number <span class="required-asterisk">*</span></label>
                                <input type="tel" id="mobileNumberReadonly" name="mobileNumberReadonly" readonly disabled class="readonly-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name">Full Name <span class="required-asterisk">*</span></label>
                            <input type="text" id="name" name="name" required placeholder="Full Name">
                            <span class="error-message" id="name-error"></span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required-asterisk">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="Email Address">
                            <span class="error-message" id="email-error"></span>
                        </div>

                        <!-- Company/Business Name -->
                        <div class="form-group" id="companyNameGroup">
                            <label for="companyName">Business / Company Name <span class="required-asterisk">*</span></label>
                            <input type="text" id="companyName" name="companyName" required placeholder="Business Name">
                            <span class="error-message" id="companyName-error"></span>
                        </div>

                        <!-- Readonly GSTIN & PAN Displays -->
                        <div class="form-group hidden" id="gstinDisplaySection">
                            <label for="gstinReadonly">GSTIN</label>
                            <input type="text" id="gstinReadonly" name="gstinReadonly" readonly class="readonly-input" placeholder="GSTIN (15 characters)" maxlength="15">
                        </div>

                        <div class="form-group hidden" id="panDisplaySection">
                            <label for="panReadonly">PAN</label>
                            <input type="text" id="panReadonly" name="panReadonly" readonly class="readonly-input" placeholder="PAN (10 characters)" maxlength="10">
                        </div>

                        <!-- GSTIN & PAN inputs (For Scenario 2B registration) -->
                        <div class="form-group hidden" id="gstinInputSection">
                            <label for="gstinInput">GSTIN <span class="required-asterisk">*</span></label>
                            <input type="text" id="gstinInput" name="gstinInput" placeholder="Enter GSTIN (15 characters)" maxlength="15">
                            <span class="error-message" id="gstinInput-error"></span>
                        </div>

                        <div class="form-group hidden" id="panInputSection">
                            <label for="panInput">PAN <span class="required-asterisk">*</span></label>
                            <input type="text" id="panInput" name="panInput" placeholder="Enter PAN (10 characters)" maxlength="10">
                            <span class="error-message" id="panInput-error"></span>
                        </div>

                        <!-- GSTIN/PAN Upload Copy (Required for Scenario 2B registration) -->
                        <div class="form-group full-width hidden" id="gstinPanAttachmentGroup">
                            <label>GSTIN/PAN Certificate Copy <span class="required-asterisk">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="gstinPanAttachment" name="gstinPanAttachment" accept=".jpg,.jpeg,.png,.pdf" class="file-input-hidden">
                                <label for="gstinPanAttachment" class="file-upload-label">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="gstinPanAttachmentDisplay">Click to browse or drag file here</span>
                                </label>
                            </div>
                            <span class="error-message" id="gstinPanAttachment-error"></span>
                        </div>

                        <!-- Address Select Dropdown -->
                        <div class="form-group full-width" id="addressSelectGroup">
                            <label for="customerType">Choose Address </label>
                            <select id="customerType" name="customerType">
                                <option value="">Select Address</option>
                            </select>
                        </div>

                        <!-- Address Grid -->
                        <div class="form-group full-width hidden" id="newAddressFields">
                            <label>Address where Service is required <span class="required-asterisk">*</span></label>
                            <input type="text" id="address1" name="address1" placeholder="Address Line 1 *"
                                class="mb-2">
                            <input type="text" id="address2" name="address2" placeholder="Address Line 2 (Optional)"
                                class="mb-2">
                            <div class="address-grid">
                                <input type="text" id="landmark" name="landmark" placeholder="Landmark">
                                <input type="text" id="city" name="city" placeholder="City *">
                                <input type="text" id="state" name="state" placeholder="State *">
                                <input type="text" id="country" name="country" placeholder="Country *">
                                <input type="text" id="zipcode" name="zipcode" placeholder="Zip-code *">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Service Request Details (Hidden until verification) -->
                <section class="form-section hidden" id="productDetailsSection">
                    <h3>2. Service Request Details</h3>

                    <!-- Dynamic Product Cards Container -->
                    <div id="productRequestsContainer"></div>

                    <!-- Add Another Product Button -->
                    <div class="add-product-btn-container">
                        <button type="button" class="btn btn-secondary" id="addProductCardBtn"><i class="bi bi-plus-circle"></i> Add Another Product / Asset</button>
                    </div>
                </section>

                <!-- Section 3: Dummy hidden section to satisfy old script references without crash -->
                <div id="issueDetailsSection" class="hidden"></div>

                <!-- Section 4: Submission (Hidden until OTP verification) -->
                <div class="form-actions hidden" id="submitSection">
                    <div class="recaptcha-holder">
                        <div class="g-recaptcha-mock">
                            <div class="g-recaptcha-left">
                                <div class="g-recaptcha-checkbox" id="recaptchaCheckbox"></div>
                                <span class="g-recaptcha-text" id="recaptchaText">I'm not a robot</span>
                            </div>
                            <div class="g-recaptcha-right">
                                <div class="g-recaptcha-logo"></div>
                                <div class="g-recaptcha-logo-text">
                                    reCAPTCHA<br>
                                    <a href="https://policies.google.com/privacy" target="_blank">Privacy</a> - <a href="https://policies.google.com/terms" target="_blank">Terms</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-large" id="submitBtn"> Submit Request</button>
                </div>
            </form>

            <!-- Success Message Container -->
            <div id="successMessage" class="success-message hidden">
                <div class="success-icon"><i class="bi bi-check-circle-fill"></i></div>
                <h2>Service Request Submitted!</h2>
                <p>If your mobile number has active WhatsApp, you will shortly receive a message from us with Service
                    call details.</p>
                <div class="ticket-info">
                    <span class="label">Case Number:</span>
                    <strong id="ticketIdDisplay">TRU-00000</strong>
                </div>

                <!-- Service Request Details -->
                <div id="serviceRequestDetails" class="service-request-details" style="display: none; margin-top: 30px; text-align: left; background: #f9f9f9; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-bottom: 15px; color: #333;">Service Request Details</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Mobile Number:</strong>
                            <p id="detailMobileNumber" style="margin: 5px 0;">-</p>
                        </div>
                        <div>
                            <strong>Service Category:</strong>
                            <p id="detailPurpose" style="margin: 5px 0;">-</p>
                        </div>
                        <div>
                            <strong>Model:</strong>
                            <p id="detailModel" style="margin: 5px 0;">-</p>
                        </div>
                        <div>
                            <strong>Serial Number:</strong>
                            <p id="detailSerial" style="margin: 5px 0;">-</p>
                        </div>
                        <div>
                            <strong>Warranty Status:</strong>
                            <p id="detailWarranty" style="margin: 5px 0;">-</p>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <p id="detailStatus" style="margin: 5px 0;">-</p>
                        </div>
                    </div>
                </div>

                <p class="contact-info">For more information, call us on our toll-free number.</p>
                <button class="btn btn-secondary" onclick="location.reload()"><i class="bi bi-arrow-repeat"></i> Raise Another Request</button>
            </div>

            <!-- Failure Message Container -->
            <div id="failureMessage" class="failure-message hidden">
                <div class="failure-icon"><i class="bi bi-x-circle-fill"></i></div>
                <h2>Service Request Failed</h2>
                <p id="failureMessage-text">We encountered an issue while submitting your service request.</p>
                <div class="error-details" style="margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 8px; text-align: left;">
                    <strong>Error Details:</strong>
                    <p id="failureErrorMessage" style="margin: 10px 0; color: #856404;">-</p>
                </div>
                <p class="contact-info">Please try again or contact our support team for assistance.</p>
                <button class="btn btn-secondary" onclick="location.reload()"><i class="bi bi-arrow-counterclockwise"></i> Try Again</button>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const wp_theme_api_url = "<?php echo get_stylesheet_directory_uri(); ?>/template/api-handler.php";
    </script>
    <script src="<?php echo get_stylesheet_directory_uri(); ?>/template/script.js"></script>
    <script>
        const yearElement = document.getElementById('year');
        if (yearElement) {
            yearElement.textContent = new Date().getFullYear();
        }
    </script>

    <?php
    get_footer();
    ?>