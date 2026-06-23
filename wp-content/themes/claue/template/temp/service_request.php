<?php
// Template Name: Service Request Template
get_header();
?>

<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/template/style.css">

<body>
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">

            <div class="form-header">
                <div class="header-title">
                    <h1>Raise a Service Request</h1>
                </div>
                <p>Log your issue and our service team will assist you within <strong>24 hours</strong>.</p>
                <span class="mandatory-note">* Marked fields are mandatory</span>
            </div>

            <form id="serviceRequestForm">

                <!-- Section 1: Customer Information -->
                <section class="form-section">
                    <h3 id="sectionTitle" style="text-align: center;">Please enter your mobile number for verification</h3>

                    <!-- OTP Verification Container (Initially Hidden) -->
                    <div id="otpVerificationContainer" class="otp-verification-container hidden">
                        <div class="form-group full-width" style="max-width: 500px; margin: 0 auto;">
                            <label for="mobileNumber">Mobile Number (OTP verification must) <span
                                    class="required-asterisk">*</span></label>
                            <div class="input-group input-group-equal">
                                <input type="tel" id="mobileNumber" name="mobileNumber" required
                                    placeholder="Enter 10-digit number" pattern="[0-9]{10}" maxlength="10">
                                <button type="button" class="btn btn-secondary" id="sendOtpBtn">Send OTP</button>
                            </div>
                        </div>

                        <!-- OTP Field (Hidden by default) -->
                        <div class="form-group otp-group hidden full-width" id="otpGroup" style="max-width: 500px; margin: 0 auto;">
                            <label for="otp">Enter OTP <span class="required-asterisk">*</span></label>
                            <div class="input-group input-group-equal">
                                <input type="text" id="otp" name="otp" placeholder="Enter OTP" maxlength="4">
                                <button type="button" class="btn btn-secondary" id="verifyOtpBtn">&nbsp;Verify&nbsp;</button>
                            </div>
                        </div>
                    </div>

                    <!-- Rest of the form fields (Hidden until OTP verification) -->
                    <div id="restOfCustomerInfo" class="hidden form-grid">

                        <!-- Mobile Number Display (After OTP Verification) - Hidden by default -->
                        <div id="mobileNumberDisplaySection" class="hidden">
                            <div class="form-group full-width">
                                <label for="mobileNumberReadonly">Mobile Number <span class="required-asterisk">*</span></label>
                                <input type="tel" id="mobileNumberReadonly" name="mobileNumberReadonly" readonly disabled class="readonly-input" style="background-color: #f5f5f5;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name">Name <span class="required-asterisk">*</span></label>
                            <input type="text" id="name" name="name" required placeholder="Full Name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required-asterisk">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="Email Address">
                        </div>

                        <div class="form-group">
                            <label for="companyName">Company Name <span class="required-asterisk">*</span></label>
                            <input type="text" id="companyName" name="companyName" required placeholder="Company Name">
                        </div>

                        <div class="form-group full-width">
                            <label for="customerType">Choose Address </label>
                            <select id="customerType" name="customerType">
                                <option value="">Select Address</option>
                            </select>
                        </div>

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

                <!-- Section 2: Product Details (Hidden until OTP verification) -->
                <section class="form-section hidden" id="productDetailsSection">
                    <h3>2. Product Details</h3>
                    <div class="form-grid">

                        <!-- Asset Selection (Step 1) -->
                        <div class="form-group" id="assetSelectionGroup">
                            <label for="assetNumber">Select Asset <span class="required-asterisk">*</span></label>
                            <select id="assetNumber" name="assetNumber" required>
                                <option value="">Select an Asset...</option>
                            </select>
                            <div id="assetDropdownWrapper" class="model-dropdown-wrapper hidden">
                                <input type="text" id="assetSearchInput" placeholder="Search asset..." class="model-search-input">
                                <div id="assetDropdownList" class="model-dropdown-list"></div>
                            </div>
                        </div>

                        <!-- Model Number Selection (Step 2 - Hidden until "Other" is selected) -->
                        <div class="form-group hidden" id="modelSelectionGroup">
                            <label for="modelNumber">Model Number <span class="required-asterisk">*</span></label>
                            <select id="modelNumber" name="modelNumber">
                                <option value="">Select a Model...</option>
                            </select>
                            <div id="modelDropdownWrapper" class="model-dropdown-wrapper hidden">
                                <input type="text" id="modelSearchInput" placeholder="Search model..." class="model-search-input">
                                <div id="modelDropdownList" class="model-dropdown-list"></div>
                            </div>
                        </div>

                        <!-- Manual Model entry (Hidden by default) -->
                        <div class="form-group hidden" id="manualModelGroup">
                            <label for="manualModel">Enter New Model Number <span
                                    class="required-asterisk">*</span></label>
                            <input type="text" id="manualModel" name="manualModel">
                        </div>

                        <div class="form-group">
                            <label for="productName">Product Name</label>
                            <input type="text" id="productName" name="productName" readonly class="readonly-input">
                        </div>

                        <div class="form-group">
                            <label for="productCategory">Product Category</label>
                            <input type="text" id="productCategory" name="productCategory" readonly
                                class="readonly-input">
                        </div>

                        <div class="form-group">
                            <label for="serialNumber">Serial Number <span class="required-asterisk">*</span></label>
                            <input type="text" id="serialNumber" name="serialNumber" required
                                placeholder="Enter Serial Number">
                        </div>

                        <div class="form-group">
                            <label for="purchaseDate">Purchase Date <span class="required-asterisk">*</span></label>
                            <input type="date" id="purchaseDate" name="purchaseDate" max="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="warrantyStatus">Warranty Status <span class="required-asterisk">*</span></label>
                            <select id="warrantyStatus" name="warrantyStatus" required>
                                <option value="">Select Warranty Status</option>
                                <option value="1_year_standard">1 year Standard warranty</option>
                                <option value="1_plus_1_extended">1+ 1 year Extended Warranty</option>
                                <option value="1_plus_2_extended">1+ 2 years Extended Warranty</option>
                                <option value="amc">AMC</option>
                                <option value="out_of_warranty">Out of Warranty</option>
                            </select>
                        </div>

                        <!-- GST & PAN Information (Visible for all warranty options) -->
                        <div class="form-group full-width hidden" id="gstPanNoteGroup">
                            <div style="padding: 12px; border-radius: 4px;">
                                <p style="margin: 0; font-size: 14px;">
                                    <strong>Note:</strong> In case of "Out of warranty" please enter company's active GSTIN. If GSTIN is not applicable please enter company's PAN number.
                                </p>
                            </div>
                        </div>

                        <div class="form-group hidden" id="gstNumberGroup">
                            <label for="gstNumber">GST Number <span class="required-asterisk">*</span></label>
                            <input type="text" id="gstNumber" name="gstNumber" placeholder="Enter GST Number (15 characters)" maxlength="15">
                            <span id="gstNumberError" class="error-message" style="color: #d32f2f; font-size: 12px; margin-top: 5px; display: none;"></span>
                        </div>

                        <div class="form-group full-width hidden" id="gstCertificateGroup">
                            <label>GST Certificate <span class="required-asterisk">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="gstCertificate" name="gstCertificate" accept=".jpg,.jpeg,.png,.pdf" class="file-input-hidden">
                                <label for="gstCertificate" class="file-upload-label">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="gstCertificateDisplay">Click to browse or drag file here</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group hidden" id="panNumberGroup">
                            <label for="panNumber">PAN Number <span class="required-asterisk">*</span></label>
                            <input type="text" id="panNumber" name="panNumber" placeholder="Enter PAN Number (10 characters)" maxlength="10">
                            <span id="panNumberError" class="error-message" style="color: #d32f2f; font-size: 12px; margin-top: 5px; display: none;"></span>
                        </div>

                        <div class="form-group full-width hidden" id="panFileGroup">
                            <label>PAN File <span class="required-asterisk">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="panFile" name="panFile" accept=".jpg,.jpeg,.png,.pdf" class="file-input-hidden">
                                <label for="panFile" class="file-upload-label">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="panFileDisplay">Click to browse or drag file here</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group full-width hidden" id="invoiceGroup">
                            <label>Invoice Copy <span class="required-asterisk">*</span> (Required for
                                1 year Standard Warranty)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="invoiceCopy" name="invoiceCopy" accept=".jpg,.jpeg,.png,.pdf" class="file-input-hidden">
                                <label for="invoiceCopy" class="file-upload-label">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span id="fileNameDisplay">Click to browse or drag file here</span>
                                </label>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- Section 3: Issue Details (Hidden until OTP verification) -->
                <section class="form-section hidden" id="issueDetailsSection">
                    <h3>3. Issue Details</h3>
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="serviceCategory">Service Category <span class="required-asterisk">*</span></label>
                            <select id="serviceCategory" name="serviceCategory" required>
                                <option value="">Select Service Category</option>
                                <!-- <option value="Demo">Demo</option> -->
                                <option value="Breakdown">Breakdown</option>
                                <option value="Installation">Installation</option>
                                <option value="PM_Service">PM Service</option>
                                <option value="Quote_Request">Quote Request</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="preferredVisit">Preferred Visit Date & Time</label>
                            <input type="datetime-local" id="preferredVisit" name="preferredVisit" min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>

                        <div class="form-group full-width">
                            <label for="issueDescription">Issue Description</label>
                            <textarea id="issueDescription" name="issueDescription" rows="4"
                                placeholder="Describe the issue in detail..."></textarea>
                        </div>

                    </div>
                </section>

                <!-- Section 4: Submission (Hidden until OTP verification) -->
                <div class="form-actions hidden" id="submitSection">
                    <button type="submit" class="btn btn-primary btn-large" id="submitBtn">Submit Service
                        Request</button>
                </div>
            </form>

            <!-- Success Message Container -->
            <div id="successMessage" class="success-message hidden">
                <div class="success-icon">✓</div>
                <h2>Service Request Submitted!</h2>
                <p>If your mobile number has active WhatsApp, you will shortly receive a message from us with Service
                    call details.</p>
                <div class="ticket-info">
                    <span class="label">Ticket Booking ID:</span>
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
                <button class="btn btn-secondary" onclick="location.reload()">Raise Another Request</button>
            </div>

            <!-- Failure Message Container -->
            <div id="failureMessage" class="failure-message hidden">
                <div class="failure-icon">✗</div>
                <h2>Service Request Failed</h2>
                <p id="failureMessage-text">We encountered an issue while submitting your service request.</p>
                <div class="error-details" style="margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 8px; text-align: left;">
                    <strong>Error Details:</strong>
                    <p id="failureErrorMessage" style="margin: 10px 0; color: #856404;">-</p>
                </div>
                <p class="contact-info">Please try again or contact our support team for assistance.</p>
                <button class="btn btn-secondary" onclick="location.reload()">Try Again</button>
            </div>

        </div>
    </main>

    <!-- <script src="script.js"></script> -->
    <script src="<?php echo get_template_directory_uri(); ?>/template/script.js"></script>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>

    <?php
    get_footer();
    ?>