document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const form = document.getElementById('serviceRequestForm');

    // OTP Elements
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const otpGroup = document.getElementById('otpGroup');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const mobileNumber = document.getElementById('mobileNumber');
    const otpInput = document.getElementById('otp');
    const otpVerificationContainer = document.getElementById('otpVerificationContainer');
    const mobileNumberDisplaySection = document.getElementById('mobileNumberDisplaySection');
    const mobileNumberReadonly = document.getElementById('mobileNumberReadonly');

    // Form Sections
    const restOfCustomerInfo = document.getElementById('restOfCustomerInfo');
    const productDetailsSection = document.getElementById('productDetailsSection');
    const issueDetailsSection = document.getElementById('issueDetailsSection');
    const submitSection = document.getElementById('submitSection');

    // GST/PAN Search Section Elements
    const gstPanSearchSection = document.getElementById('gstPanSearchSection');
    const gstinOrPanInput = document.getElementById('gstinOrPanInput');
    const gstinOrPanSearchBtn = document.getElementById('gstinOrPanSearchBtn');
    const gstinOrPanSearchError = document.getElementById('gstinOrPanSearchError');

    // Customer Detail Input & Display Elements
    const companyNameGroup = document.getElementById('companyNameGroup');
    const companyName = document.getElementById('companyName');
    const gstinDisplaySection = document.getElementById('gstinDisplaySection');
    const gstinReadonly = document.getElementById('gstinReadonly');
    const panDisplaySection = document.getElementById('panDisplaySection');
    const panReadonly = document.getElementById('panReadonly');
    const gstinInputSection = document.getElementById('gstinInputSection');
    const gstinInput = document.getElementById('gstinInput');
    const panInputSection = document.getElementById('panInputSection');
    const panInput = document.getElementById('panInput');
    const gstinPanAttachmentGroup = document.getElementById('gstinPanAttachmentGroup');
    const gstinPanAttachment = document.getElementById('gstinPanAttachment');
    const gstinPanAttachmentDisplay = document.getElementById('gstinPanAttachmentDisplay');

    // Address Elements
    const addressSelectGroup = document.getElementById('addressSelectGroup');
    const customerType = document.getElementById('customerType');
    const newAddressFields = document.getElementById('newAddressFields');
    const addressInputs = newAddressFields.querySelectorAll('input');

    // Multi-Product Elements
    const productRequestsContainer = document.getElementById('productRequestsContainer');
    const addProductCardBtn = document.getElementById('addProductCardBtn');

    // Success/Failure Elements
    const successMessage = document.getElementById('successMessage');
    const failureMessage = document.getElementById('failureMessage');
    const ticketIdDisplay = document.getElementById('ticketIdDisplay');

    // State Variables
    let customerData = null;
    let isOtpVerified = false;
    let activeScenario = 'Scenario_1'; // Scenario_1, Scenario_2A, Scenario_2B
    let contactId = null;
    let accountId = null;
    let searchGstPanVal = '';
    let existingAddresses = [];
    let registeredAssets = [];
    let productsList = []; // Item Master Product2 array
    let productCards = []; // Array of card index integers
    let cardCount = 0;

    // reCAPTCHA elements & status
    const recaptchaCheckbox = document.getElementById('recaptchaCheckbox');
    const recaptchaText = document.getElementById('recaptchaText');
    let isRecaptchaChecked = false;

    // UI helpers
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (window.bootstrap && tooltipTriggerList.length) {
        [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
    }

    function showToast(message, type = 'info', title = '') {
        const region = document.getElementById('toastRegion');
        if (!region) {
            console[type === 'error' ? 'error' : 'log'](message);
            return;
        }

        const iconMap = {
            success: 'bi-check-circle-fill',
            error: 'bi-exclamation-triangle-fill',
            info: 'bi-info-circle-fill'
        };
        const titleMap = {
            success: 'Success',
            error: 'Action needed',
            info: 'Update'
        };
        const toast = document.createElement('div');
        toast.className = `app-toast ${type}`;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.innerHTML = `<i class="bi ${iconMap[type] || iconMap.info}"></i><div><strong>${title || titleMap[type] || 'Update'}</strong><span>${message}</span></div>`;
        region.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-6px)';
            setTimeout(() => toast.remove(), 220);
        }, type === 'error' ? 5200 : 3600);
    }

    function updateProgress(stage) {
        const order = ['verify', 'customer', 'service', 'submit'];
        const activeIndex = Math.max(order.indexOf(stage), 0);
        document.querySelectorAll('.progress-step').forEach(step => {
            const idx = order.indexOf(step.dataset.step);
            step.classList.toggle('is-active', idx === activeIndex);
            step.classList.toggle('is-complete', idx >= 0 && idx < activeIndex);
        });
    }

    function initializeUploadZone(input, display, defaultText) {
        if (!input || !display) return;
        const wrapper = input.closest('.file-upload-wrapper');
        const label = wrapper ? wrapper.querySelector('.file-upload-label') : null;
        const syncFileName = () => {
            if (input.files && input.files.length > 0) {
                display.textContent = input.files[0].name;
                wrapper?.classList.add('has-file');
            } else {
                display.textContent = defaultText;
                wrapper?.classList.remove('has-file');
            }
            clearFieldError(input.id);
        };
        input.addEventListener('change', syncFileName);
        if (label && wrapper) {
            ['dragenter', 'dragover'].forEach(eventName => {
                label.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    wrapper.classList.add('drag-over');
                });
            });
            ['dragleave', 'drop'].forEach(eventName => {
                label.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    wrapper.classList.remove('drag-over');
                });
            });
            label.addEventListener('drop', (event) => {
                if (event.dataTransfer?.files?.length) {
                    input.files = event.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }
    }

    function getFieldErrorId(fieldId) {
        if (fieldId === 'gstinOrPanInput') return 'gstinOrPanSearchError';
        return fieldId ? `${fieldId}-error` : '';
    }

    function getErrorAnchor(field) {
        if (!field) return null;
        return field.closest('.file-upload-wrapper') || field.closest('.input-group') || field;
    }

    function ensureFieldErrorSlot(field) {
        if (!field || !field.id || field.type === 'hidden') return;
        if (field.closest('.address-grid') && !field.closest('.address-field')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'address-field';
            field.parentNode.insertBefore(wrapper, field);
            wrapper.appendChild(field);
        }
        const errorId = getFieldErrorId(field.id);
        if (document.getElementById(errorId)) return;
        const error = document.createElement('span');
        error.className = 'error-message';
        error.id = errorId;
        const anchor = getErrorAnchor(field);
        anchor.insertAdjacentElement('afterend', error);
    }

    function ensureInlineErrorSlots(scope = document) {
        scope.querySelectorAll('input[id], select[id], textarea[id]').forEach(ensureFieldErrorSlot);
    }

    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const error = document.getElementById(getFieldErrorId(fieldId));
        if (field) field.classList.remove('is-invalid');
        if (error) {
            error.textContent = '';
            error.style.display = '';
        }
    }

    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) ensureFieldErrorSlot(field);
        const error = document.getElementById(getFieldErrorId(fieldId));
        if (field) field.classList.add('is-invalid');
        if (error) {
            error.textContent = message;
            error.style.display = 'block';
        }
    }

    function validateFieldValue(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return true;
        if (!field.value || !field.value.trim()) {
            showFieldError(fieldId, message);
            field.focus();
            return false;
        }
        clearFieldError(fieldId);
        return true;
    }

    function isVisibleField(field) {
        if (!field || field.disabled || field.closest('.hidden')) return false;
        if (field.offsetParent !== null) return true;
        return field.type === 'file' && field.closest('.file-upload-wrapper')?.offsetParent !== null;
    }

    function getFieldLabel(field) {
        const explicitLabel = field.id ? document.querySelector(`label[for="${field.id}"]`) : null;
        const nearbyLabel = field.closest('.form-group')?.querySelector('label');
        const labelText = (explicitLabel || nearbyLabel)?.textContent || field.placeholder || field.name || 'This field';
        return labelText.replace('*', '').replace(/\s+/g, ' ').trim();
    }

    function validateRequiredControl(field) {
        if (!isVisibleField(field) || !field.required) return { isValid: true };

        const isEmptyFile = field.type === 'file' && (!field.files || field.files.length === 0);
        const isEmptyValue = field.type !== 'file' && (!field.value || !String(field.value).trim());
        if (!isEmptyFile && !isEmptyValue) {
            clearFieldError(field.id);
            return { isValid: true };
        }

        showFieldError(field.id, `${getFieldLabel(field)} is required.`);
        return { isValid: false, field };
    }

    function validateVisibleRequiredControls(scope = form) {
        let firstInvalid = null;
        scope.querySelectorAll('input[required][id], select[required][id], textarea[required][id]').forEach(field => {
            const result = validateRequiredControl(field);
            if (!result.isValid && !firstInvalid) firstInvalid = result.field;
        });
        return { isValid: !firstInvalid, firstInvalid };
    }

    document.addEventListener('input', (event) => {
        if (event.target?.matches?.('input[id], textarea[id]')) {
            clearFieldError(event.target.id);
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target?.matches?.('select[id], input[id]')) {
            clearFieldError(event.target.id);
        }
    });

    document.addEventListener('blur', (event) => {
        if (event.target && event.target.id === 'email') {
            const emailVal = event.target.value.trim();
            if (emailVal) {
                if (!validateEmail(emailVal)) {
                    showFieldError('email', 'Please enter a valid email address (e.g., name@domain.com).');
                } else {
                    clearFieldError('email');
                }
            }
        }
    }, true);

    ensureInlineErrorSlots();
    updateProgress('verify');
    initializeUploadZone(gstinPanAttachment, gstinPanAttachmentDisplay, 'Click to browse or drag file here');

    // API Base URL
    let API_URL = (typeof wp_theme_api_url !== 'undefined') ? wp_theme_api_url : 'api-handler.php';

    // --- Fetch Customer Data (After Session Validation) ---
    async function fetchCustomerDataInBackground() {
        try {
            console.log('Fetching customer data in background...');
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'getCustomerData'
                })
            });

            const data = await response.json();
            console.log('Customer data response:', data);

            if (data.success && data.customerData) {
                customerData = data.customerData;
                console.log('Customer data loaded:', customerData);

                // Populate form with the newly fetched customer data
                populateFormWithCustomerData(customerData);
            }
        } catch (error) {
            console.error('Error fetching customer data:', error);
        }
    }

    // --- Check Session on Page Load ---
    async function checkExistingSession() {
        showGlobalLoader('Verifying session and loading system data...');
        try {
            console.log('Starting session check...');
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'checkSession'
                })
            });

            const data = await response.json();
            console.log('Session check response:', data);

            if (data.success && data.verified) {
                console.log('OK Existing session found for:', data.mobile);

                isOtpVerified = true;
                mobileNumber.value = data.mobile;

                // Fetch products and customer data in parallel
                await Promise.all([fetchProducts(), fetchCustomerDataInBackground()]);

                // Now customerData is set, decide which scenario to show
                const sessionContact    = customerData?.contactInfo;
                const sessionHasContact = sessionContact && sessionContact.SF_ContactId;
                const sessionActive     = isContactActive(sessionContact);

                if (sessionHasContact && sessionActive) {
                    // Active contact — go directly to the form
                    activeScenario = 'Scenario_1';
                    contactId = sessionContact.SF_ContactId;
                    populateFormWithCustomerData(customerData);
                    revealForm();
                } else if (sessionHasContact && !sessionActive) {
                    // Inactive contact — show GST/PAN gate with notice
                    showGstPanSearchSection(true);
                } else {
                    // No contact found — show OTP screen (new user)
                    otpVerificationContainer.classList.remove('hidden');
                }
            } else {
                console.log('X No existing session - showing OTP screen');
                otpVerificationContainer.classList.remove('hidden');
                restOfCustomerInfo.classList.add('hidden');
                productDetailsSection.classList.add('hidden');
                submitSection.classList.add('hidden');
                mobileNumberDisplaySection.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error checking session:', error);
            otpVerificationContainer.classList.remove('hidden');
            restOfCustomerInfo.classList.add('hidden');
            productDetailsSection.classList.add('hidden');
            submitSection.classList.add('hidden');
            mobileNumberDisplaySection.classList.add('hidden');
        } finally {
            hideGlobalLoader();
        }
    }

    // Check session on page load
    setTimeout(() => {
        checkExistingSession();
    }, 50);

    // --- Helper Functions ---
    function mapWarrantyTypeToSalesforce(formValue) {
        // Map form dropdown values → Salesforce Asset Warranty_Type__c restricted picklist
        const warrantyMapping = {
            'Standard Warranty':   'Standard Warranty',
            'Extended Warranty':   '1+1 Year Extended Warrantyed Warranty',
            'Under AMC':           'AMC',
            'Out of warranty':     'Not Applicable'
        };
        return warrantyMapping[formValue] !== undefined ? warrantyMapping[formValue] : formValue;
    }

    function showLoader(button, text = 'Loading...') {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = text;
    }

    function hideLoader(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }

    function showGlobalLoader(text = 'Loading, please wait...') {
        const overlay = document.getElementById('global-loader-overlay');
        const textEl = document.getElementById('global-loader-text');
        if (overlay && textEl) {
            textEl.textContent = text;
            overlay.classList.remove('hidden');
        }
    }

    function hideGlobalLoader() {
        const overlay = document.getElementById('global-loader-overlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
    }

    // --- Contact Status Helpers ---

    /**
     * Returns true if the contactInfo returned by Salesforce represents an active contact.
     * Inactive is signalled by: isActive === false  OR  status === 'Inactive' (case-insensitive).
     */
    function isContactActive(contact) {
        if (!contact || !contact.SF_ContactId) return false;
        if (contact.isActive === false) return false;
        if (contact.status && contact.status.toLowerCase() === 'inactive') return false;
        return true;
    }

    /**
     * Show the GST/PAN search gate. Optionally pass `inactiveMode = true`
     * to update the heading/subtitle copy and reveal the inactive notice banner.
     */
    function showGstPanSearchSection(inactiveMode = false) {
        const notice = document.getElementById('inactiveContactNotice');
        const title  = document.getElementById('gstPanSearchTitle');
        const sub    = document.getElementById('gstPanSearchSubtitle');

        otpVerificationContainer.classList.add('hidden');
        otpVerificationContainer.classList.remove('otp-active');

        const sectionTitle = document.getElementById('sectionTitle');
        if (sectionTitle) {
            sectionTitle.textContent = inactiveMode ? 'Account Verification Required' : 'Verification Required';
        }

        if (inactiveMode) {
            if (notice) notice.classList.remove('hidden');
            if (title)  title.innerHTML  = '<i class="bi bi-person-x-fill"></i> Account Inactive';
            if (sub)    sub.textContent  = 'Please verify your business identity using GSTIN or PAN to reactivate your service request.';
        } else {
            if (notice) notice.classList.add('hidden');
            if (title)  title.innerHTML  = '<i class="bi bi-building-check"></i> Mobile number not registered';
            if (sub)    sub.textContent  = 'Please search your business details using GSTIN or PAN.';
        }

        gstPanSearchSection.classList.remove('hidden');
        gstinOrPanInput.focus();
    }

    function showError(message) {
        showToast(message, 'error');
    }

    function showSuccess(message) {
        showToast(message, 'success');
    }

    function validateGST(gstNumber) {
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/;
        return gstRegex.test(gstNumber.toUpperCase());
    }

    function validatePAN(panNumber) {
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        return panRegex.test(panNumber.toUpperCase());
    }

    function validateEmail(email) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailRegex.test(email);
    }

    // --- Populate Form with Customer Data (Scenario 1) ---
    function populateFormWithCustomerData(data) {
        if (!data) return;

        if (data.message === 'No Contact Found') {
            console.log('New customer - Scenario 2 path');
            return;
        }

        activeScenario = 'Scenario_1';

        // Existing customer - populate contact info
        if (data.contactInfo) {
            const contact = data.contactInfo;
            contactId = contact.SF_ContactId;

            if (contact.contactName) {
                document.getElementById('name').value = contact.contactName;
                document.getElementById('name').readOnly = true;
                document.getElementById('name').classList.add('readonly-input');
            }

            if (contact.Email) {
                document.getElementById('email').value = contact.Email;
                document.getElementById('email').readOnly = true;
                document.getElementById('email').classList.add('readonly-input');
            }
        }

        // Normalize accountInfo - API returns a single object, handle both forms defensively
        const accountInfoRaw = data.accountInfo;
        const accountInfoArr = Array.isArray(accountInfoRaw)
            ? accountInfoRaw
            : (accountInfoRaw && accountInfoRaw.SF_AccountId ? [accountInfoRaw] : []);

        // Set Business Name from default account
        if (accountInfoArr.length > 0) {
            const defaultAccount = accountInfoArr.find(a => a.isDefaultAccount === true) || accountInfoArr[0];
            if (defaultAccount) {
                accountId = defaultAccount.SF_AccountId;
                document.getElementById('companyName').value = defaultAccount.accountName || '';
                document.getElementById('companyName').readOnly = true;
                document.getElementById('companyName').classList.add('readonly-input');

                // Always show GSTIN section for Scenario 1
                gstinDisplaySection.classList.remove('hidden');
                if (defaultAccount.gstin) {
                    // Value exists - show as readonly
                    gstinReadonly.value    = defaultAccount.gstin;
                    gstinReadonly.readOnly = true;
                    gstinReadonly.classList.add('readonly-input');
                    gstinReadonly.removeAttribute('placeholder');
                } else {
                    // No GSTIN on file - allow user to enter it
                    gstinReadonly.value    = '';
                    gstinReadonly.readOnly = false;
                    gstinReadonly.classList.remove('readonly-input');
                    gstinReadonly.placeholder = 'Enter GSTIN (optional)';
                    // Update label to hint it's optional
                    const gstinLabel = gstinDisplaySection.querySelector('label');
                    if (gstinLabel) gstinLabel.textContent = 'GSTIN (optional)';
                }

                // Always show PAN section for Scenario 1
                panDisplaySection.classList.remove('hidden');
                if (defaultAccount.pan) {
                    // Value exists - show as readonly
                    panReadonly.value    = defaultAccount.pan;
                    panReadonly.readOnly = true;
                    panReadonly.classList.add('readonly-input');
                    panReadonly.removeAttribute('placeholder');
                } else {
                    // No PAN on file - allow user to enter it
                    panReadonly.value    = '';
                    panReadonly.readOnly = false;
                    panReadonly.classList.remove('readonly-input');
                    panReadonly.placeholder = 'Enter PAN (optional)';
                    const panLabel = panDisplaySection.querySelector('label');
                    if (panLabel) panLabel.textContent = 'PAN (optional)';
                }
            }
        }

        // Populate Address Select dropdown
        customerType.innerHTML = '<option value="">Select Address</option>';
        if (accountInfoArr.length > 0) {
            accountInfoArr.forEach((account, index) => {
                if (account.billingAddress && account.billingAddress.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = 'account_' + index;
                    option.textContent = account.billingAddress;
                    option.dataset.accountId   = account.SF_AccountId;
                    option.dataset.accountName = account.accountName || '';
                    option.dataset.address     = account.billingAddress;

                    if (account.isDefaultAccount) {
                        option.selected = true;
                    }
                    customerType.appendChild(option);
                }
            });

            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = 'Want to add another Address?';
            customerType.appendChild(newOption);
        } else {
            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = 'Add Address';
            customerType.appendChild(newOption);
        }

        // Load registered assets - API returns assetList at the root level
        registeredAssets = [];
        if (data.assetList && data.assetList.length > 0) {
            registeredAssets = data.assetList;
        }

        addressSelectGroup.classList.remove('hidden');
    }

    // --- Fetch Products from Salesforce Item Master ---
    async function fetchProducts() {
        if (productsList.length > 0) return;
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'getProducts'
                })
            });

            const data = await response.json();
            if (data && (data.success || data.products)) {
                productsList = data.products || [];
                console.log('Products loaded:', productsList.length);
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    // --- Reveal Form Screen 2 ---
    function revealForm() {
        const sectionTitle = document.getElementById('sectionTitle');
        if (sectionTitle) {
            sectionTitle.textContent = '1. Customer Information';
            sectionTitle.style.textAlign = 'left';
        }
        updateProgress('customer');

        otpVerificationContainer.classList.add('hidden');
        otpVerificationContainer.classList.remove('otp-active');
        if (gstPanSearchSection) {
            gstPanSearchSection.classList.add('hidden');
        }

        // Show mobile number readonly
        mobileNumberDisplaySection.classList.remove('hidden');
        mobileNumberReadonly.value = mobileNumber.value;

        // Show Customer Info fields
        restOfCustomerInfo.classList.remove('hidden');

        // Initialize dynamic product items container (Clear and add initial card)
        productRequestsContainer.innerHTML = '';
        productCards = [];
        cardCount = 0;
        addProductCard();

        productDetailsSection.classList.remove('hidden');
        submitSection.classList.remove('hidden');
        updateProgress('service');

        setTimeout(() => {
            restOfCustomerInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }

    // --- OTP Verification Logic ---
    sendOtpBtn.addEventListener('click', async () => {
        const mobile = mobileNumber.value.trim();
        if (mobile.length !== 10 || !/^[0-9]{10}$/.test(mobile)) {
            showFieldError('mobileNumber', 'Please enter a valid 10-digit mobile number.');
            return;
        }
        clearFieldError('mobileNumber');

        showLoader(sendOtpBtn, 'Sending...');

        let isSuccess = false;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'sendOTP',
                    mobileNumber: mobile
                })
            });

            const data = await response.json();

            if (data.success) {
                isSuccess = true;
                otpGroup.classList.remove('hidden');
                otpVerificationContainer.classList.add('otp-active');
                // Update the saved text so hideLoader restores to "Resend OTP" not "Send OTP"
                sendOtpBtn.dataset.originalText = 'Resend OTP';

                if (data.otp) {
                    console.log('For testing, OTP code is:', data.otp);
                }
                showSuccess('OTP sent to ' + mobile);
                otpInput.focus();
            } else {
                showError(data.message || 'Failed to send OTP. Please try again.');
            }
        } catch (error) {
            console.error('Error sending OTP:', error);
            showError('Network error. Please check your connection.');
        } finally {
            hideLoader(sendOtpBtn);
            if (isSuccess) {
                sendOtpBtn.disabled = true;
                mobileNumber.disabled = true;
                let timeLeft = 300; // 5 minutes
                const formatTime = (seconds) => {
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${mins}:${secs.toString().padStart(2, '0')}`;
                };
                sendOtpBtn.textContent = `Resend OTP (${formatTime(timeLeft)})`;

                const timerInterval = setInterval(() => {
                    timeLeft--;
                    sendOtpBtn.textContent = `Resend OTP (${formatTime(timeLeft)})`;

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        sendOtpBtn.disabled = false;
                        mobileNumber.disabled = false;
                        sendOtpBtn.textContent = 'Resend OTP';
                        sendOtpBtn.dataset.originalText = 'Resend OTP';
                    }
                }, 1000);
            }
        }
    });

    verifyOtpBtn.addEventListener('click', async () => {
        const mobile = mobileNumber.value.trim();
        const otp = otpInput.value.trim();

        if (otp.length < 4) {
            showFieldError('otp', 'Please enter a valid OTP.');
            return;
        }
        clearFieldError('otp');

        showLoader(verifyOtpBtn, 'Verifying...');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'verifyOTP',
                    mobileNumber: mobile,
                    otp: otp
                })
            });

            const data = await response.json();

            if (data.success) {
                verifyOtpBtn.textContent = 'Verified OK';
                verifyOtpBtn.classList.remove('btn-secondary');
                verifyOtpBtn.classList.add('btn-primary');
                verifyOtpBtn.disabled = true;
                otpInput.readOnly = true;
                mobileNumber.readOnly = true;
                sendOtpBtn.disabled = true;

                isOtpVerified = true;
                customerData = data.customerData;

                // Determine whether Contact is active, inactive, or missing
                const contact    = customerData?.contactInfo;
                const hasContact = contact && contact.SF_ContactId;
                const active     = isContactActive(contact);

                if (hasContact && active) {
                    // Scenario 1: Active existing contact — go directly to the form
                    activeScenario = 'Scenario_1';
                    contactId = contact.SF_ContactId;
                    populateFormWithCustomerData(customerData);
                    await fetchProducts();
                    revealForm();
                } else if (hasContact && !active) {
                    // Inactive contact — show GST/PAN gate with informational notice
                    showGstPanSearchSection(true);
                } else {
                    // Scenario 2: No contact at all — show GST/PAN gate normally
                    showGstPanSearchSection(false);
                }
            } else {
                showError(data.message || 'Verification failed');
            }
        } catch (error) {
            console.error('Error verifying OTP:', error);
            showError('Verification failed. Check network connection.');
        } finally {
            hideLoader(verifyOtpBtn);
        }
    });

    // --- GSTIN/PAN Search Action (Scenario 2) ---
    gstinOrPanSearchBtn.addEventListener('click', async () => {
        const gstPanVal = gstinOrPanInput.value.trim().toUpperCase();

        if (gstPanVal === '') {
            gstinOrPanSearchError.textContent = 'Please enter a valid GSTIN or PAN.';
            gstinOrPanSearchError.style.display = 'block';
            return;
        }

        if (gstPanVal.length === 15) {
            if (!validateGST(gstPanVal)) {
                gstinOrPanSearchError.textContent = 'Invalid GST Number format. Required: 15 characters (e.g., 22AAAAA0000A1Z5)';
                gstinOrPanSearchError.style.display = 'block';
                return;
            }
        } else if (gstPanVal.length === 10) {
            if (!validatePAN(gstPanVal)) {
                gstinOrPanSearchError.textContent = 'Invalid PAN Number format. Required: 10 characters (e.g., ABCDE1234F)';
                gstinOrPanSearchError.style.display = 'block';
                return;
            }
        } else {
            gstinOrPanSearchError.textContent = 'GSTIN must be 15 characters, or PAN must be 10 characters.';
            gstinOrPanSearchError.style.display = 'block';
            return;
        }

        clearFieldError('gstinOrPanInput');
        showLoader(gstinOrPanSearchBtn, 'Searching...');
        showGlobalLoader('Searching business details in Salesforce...');
        searchGstPanVal = gstPanVal;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'searchByGSTPAN',
                    gstinOrPan: gstPanVal
                })
            });

            const data = await response.json();
            console.log('GST/PAN Search Response:', data);

            // The API returns accountInfo as a single object (not array)
            // Normalize: wrap in array if it's an object with SF_AccountId
            const hasAccount = data && (
                (data.accountInfo && data.accountInfo.SF_AccountId) ||
                (Array.isArray(data.accountInfo) && data.accountInfo.length > 0)
            );

            // Scenario 2A: Business Exists (Account found)
            if (hasAccount) {
                activeScenario = 'Scenario_2A';

                // Normalize accountInfo to always work as a single object
                const account = Array.isArray(data.accountInfo) ? data.accountInfo[0] : data.accountInfo;
                accountId = account.SF_AccountId;

                // Scenario 2A: Always create a new contact record with the given mobile number
                const existingContact = null;
                contactId = null;

                // --- Populate Business / Company Info (readonly) ---
                companyName.value = account.accountName || '';
                companyName.readOnly = true;
                companyName.classList.add('readonly-input');

                // Show readonly GSTIN display
                if (account.gstin) {
                    gstinDisplaySection.classList.remove('hidden');
                    gstinReadonly.value = account.gstin;
                } else if (gstPanVal.length === 15) {
                    gstinDisplaySection.classList.remove('hidden');
                    gstinReadonly.value = gstPanVal;
                }

                // Show readonly PAN display
                if (account.pan) {
                    panDisplaySection.classList.remove('hidden');
                    panReadonly.value = account.pan;
                } else if (gstPanVal.length === 10) {
                    panDisplaySection.classList.remove('hidden');
                    panReadonly.value = gstPanVal;
                } else if (gstPanVal.length === 15) {
                    panDisplaySection.classList.remove('hidden');
                    panReadonly.value = gstPanVal.substring(2, 12);
                }

                gstPanSearchSection.classList.add('hidden');
                mobileNumberDisplaySection.classList.remove('hidden');
                mobileNumberReadonly.value = mobileNumber.value;

                // --- Auto-populate Contact Info ---
                const nameInput  = document.getElementById('name');
                const emailInput = document.getElementById('email');

                if (existingContact) {
                    // Contact already exists - prefill and lock as readonly
                    nameInput.value    = existingContact.contactName || '';
                    nameInput.readOnly = true;
                    nameInput.classList.add('readonly-input');

                    emailInput.value    = existingContact.Email || '';
                    emailInput.readOnly = true;
                    emailInput.classList.add('readonly-input');
                } else {
                    // No existing contact - clear fields so user can type their details
                    nameInput.value    = '';
                    nameInput.readOnly = false;
                    nameInput.classList.remove('readonly-input');

                    emailInput.value    = '';
                    emailInput.readOnly = false;
                    emailInput.classList.remove('readonly-input');
                }

                // --- Populate Address dropdown from billing address ---
                customerType.innerHTML = '<option value="">Select Address</option>';

                const billingAddr = account.billingAddress ? account.billingAddress.trim() : '';
                if (billingAddr) {
                    const option = document.createElement('option');
                    option.value = 'account_0';
                    option.textContent = billingAddr;
                    option.dataset.accountId   = account.SF_AccountId;
                    option.dataset.accountName = account.accountName || '';
                    option.dataset.address     = billingAddr;
                    option.selected = true;
                    customerType.appendChild(option);
                }

                const newOption = document.createElement('option');
                newOption.value = 'new';
                newOption.textContent = 'Want to add another Address?';
                customerType.appendChild(newOption);

                addressSelectGroup.classList.remove('hidden');

                // --- Populate registered assets (from root assetList) ---
                registeredAssets = [];
                if (data.assetList && data.assetList.length > 0) {
                    registeredAssets = data.assetList;
                }

                await fetchProducts();
                revealForm();
            } else {
                // Scenario 2B: Business Does Not Exist (Account not found)
                activeScenario = 'Scenario_2B';
                accountId = null;
                contactId = null;

                gstPanSearchSection.classList.add('hidden');
                mobileNumberDisplaySection.classList.remove('hidden');
                mobileNumberReadonly.value = mobileNumber.value;

                companyName.value = '';
                companyName.readOnly = false;
                companyName.classList.remove('readonly-input');

                // Show input fields for GSTIN, PAN, and attachment file upload
                gstinInputSection.classList.remove('hidden');
                panInputSection.classList.remove('hidden');
                gstinPanAttachmentGroup.classList.remove('hidden');

                if (gstPanVal.length === 15) {
                    gstinInput.value = gstPanVal;
                    panInput.value = gstPanVal.substring(2, 12);
                } else {
                    panInput.value = gstPanVal;
                }

                // Business Registration requires direct Address Details inputs
                addressSelectGroup.classList.add('hidden');
                newAddressFields.classList.remove('hidden');
                addressInputs.forEach(input => {
                    if (input.id !== 'address2' && input.id !== 'landmark') {
                        input.setAttribute('required', 'required');
                    }
                });

                registeredAssets = [];

                // Clear Name & Email inputs for editing
                document.getElementById('name').value = '';
                document.getElementById('name').readOnly = false;
                document.getElementById('name').classList.remove('readonly-input');
                document.getElementById('email').value = '';
                document.getElementById('email').readOnly = false;
                document.getElementById('email').classList.remove('readonly-input');

                await fetchProducts();
                revealForm();
            }
        } catch (error) {
            console.error('Error searching GST/PAN:', error);
            gstinOrPanSearchError.textContent = 'Failed to search business. Please try again.';
            gstinOrPanSearchError.style.display = 'block';
        } finally {
            hideLoader(gstinOrPanSearchBtn);
            hideGlobalLoader();
        }
    });

    // --- Address Select Event ---
    customerType.addEventListener('change', (e) => {
        if (e.target.value === 'new') {
            newAddressFields.classList.remove('hidden');
            addressInputs.forEach(input => {
                if (input.id !== 'address2' && input.id !== 'landmark') {
                    input.setAttribute('required', 'required');
                }
            });
        } else {
            newAddressFields.classList.add('hidden');
            addressInputs.forEach(input => input.removeAttribute('required'));
        }
    });

    // --- Screen 2: Dynamic Multi-Product Card Controllers ---
    addProductCardBtn.addEventListener('click', addProductCard);

    function addProductCard() {
        const index = cardCount++;
        productCards.push(index);

        const cardHtml = createProductRequestCard(index);
        productRequestsContainer.insertAdjacentHTML('beforeend', cardHtml);

        initializeProductCard(index);
        updateProgress('service');
    }

    // Refresh asset dropdowns in ALL existing cards when registeredAssets changes
    function refreshAssetDropdowns() {
        productCards.forEach(idx => {
            const assetSelect = document.getElementById('assetNumber_' + idx);
            const assetDropdownList = document.getElementById('assetDropdownList_' + idx);
            if (!assetSelect) return;

            // Rebuild native <select> options
            assetSelect.innerHTML = '<option value="">Select an Asset...</option>';
            registeredAssets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.assetId;
                option.textContent = buildAssetLabel(asset);
                assetSelect.appendChild(option);
            });
            const otherOpt = document.createElement('option');
            otherOpt.value = 'other';
            otherOpt.textContent = 'Other (Register New Product)';
            assetSelect.appendChild(otherOpt);

            // Rebuild custom search overlay list
            if (assetDropdownList) {
                renderAssetDropdownById(idx, registeredAssets);
            }
        });
    }

    // Build a human-readable label for an asset
    function buildAssetLabel(asset) {
        let label = asset.assetName || 'Unknown Asset';
        if (asset.serialNumber) label += ' | SN: ' + asset.serialNumber;
        if (asset.warrantyType) label += ' | ' + asset.warrantyType;
        if (asset.purchaseDate) label += ' | Purchased: ' + asset.purchaseDate;
        return label;
    }

    // Render search overlay items for a specific card by index
    function renderAssetDropdownById(idx, assetsToShow) {
        const assetDropdownList = document.getElementById('assetDropdownList_' + idx);
        const assetSelect      = document.getElementById('assetNumber_' + idx);
        const assetDropdownWrapper = document.getElementById('assetDropdownWrapper_' + idx);
        if (!assetDropdownList) return;

        assetDropdownList.innerHTML = assetsToShow.map(asset => `
            <div class="model-dropdown-item asset-dropdown-item" data-value="${asset.assetId}"
                 data-name="${(asset.assetName || '').replace(/"/g, '&quot;')}"
                 data-serial="${asset.serialNumber || ''}">
                <span class="asset-item-name">${asset.assetName || 'Unknown'}</span>
                <span class="asset-item-meta">
                    ${asset.serialNumber ? '<span>SN: ' + asset.serialNumber + '</span>' : ''}
                    ${asset.warrantyType ? '<span>' + asset.warrantyType + '</span>' : ''}
                    ${asset.purchaseDate ? '<span>Purchased: ' + asset.purchaseDate + '</span>' : ''}
                </span>
            </div>`).join('');

        assetDropdownList.innerHTML += `<div class="model-dropdown-item" data-value="other" data-name="Other" data-serial="">Other (Register New Product)</div>`;

        assetDropdownList.querySelectorAll('.model-dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                assetSelect.value = item.dataset.value;
                assetDropdownWrapper.classList.add('hidden');
                document.getElementById('assetSearchInput_' + idx).value = '';
                assetSelect.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }

    window.removeProductCard = function(index) {
        productCards = productCards.filter(i => i !== index);
        const card = document.querySelector(`.product-item-card[data-index="${index}"]`);
        if (card) {
            card.remove();
        }

        // Re-number labels
        const cards = productRequestsContainer.querySelectorAll('.product-item-card');
        cards.forEach((c, idx) => {
            const h4 = c.querySelector('h4');
            if (h4) {
                h4.textContent = `Product #${idx + 1}`;
            }
        });
    };

    function initializeProductCard(index) {
        const assetSelect = document.getElementById('assetNumber_' + index);
        const assetSearchInput = document.getElementById('assetSearchInput_' + index);
        const assetDropdownWrapper = document.getElementById('assetDropdownWrapper_' + index);
        const assetDropdownList = document.getElementById('assetDropdownList_' + index);

        const modelSelect = document.getElementById('modelNumber_' + index);
        const modelSelectionGroup = document.getElementById('modelSelectionGroup_' + index);
        const modelSearchInput = document.getElementById('modelSearchInput_' + index);
        const modelDropdownWrapper = document.getElementById('modelDropdownWrapper_' + index);
        const modelDropdownList = document.getElementById('modelDropdownList_' + index);

        const manualModelGroup = document.getElementById('manualModelGroup_' + index);
        const manualModel = document.getElementById('manualModel_' + index);
        const productName = document.getElementById('productName_' + index);
        const productCategory = document.getElementById('productCategory_' + index);
        const serialNumber = document.getElementById('serialNumber_' + index);
        const purchaseDate = document.getElementById('purchaseDate_' + index);
        const installationDate = document.getElementById('installationDate_' + index);
        const warrantySelect = document.getElementById('warrantyStatus_' + index);
        const invoiceGroup = document.getElementById('invoiceGroup_' + index);
        const invoiceCopy = document.getElementById('invoiceCopy_' + index);
        const fileNameDisplay = document.getElementById('fileNameDisplay_' + index);
        initializeUploadZone(invoiceCopy, fileNameDisplay, 'Click to browse or drag file here');

        // 1. Populate registered assets dropdown (native <select> with rich labels)
        assetSelect.innerHTML = '<option value="">Select an Asset...</option>';
        registeredAssets.forEach(asset => {
            const option = document.createElement('option');
            option.value = asset.assetId;
            option.textContent = buildAssetLabel(asset);
            assetSelect.appendChild(option);
        });
        const otherAssetOption = document.createElement('option');
        otherAssetOption.value = 'other';
        otherAssetOption.textContent = 'Other (Register New Product)';
        assetSelect.appendChild(otherAssetOption);

        // 2. Populate product models dropdown (Item Master)
        modelSelect.innerHTML = '<option value="">Select a Model...</option>';
        productsList.forEach(prod => {
            const option = document.createElement('option');
            option.value = prod.salesforceId || prod.id;
            // Show: ModelNo - ProductName [Category]
            let displayText = prod.name || '';
            if (prod.family) {
                displayText = displayText + ' [' + prod.family + ']';
            }
            if (prod.modelNo) {
                displayText = prod.modelNo + ' — ' + displayText;
            }
            option.textContent = displayText;
            modelSelect.appendChild(option);
        });
        const otherModelOption = document.createElement('option');
        otherModelOption.value = 'other';
        otherModelOption.textContent = 'Other (Add New)';
        modelSelect.appendChild(otherModelOption);

        // 3. Asset Selection Change handler
        assetSelect.addEventListener('change', () => {
            const val = assetSelect.value;
            if (val === 'other') {
                // Register new asset: reveal model selector
                modelSelectionGroup.classList.remove('hidden');
                modelSelect.setAttribute('required', 'required');

                productName.value = '';
                productCategory.value = '';
                serialNumber.value = '';
                purchaseDate.value = '';
                warrantySelect.value = '';
                if (installationDate) {
                    installationDate.value = '';
                    installationDate.readOnly = false;
                    installationDate.classList.remove('readonly-input');
                }

                productName.readOnly = true;
                productCategory.readOnly = true;
                serialNumber.readOnly = false;
                purchaseDate.readOnly = false;

                productName.classList.add('readonly-input');
                productCategory.classList.add('readonly-input');
                serialNumber.classList.remove('readonly-input');
                purchaseDate.classList.remove('readonly-input');

                serialNumber.setAttribute('required', 'required');
                purchaseDate.setAttribute('required', 'required');

                // Clear any existing asset summary badge when selecting Other
                const badge = document.getElementById('assetSummary_' + index);
                if (badge) {
                    badge.remove();
                }
            } else if (val === '') {
                modelSelectionGroup.classList.add('hidden');
                modelSelect.removeAttribute('required');
                manualModelGroup.classList.add('hidden');
                manualModel.removeAttribute('required');

                productName.value = '';
                productCategory.value = '';
                serialNumber.value = '';
                purchaseDate.value = '';
                warrantySelect.value = '';
                if (installationDate) {
                    installationDate.value = '';
                    installationDate.readOnly = false;
                    installationDate.classList.remove('readonly-input');
                }

                serialNumber.removeAttribute('required');
                purchaseDate.removeAttribute('required');

                // Clear any existing asset summary badge when clearing selection
                const badge = document.getElementById('assetSummary_' + index);
                if (badge) {
                    badge.remove();
                }
            } else {
                // Selected an existing registered asset - prefill all fields
                modelSelectionGroup.classList.add('hidden');
                modelSelect.removeAttribute('required');
                manualModelGroup.classList.add('hidden');
                manualModel.removeAttribute('required');

                const selectedAsset = registeredAssets.find(a => a.assetId === val);
                if (selectedAsset) {
                    // For registered assets the family isn't returned, so show asset name as-is
                    productName.value    = selectedAsset.assetName || '';
                    productCategory.value = '';
                    serialNumber.value   = selectedAsset.serialNumber || '';
                    purchaseDate.value   = selectedAsset.purchaseDate || '';
                    if (installationDate) {
                        const instDateVal = selectedAsset.InstallDates || selectedAsset.InstallDate || selectedAsset.installDate || '';
                        installationDate.value = instDateVal;
                        installationDate.min = selectedAsset.purchaseDate || '';
                        installationDate.readOnly = true;
                        installationDate.classList.add('readonly-input');
                    }

                    serialNumber.readOnly = true;
                    purchaseDate.readOnly = true;
                    serialNumber.classList.add('readonly-input');
                    purchaseDate.classList.add('readonly-input');
                    serialNumber.removeAttribute('required');
                    purchaseDate.removeAttribute('required');

                    // Map Salesforce warrantyType string back to our select option value
                    const warrantyKeyMap = {
                        'Standard Warranty': 'Standard Warranty',
                        'Negotiated Warranty': 'Standard Warranty',
                        '1 Year Standard Warranty': 'Standard Warranty',
                        '1+1 Year Extended Warrantyed Warranty': 'Extended Warranty',
                        '1+2 Year Extended Warranty': 'Extended Warranty',
                        'Not Applicable': 'Out of warranty',
                        'AMC': 'Under AMC'
                    };
                    const mappedWarranty = warrantyKeyMap[selectedAsset.warrantyType] || '';
                    warrantySelect.value = mappedWarranty;
                    warrantySelect.dispatchEvent(new Event('change', { bubbles: true }));

                    // Show an asset summary badge beneath the dropdown
                    let badge = document.getElementById('assetSummary_' + index);
                    if (!badge) {
                        badge = document.createElement('div');
                        badge.id = 'assetSummary_' + index;
                        badge.className = 'asset-summary-badge';
                        assetSelect.parentNode.insertBefore(badge, assetSelect.nextSibling);
                    }
                    const instDateVal = selectedAsset.InstallDates || selectedAsset.InstallDate || selectedAsset.installDate || '';
                    badge.innerHTML = `
                        <span class="badge-chip">Asset: ${selectedAsset.assetName || ''}</span>
                        ${selectedAsset.serialNumber ? `<span class="badge-chip">SN: ${selectedAsset.serialNumber}</span>` : ''}
                        ${selectedAsset.warrantyType ? `<span class="badge-chip">Warranty: ${selectedAsset.warrantyType}</span>` : ''}
                        ${selectedAsset.purchaseDate ? `<span class="badge-chip">Purchased: ${selectedAsset.purchaseDate}</span>` : ''}
                        ${instDateVal ? `<span class="badge-chip">Installed: ${instDateVal}</span>` : ''}
                    `;
                }
            }
        });

        // 4. Model Selection Change handler
        modelSelect.addEventListener('change', () => {
            const val = modelSelect.value;
            if (val === 'other') {
                manualModelGroup.classList.remove('hidden');
                manualModel.setAttribute('required', 'required');
                productName.value = '';
                productName.dataset.rawName = '';
                productCategory.value = '';
                productName.readOnly = false;
                productCategory.readOnly = false;
                productName.classList.remove('readonly-input');
                productCategory.classList.remove('readonly-input');
            } else {
                manualModelGroup.classList.add('hidden');
                manualModel.removeAttribute('required');
                productName.readOnly = true;
                productCategory.readOnly = true;
                productName.classList.add('readonly-input');
                productCategory.classList.add('readonly-input');

                const selectedProd = productsList.find(p => (p.salesforceId || p.id) === val);
                if (selectedProd) {
                    // Show Category alongside Product Name for better context
                    const catPrefix = selectedProd.family ? selectedProd.family + ' — ' : '';
                    productName.value                    = catPrefix + (selectedProd.name || '');
                    productName.dataset.rawName           = selectedProd.name || '';
                    productCategory.value                = selectedProd.family || '';
                }
            }
        });

        // 5. Warranty Selection Change handler
        warrantySelect.addEventListener('change', () => {
            const val = warrantySelect.value;
            if (val === 'Standard Warranty' || val === 'Out of warranty') {
                invoiceGroup.classList.remove('hidden');
                invoiceCopy.setAttribute('required', 'required');
            } else {
                invoiceGroup.classList.add('hidden');
                invoiceCopy.removeAttribute('required');
            }
        });

        invoiceCopy.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
                fileNameDisplay.style.color = '#333333';
            } else {
                fileNameDisplay.textContent = 'Click to browse or drag file here';
                fileNameDisplay.style.color = '';
            }
        });

        // 5a. Date Validation constraints & listeners
        const todayStr = new Date().toISOString().split('T')[0];
        if (purchaseDate) {
            purchaseDate.max = todayStr;
        }
        if (installationDate) {
            installationDate.max = todayStr;
        }

        if (purchaseDate && installationDate) {
            purchaseDate.addEventListener('change', () => {
                const purVal = purchaseDate.value;
                if (purVal > todayStr) {
                    showFieldError(purchaseDate.id, 'Purchase Date cannot be a future date.');
                    purchaseDate.value = '';
                    installationDate.min = '';
                } else {
                    clearFieldError(purchaseDate.id);
                    installationDate.min = purVal;
                }

                // Re-validate installation date if already selected
                const instVal = installationDate.value;
                if (instVal) {
                    if (instVal > todayStr) {
                        showFieldError(installationDate.id, 'Installation Date cannot be a future date.');
                    } else if (purVal && instVal < purVal) {
                        showFieldError(installationDate.id, 'Installation Date must be on or after the Purchase Date.');
                    } else {
                        clearFieldError(installationDate.id);
                    }
                }
            });

            installationDate.addEventListener('change', () => {
                const purVal = purchaseDate.value;
                const instVal = installationDate.value;
                if (instVal) {
                    if (instVal > todayStr) {
                        showFieldError(installationDate.id, 'Installation Date cannot be a future date.');
                    } else if (purVal && instVal < purVal) {
                        showFieldError(installationDate.id, 'Installation Date must be on or after the Purchase Date.');
                    } else {
                        clearFieldError(installationDate.id);
                    }
                } else {
                    clearFieldError(installationDate.id);
                }
            });
        }

        // 6. Custom search overlay for registered assets - using shared helper
        renderAssetDropdownById(index, registeredAssets);

        // Prevent native dropdown list from opening and show custom searchable dropdown wrapper
        assetSelect.addEventListener('mousedown', (e) => {
            e.preventDefault();
            // Clear stale asset summary badge immediately when user interacts with Select Asset dropdown
            const badge = document.getElementById('assetSummary_' + index);
            if (badge) {
                badge.remove();
            }

            const isHidden = assetDropdownWrapper.classList.contains('hidden');
            // Hide other dropdown wrapper
            document.querySelectorAll('.model-dropdown-wrapper').forEach(w => w.classList.add('hidden'));
            if (isHidden) {
                assetDropdownWrapper.classList.remove('hidden');
                assetSearchInput.focus();
            }
        });

        assetSearchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            const filtered = term.length === 0
                ? registeredAssets
                : registeredAssets.filter(asset =>
                    (asset.assetName || '').toLowerCase().includes(term) ||
                    (asset.serialNumber || '').toLowerCase().includes(term) ||
                    (asset.warrantyType || '').toLowerCase().includes(term)
                  );
            if (filtered.length === 0) {
                assetDropdownList.innerHTML = '<div class="model-dropdown-item disabled">No assets found</div>';
            } else {
                renderAssetDropdownById(index, filtered);
            }
        });

        // 7. Custom search overlay for Salesforce Product models
        function renderModelDropdown(productsToShow) {
            modelDropdownList.innerHTML = productsToShow.map(prod => {
                const modelPart    = prod.modelNo   ? `<span class="model-item-no">${prod.modelNo}</span>` : '';
                const namePart     = prod.name      ? `<span class="model-item-name">${prod.name}</span>` : '';
                const categoryPart = prod.family    ? `<span class="model-item-category">${prod.family}</span>` : '';
                return `<div class="model-dropdown-item" data-value="${prod.salesforceId || prod.id}" data-name="${prod.name}" data-category="${prod.family || ''}">${modelPart}${namePart}${categoryPart}</div>`;
            }).join('');
            modelDropdownList.innerHTML += `<div class="model-dropdown-item" data-value="other" data-name="Other" data-category="">Other (Add New)</div>`;

            modelDropdownList.querySelectorAll('.model-dropdown-item').forEach(item => {
                item.addEventListener('click', () => {
                    modelSelect.value = item.dataset.value;
                    modelDropdownWrapper.classList.add('hidden');
                    modelSearchInput.value = '';
                    modelSelect.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }

        renderModelDropdown(productsList);

        // Prevent native dropdown list from opening and show custom searchable dropdown wrapper
        modelSelect.addEventListener('mousedown', (e) => {
            e.preventDefault();
            const isHidden = modelDropdownWrapper.classList.contains('hidden');
            // Hide other dropdown wrapper
            document.querySelectorAll('.model-dropdown-wrapper').forEach(w => w.classList.add('hidden'));
            if (isHidden) {
                modelDropdownWrapper.classList.remove('hidden');
                modelSearchInput.focus();
            }
        });

        modelSearchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            if (term.length === 0) {
                renderModelDropdown(productsList);
            } else {
                const filtered = productsList.filter(prod =>
                    (prod.name || '').toLowerCase().includes(term) ||
                    (prod.modelNo || '').toLowerCase().includes(term) ||
                    (prod.family || '').toLowerCase().includes(term)
                );
                if (filtered.length === 0) {
                    modelDropdownList.innerHTML = '<div class="model-dropdown-item disabled">No models found</div>';
                } else {
                    renderModelDropdown(filtered);
                }
            }
        });

        // Close dropdown wrappers when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target !== assetSelect && e.target !== assetSearchInput && !assetDropdownWrapper.contains(e.target)) {
                assetDropdownWrapper.classList.add('hidden');
            }
            if (e.target !== modelSelect && e.target !== modelSearchInput && !modelDropdownWrapper.contains(e.target)) {
                modelDropdownWrapper.classList.add('hidden');
            }
        });
    }

    // --- reCAPTCHA Interactivity ---
    if (recaptchaCheckbox && recaptchaText) {
        const toggleRecaptcha = () => {
            isRecaptchaChecked = !isRecaptchaChecked;
            if (isRecaptchaChecked) {
                recaptchaCheckbox.classList.add('checked');
            } else {
                recaptchaCheckbox.classList.remove('checked');
            }
        };

        recaptchaCheckbox.addEventListener('click', toggleRecaptcha);
        recaptchaText.addEventListener('click', toggleRecaptcha);
    }

    // --- Form Submission Flow ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        function validateCustomerDetails() {
            if (gstinInputSection.classList.contains('hidden')) {
                gstinInput.removeAttribute('required');
            } else {
                gstinInput.setAttribute('required', 'required');
            }

            if (panInputSection.classList.contains('hidden')) {
                panInput.removeAttribute('required');
            } else {
                panInput.setAttribute('required', 'required');
            }

            if (gstinPanAttachmentGroup.classList.contains('hidden')) {
                gstinPanAttachment.removeAttribute('required');
            } else {
                gstinPanAttachment.setAttribute('required', 'required');
            }

            const requiredResult = validateVisibleRequiredControls(form);
            let firstInvalid = requiredResult.firstInvalid || null;

            const gstSectionVisible = !gstinInputSection.classList.contains('hidden');
            const panSectionVisible = !panInputSection.classList.contains('hidden');
            const gstVal = gstinInput.value.trim().toUpperCase();
            const panVal = panInput.value.trim().toUpperCase();

            if (gstSectionVisible && gstVal && !validateGST(gstVal)) {
                showFieldError('gstinInput', 'Invalid GST Number format. Required: 15 characters (e.g., 22AAAAA0000A1Z5)');
                if (!firstInvalid) firstInvalid = gstinInput;
            }

            if (panSectionVisible && panVal && !validatePAN(panVal)) {
                showFieldError('panInput', 'Invalid PAN Number format. Required: 10 characters (e.g., ABCDE1234F)');
                if (!firstInvalid) firstInvalid = panInput;
            }

            if (gstSectionVisible && panSectionVisible && gstVal && panVal && gstVal.length >= 12) {
                const embeddedPan = gstVal.substring(2, 12);
                if (embeddedPan !== panVal) {
                    showFieldError('panInput', 'PAN Number does not match the PAN embedded in GST Number (positions 3-12).');
                    if (!firstInvalid) firstInvalid = panInput;
                }
            }

            const emailInput = document.getElementById('email');
            const emailVal = emailInput ? emailInput.value.trim() : '';
            if (emailInput && emailVal && !validateEmail(emailVal)) {
                showFieldError('email', 'Please enter a valid email address (e.g., name@domain.com).');
                if (!firstInvalid) firstInvalid = emailInput;
            }

            return { isValid: !firstInvalid, firstInvalid };
        }

        if (!isOtpVerified) {
            showToast('Please verify your mobile number with OTP before submitting.', 'error');
            return;
        }

        if (!isRecaptchaChecked) {
            showToast('Please verify that you are not a robot.', 'error');
            return;
        }

        // Validate required fields
        const validationResult = validateCustomerDetails();
        if (!validationResult.isValid) {
            validationResult.firstInvalid.focus();
            return;
        }
        // Validate that we have at least one product added
        if (productCards.length === 0) {
            showToast('Please add at least one product asset to raise a service request.', 'error');
            return;
        }

        // Validate dates on all product cards
        let firstDateInvalidField = null;
        const todayStr = new Date().toISOString().split('T')[0];
        for (const idx of productCards) {
            const assetSelectVal = document.getElementById('assetNumber_' + idx).value;
            // Only validate dates if registering a new product / selecting 'other'
            if (assetSelectVal === 'other') {
                const purchaseDateEl = document.getElementById('purchaseDate_' + idx);
                const installationDateEl = document.getElementById('installationDate_' + idx);

                if (purchaseDateEl) {
                    const purVal = purchaseDateEl.value;
                    if (!purVal) {
                        showFieldError(purchaseDateEl.id, 'Purchase Date is required.');
                        if (!firstDateInvalidField) firstDateInvalidField = purchaseDateEl;
                    } else if (purVal > todayStr) {
                        showFieldError(purchaseDateEl.id, 'Purchase Date cannot be a future date.');
                        if (!firstDateInvalidField) firstDateInvalidField = purchaseDateEl;
                    } else {
                        clearFieldError(purchaseDateEl.id);
                    }
                }

                if (installationDateEl) {
                    const purVal = purchaseDateEl ? purchaseDateEl.value : '';
                    const instVal = installationDateEl.value;
                    if (instVal) {
                        if (instVal > todayStr) {
                            showFieldError(installationDateEl.id, 'Installation Date cannot be a future date.');
                            if (!firstDateInvalidField) firstDateInvalidField = installationDateEl;
                        } else if (purVal && instVal < purVal) {
                            showFieldError(installationDateEl.id, 'Installation Date must be on or after the Purchase Date.');
                            if (!firstDateInvalidField) firstDateInvalidField = installationDateEl;
                        } else {
                            clearFieldError(installationDateEl.id);
                        }
                    }
                }
            }
        }

        if (firstDateInvalidField) {
            firstDateInvalidField.focus();
            return;
        }


        const submitBtn = document.getElementById('submitBtn');
        showLoader(submitBtn, 'Submitting...');
        showGlobalLoader('Submitting service request to Salesforce...');

        try {
            // Loop through each product card to submit separate Salesforce request cases
            const submitPromises = productCards.map(async (idx) => {
                const formData = {};

                // Build contact & business parameters based on Screen 1 Scenario
                if (activeScenario === 'Scenario_1') {
                    formData.contactId = contactId;

                    const selectedAddress = customerType.value;
                    if (selectedAddress && selectedAddress.startsWith('account_')) {
                        // Read accountId from the selected option's dataset (set when building dropdown)
                        const selectedOption = customerType.options[customerType.selectedIndex];
                        formData.accountId = selectedOption.dataset.accountId || accountId;
                    } else if (selectedAddress === 'new') {
                        formData.accountName     = companyName.value.trim();
                        formData.accountPhone    = mobileNumber.value;
                        formData.accountCategory = 'Direct';
                        formData.billingStreet      = document.getElementById('address1').value;
                        formData.billingCity        = document.getElementById('city').value;
                        formData.billingState       = document.getElementById('state').value;
                        formData.billingCountry     = document.getElementById('country').value || 'India';
                        formData.billingPostalCode  = document.getElementById('zipcode').value;
                    } else {
                        // Default: use the known accountId
                        formData.accountId = accountId;
                    }
                    // Include optional GSTIN / PAN if provided by user
                    const gstinVal = gstinReadonly?.value?.trim();
                    if (gstinVal) {
                        formData.gstin = gstinVal;
                    }
                    const panVal = panReadonly?.value?.trim();
                    if (panVal) {
                        formData.pan = panVal;
                    }
                    if (contactId) {
                        // Existing contact was found during GST/PAN search - just pass the IDs
                        formData.contactId = contactId;
                    } else {
                        // New contact registration linked to existing account
                        const fullNameVal = document.getElementById('name').value.trim();
                        const parts = fullNameVal.split(' ');
                        formData.firstName    = parts[0] || '';
                        formData.lastName     = parts.slice(1).join(' ') || parts[0];
                        formData.contactPhone = mobileNumber.value;
                        formData.contactEmail = document.getElementById('email').value.trim();
                    }


                } else if (activeScenario === 'Scenario_2A') {
                    formData.accountId = accountId;

                    // Include GSTIN / PAN retrieved during verification search
                    const gstinVal = gstinReadonly?.value?.trim();
                    if (gstinVal) {
                        formData.gstin = gstinVal;
                    }
                    const panVal = panReadonly?.value?.trim();
                    if (panVal) {
                        formData.pan = panVal;
                    }

                    if (contactId) {
                        // Existing contact was found during GST/PAN search - just pass the IDs
                        formData.contactId = contactId;
                    } else {
                        // New contact registration linked to existing account
                        const fullNameVal = document.getElementById('name').value.trim();
                        const parts = fullNameVal.split(' ');
                        formData.firstName    = parts[0] || '';
                        formData.lastName     = parts.slice(1).join(' ') || parts[0];
                        formData.contactPhone = mobileNumber.value;
                        formData.contactEmail = document.getElementById('email').value.trim();
                    }

                    const selectedAddress = customerType.value;
                    if (selectedAddress === 'new') {
                        formData.billingStreet     = document.getElementById('address1').value;
                        formData.billingCity       = document.getElementById('city').value;
                        formData.billingState      = document.getElementById('state').value;
                        formData.billingCountry    = document.getElementById('country').value || 'India';
                        formData.billingPostalCode = document.getElementById('zipcode').value;
                    }
                } else if (activeScenario === 'Scenario_2B') {
                    formData.accountName = companyName.value.trim();
                    formData.accountPhone = mobileNumber.value;
                    formData.accountCategory = 'Direct';
                    formData.gstin = gstinInput.value.trim().toUpperCase();
                    formData.pan = panInput.value.trim().toUpperCase();

                    formData.billingStreet = document.getElementById('address1').value;
                    formData.billingCity = document.getElementById('city').value;
                    formData.billingState = document.getElementById('state').value;
                    formData.billingCountry = document.getElementById('country').value || 'India';
                    formData.billingPostalCode = document.getElementById('zipcode').value;

                    const fullNameVal = document.getElementById('name').value.trim();
                    const parts = fullNameVal.split(' ');
                    formData.firstName = parts[0] || '';
                    formData.lastName = parts.slice(1).join(' ') || parts[0];
                    formData.contactPhone = mobileNumber.value;
                    formData.contactEmail = document.getElementById('email').value.trim();
                }

                // Build product details for this card
                const assetSelectVal = document.getElementById('assetNumber_' + idx).value;
                if (assetSelectVal !== 'other') {
                    formData.assetId = assetSelectVal;
                } else {
                    // Use raw product name (without category prefix) for Salesforce payload
                    const productNameEl = document.getElementById('productName_' + idx);
                    formData.assetName = (productNameEl.dataset.rawName || productNameEl.value).trim();
                    formData.product2Id = document.getElementById('modelNumber_' + idx).value;
                    formData.purchaseDate = document.getElementById('purchaseDate_' + idx).value;
                    formData.warrantyType = mapWarrantyTypeToSalesforce(document.getElementById('warrantyStatus_' + idx).value);
                    formData.serialNumber = document.getElementById('serialNumber_' + idx).value.trim();
                    formData.price = 0;
                    const instDateInput = document.getElementById('installationDate_' + idx);
                    if (instDateInput && instDateInput.value) {
                        formData.InstallDates = instDateInput.value;
                        formData.InstallDate = instDateInput.value;
                        formData.installDate = instDateInput.value;
                    }
                }

                // Service Request details for this card
                // Note: Salesforce endpoint expects 'purpose' not 'serviceCategory'
                formData.purpose = document.getElementById('serviceCategory_' + idx).value;
                formData.priority = 'Normal';
                formData.callType = document.getElementById('warrantyStatus_' + idx).value;

                const issueDesc = document.getElementById('issueDescription_' + idx).value;
                if (issueDesc && issueDesc.trim() !== '') {
                    formData.description = issueDesc.trim();
                }

                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'submitServiceRequest',
                        formData: formData,
                        customerName: document.getElementById('name')?.value?.trim() || '',
                        customerEmail: document.getElementById('email')?.value?.trim() || '',
                        customerMobile: mobileNumber?.value || ''
                    })
                });

                return await response.json();
            });

            const results = await Promise.all(submitPromises);
            console.log('Submission loop results:', results);

            const successfulResults = results.filter(r =>
                r.success === true ||
                r.status === 'Success' ||
                r.status === 'success' ||
                r.parentSRId ||
                r.caseId
            );

            if (successfulResults.length === results.length) {
                const caseIds = successfulResults.map((r, i) => {
                    const nestedCase = (r.childCases && r.childCases[0]) ? (r.childCases[0].caseNumber || r.childCases[0].childSRId) : null;
                    return r.caseNumber || r.CaseNumber || nestedCase || r.parentSRId || r.caseId || r.childSRId || `TRU-${Math.floor(10000 + Math.random() * 90000)}`;
                }).join(', ');

                form.classList.add('hidden');
                document.querySelector('.form-header').classList.add('hidden');
                ticketIdDisplay.textContent = caseIds;

                // Show summary using the actual first active product card index
                const firstIdx = productCards[0];
                document.getElementById('detailMobileNumber').textContent = mobileNumber.value;
                const svcCatEl = document.getElementById('serviceCategory_' + firstIdx);
                document.getElementById('detailPurpose').textContent = svcCatEl ? svcCatEl.options[svcCatEl.selectedIndex].text : '';
                const modelEl = document.getElementById('modelNumber_' + firstIdx);
                document.getElementById('detailModel').textContent = (document.getElementById('productName_' + firstIdx) || {}).value || (modelEl ? modelEl.options[modelEl.selectedIndex].text : '');
                document.getElementById('detailSerial').textContent = (document.getElementById('serialNumber_' + firstIdx) || {}).value || '';

                const warrantySelectEl = document.getElementById('warrantyStatus_' + firstIdx);
                document.getElementById('detailWarranty').textContent = warrantySelectEl ? warrantySelectEl.options[warrantySelectEl.selectedIndex].text : '';
                document.getElementById('detailStatus').textContent = successfulResults[0].status || 'Pending';

                document.getElementById('serviceRequestDetails').style.display = 'block';
                successMessage.classList.remove('hidden');
                updateProgress('submit');
                showToast('Your service request was submitted successfully.', 'success');
                failureMessage.classList.add('hidden');

                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Some or all cases failed
                form.classList.add('hidden');
                document.querySelector('.form-header').classList.add('hidden');

                const errorText = results.map(r => r.message || 'Submission failed.').join(' | ');
                document.getElementById('failureMessage-text').textContent = 'One or more of your service request submissions failed.';
                document.getElementById('failureErrorMessage').textContent = errorText;

                failureMessage.classList.remove('hidden');
                showToast('We could not submit the request. Please review the details and try again.', 'error');
                successMessage.classList.add('hidden');

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Error submitting forms:', error);
            form.classList.add('hidden');
            document.querySelector('.form-header').classList.add('hidden');

            document.getElementById('failureMessage-text').textContent = 'Network error. Please check your connection and try again.';
            document.getElementById('failureErrorMessage').textContent = 'Error: ' + error.message;

            failureMessage.classList.remove('hidden');
            successMessage.classList.add('hidden');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            hideLoader(submitBtn);
            hideGlobalLoader();
        }
    });

    // Helper to generate dynamic cards html dynamically in Javascript code
    function createProductRequestCard(index) {
        return `
        <div class="product-item-card" data-index="${index}">
            ${index > 0 ? `<button type="button" class="remove-product-btn" onclick="removeProductCard(${index})"><i class="bi bi-trash"></i> Remove</button>` : ''}
            <h4>Product #${index + 1}</h4>

            <div class="form-grid">
                <!-- Asset Selection -->
                <div class="form-group">
                    <label for="assetNumber_${index}">Select Asset <span class="required-asterisk">*</span></label>
                    <select id="assetNumber_${index}" name="assetNumber_${index}" required class="asset-number-select">
                        <option value="">Select an Asset...</option>
                    </select>
                    <span class="error-message" id="assetNumber_${index}-error"></span>
                    <div id="assetDropdownWrapper_${index}" class="model-dropdown-wrapper hidden">
                        <input type="text" id="assetSearchInput_${index}" placeholder="Search asset..." class="model-search-input">
                        <div id="assetDropdownList_${index}" class="model-dropdown-list"></div>
                    </div>
                </div>

                <!-- Model Number Selection -->
                <div class="form-group hidden" id="modelSelectionGroup_${index}">
                    <label for="modelNumber_${index}">Model Number <span class="required-asterisk">*</span></label>
                    <select id="modelNumber_${index}" name="modelNumber_${index}">
                        <option value="">Select a Model...</option>
                    </select>
                    <span class="error-message" id="modelNumber_${index}-error"></span>
                    <div id="modelDropdownWrapper_${index}" class="model-dropdown-wrapper hidden">
                        <input type="text" id="modelSearchInput_${index}" placeholder="Search model..." class="model-search-input">
                        <div id="modelDropdownList_${index}" class="model-dropdown-list"></div>
                    </div>
                </div>

                <!-- Manual Model entry -->
                <div class="form-group hidden" id="manualModelGroup_${index}">
                    <label for="manualModel_${index}">Enter New Model Number <span class="required-asterisk">*</span></label>
                    <input type="text" id="manualModel_${index}" name="manualModel_${index}">
                    <span class="error-message" id="manualModel_${index}-error"></span>
                </div>

                <div class="form-group" id="productNameGroup_${index}">
                    <label for="productName_${index}">Product Name</label>
                    <input type="text" id="productName_${index}" name="productName_${index}" readonly class="readonly-input">
                </div>

                <div class="form-group" id="productCategoryGroup_${index}">
                    <label for="productCategory_${index}">Product Category</label>
                    <input type="text" id="productCategory_${index}" name="productCategory_${index}" readonly class="readonly-input">
                </div>

                <div class="form-group" id="serialNumberGroup_${index}">
                    <label for="serialNumber_${index}">Serial Number <span class="required-asterisk">*</span></label>
                    <input type="text" id="serialNumber_${index}" name="serialNumber_${index}" placeholder="Enter Serial Number">
                    <span class="error-message" id="serialNumber_${index}-error"></span>
                </div>

                <div class="form-group" id="purchaseDateGroup_${index}">
                    <label for="purchaseDate_${index}">Purchase Date <span class="required-asterisk">*</span></label>
                    <input type="date" id="purchaseDate_${index}" name="purchaseDate_${index}">
                    <span class="error-message" id="purchaseDate_${index}-error"></span>
                </div>

                <div class="form-group" id="installationDateGroup_${index}">
                    <label for="installationDate_${index}">Installation Date</label>
                    <input type="date" id="installationDate_${index}" name="installationDate_${index}">
                    <span class="error-message" id="installationDate_${index}-error"></span>
                </div>

                <div class="form-group">
                    <label for="warrantyStatus_${index}">Warranty Status <span class="required-asterisk">*</span></label>
                    <select id="warrantyStatus_${index}" name="warrantyStatus_${index}" required class="warranty-status-select">
                        <option value="">Select Warranty Status</option>
                        <option value="Standard Warranty">Standard Warranty</option>
                        <option value="Extended Warranty">Extended Warranty</option>
                        <option value="Under AMC">Under AMC</option>
                        <option value="Out of warranty">Out of warranty</option>
                    </select>
                    <span class="error-message" id="warrantyStatus_${index}-error"></span>
                </div>

                <div class="form-group">
                    <label for="serviceCategory_${index}">Service Category <span class="required-asterisk">*</span></label>
                    <select id="serviceCategory_${index}" name="serviceCategory_${index}" required>
                        <option value="">Select Service Category</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Installation">Installation</option>
                        <option value="PM Service">PM Service</option>
                        <option value="Quote Request">Quote Request</option>
                    </select>
                    <span class="error-message" id="serviceCategory_${index}-error"></span>
                </div>

                <div class="form-group full-width hidden" id="invoiceGroup_${index}">
                    <label>Invoice Copy <span class="required-asterisk">*</span> (Required for 1 year Standard & Out of Warranty)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="invoiceCopy_${index}" name="invoiceCopy_${index}" accept=".jpg,.jpeg,.png,.pdf" class="file-input-hidden invoice-file-input">
                        <label for="invoiceCopy_${index}" class="file-upload-label">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="upload-icon">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span id="fileNameDisplay_${index}">Click to browse or drag file here</span><small>JPG, PNG, or PDF accepted</small>
                        </label>
                    </div>
                    <span class="error-message" id="invoiceCopy_${index}-error"></span>
                </div>

                <div class="form-group full-width">
                    <label for="issueDescription_${index}">Issue Description</label>
                    <textarea id="issueDescription_${index}" name="issueDescription_${index}" rows="3" placeholder="Describe the issue in detail..."></textarea>
                </div>
            </div>
        </div>`;
    }

    // --- Reset Flow (Try Again / Raise Another Request) ---
    async function resetAndReload() {
        try {
            await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'clearSession'
                })
            });
        } catch (e) {
            console.error('Error clearing session:', e);
        }
        location.reload();
    }

    const tryAgainBtn = document.getElementById('tryAgainBtn');
    if (tryAgainBtn) {
        tryAgainBtn.addEventListener('click', resetAndReload);
    }

    const raiseAnotherBtn = document.getElementById('raiseAnotherBtn');
    if (raiseAnotherBtn) {
        raiseAnotherBtn.addEventListener('click', resetAndReload);
    }
});
