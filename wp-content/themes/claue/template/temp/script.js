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

    // Address Elements
    const customerType = document.getElementById('customerType');
    const newAddressFields = document.getElementById('newAddressFields');
    const addressInputs = newAddressFields.querySelectorAll('input');

    // Product Elements
    const assetNumber = document.getElementById('assetNumber');
    const assetDropdownWrapper = document.getElementById('assetDropdownWrapper');
    const assetSearchInput = document.getElementById('assetSearchInput');
    const assetDropdownList = document.getElementById('assetDropdownList');
    
    const modelNumber = document.getElementById('modelNumber');
    const modelSelectionGroup = document.getElementById('modelSelectionGroup');
    const manualModelGroup = document.getElementById('manualModelGroup');
    const manualModel = document.getElementById('manualModel');
    const productName = document.getElementById('productName');
    const productCategory = document.getElementById('productCategory');

    // Warranty Elements
    const warrantyStatus = document.getElementById('warrantyStatus');
    const invoiceGroup = document.getElementById('invoiceGroup');
    const invoiceCopy = document.getElementById('invoiceCopy');
    
    // GST & PAN Elements
    const gstPanNoteGroup = document.getElementById('gstPanNoteGroup');
    const gstNumberGroup = document.getElementById('gstNumberGroup');
    const gstCertificateGroup = document.getElementById('gstCertificateGroup');
    const panNumberGroup = document.getElementById('panNumberGroup');
    const panFileGroup = document.getElementById('panFileGroup');
    const gstNumber = document.getElementById('gstNumber');
    const gstCertificate = document.getElementById('gstCertificate');
    const panNumber = document.getElementById('panNumber');
    const panFile = document.getElementById('panFile');

    // Success Elements
    const successMessage = document.getElementById('successMessage');
    const ticketIdDisplay = document.getElementById('ticketIdDisplay');

    // Store customer data
    let customerData = null;
    
    // OTP Verification Status
    let isOtpVerified = false;

    // API Base URL
    const API_URL = window.location.origin + '/wp-content/themes/claue/template/api-handler.php';

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
            // Not critical - user can still use the form
        }
    }

    // --- Check Session on Page Load ---
    async function checkExistingSession() {
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
                console.log('✓ Existing session found for:', data.mobile);
                
                // Set verification flag
                isOtpVerified = true;
                
                // Set mobile number
                mobileNumber.value = data.mobile;
                
                // Fetch products immediately
                await fetchProducts();
                
                // Reveal form immediately (don't wait for customer data)
                revealForm();
                
                // Fetch customer data in the background (after form is shown)
                fetchCustomerDataInBackground();
                
                console.log('✓ Form restored from session');
            } else {
                console.log('✗ No existing session - showing OTP screen');
                // Make sure OTP verification container is visible
                otpVerificationContainer.classList.remove('hidden');
                restOfCustomerInfo.classList.add('hidden');
                productDetailsSection.classList.add('hidden');
                issueDetailsSection.classList.add('hidden');
                submitSection.classList.add('hidden');
                mobileNumberDisplaySection.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error checking session:', error);
            // On error, show OTP screen
            otpVerificationContainer.classList.remove('hidden');
            restOfCustomerInfo.classList.add('hidden');
            productDetailsSection.classList.add('hidden');
            issueDetailsSection.classList.add('hidden');
            submitSection.classList.add('hidden');
            mobileNumberDisplaySection.classList.add('hidden');
        }
    }

    // Check session on page load (with minimal delay to ensure DOM is ready)
    setTimeout(() => {
        checkExistingSession();
    }, 50);

    // --- Mock Data ---
    const mockProductData = {
        'TF-100': { name: 'Trufrost Single Door Cooler', category: 'Commercial Refrigeration' },
        'TF-250': { name: 'Trufrost Deep Freezer', category: 'Freezers' },
        'TF-500': { name: 'Trufrost Display Fridge', category: 'Display Units' }
    };

    // --- Helper Functions ---
    
    /**
     * Map form warranty values to Salesforce picklist values
     */
    function mapWarrantyTypeToSalesforce(formValue) {
        const warrantyMapping = {
            '1_year_standard': '1 Year Standard Warranty',
            '1_plus_1_extended': '1+1 Year Extended Warranty',
            '1_plus_2_extended': '1+2 Years Extended Warranty',
            'amc': 'AMC',
            'out_of_warranty': 'Out of Warranty'
        };
        
        return warrantyMapping[formValue] || formValue;
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

    function showError(message) {
        alert(message);
    }

    function showSuccess(message) {
        alert(message);
    }

    // --- GST and PAN Validation Functions ---
    
    /**
     * Validate GST Number Format
     * GST Format: 15 characters - Pattern: ^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$
     * Example: 22AAAAA0000A1Z5
     */
    function validateGST(gstNumber) {
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/;
        return gstRegex.test(gstNumber.toUpperCase());
    }
    
    /**
     * Validate PAN Number Format
     * PAN Format: 10 characters - Pattern: ^[A-Z]{5}[0-9]{4}[A-Z]{1}$
     * Example: ABCDE1234F
     */
    function validatePAN(panNumber) {
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        return panRegex.test(panNumber.toUpperCase());
    }
    
    /**
     * Verify PAN matches the PAN embedded in GST (positions 3-12)
     */
    function validateGSTPANMatch(gstNumber, panNumber) {
        const gstPan = gstNumber.substring(2, 12).toUpperCase();
        return gstPan === panNumber.toUpperCase();
    }
    
    /**
     * Show GST validation error
     */
    function showGSTError(message) {
        const errorElement = document.getElementById('gstNumberError');
        if (message) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            gstNumber.classList.add('is-invalid');
        } else {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            gstNumber.classList.remove('is-invalid');
        }
    }
    
    /**
     * Show PAN validation error
     */
    function showPANError(message) {
        const errorElement = document.getElementById('panNumberError');
        if (message) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            panNumber.classList.add('is-invalid');
        } else {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            panNumber.classList.remove('is-invalid');
        }
    }

    // --- Populate Form with Customer Data ---
    function populateFormWithCustomerData(data) {
        if (!data) return;

        // Check if customer exists
        if (data.message === 'No Contact Found') {
            console.log('New customer - form will be empty');
            return;
        }

        // Existing customer - populate contact info
        if (data.contactInfo) {
            const contact = data.contactInfo;
            
            if (contact.contactName) {
                document.getElementById('name').value = contact.contactName;
                // Make name readonly since it came from API
                document.getElementById('name').readOnly = true;
                document.getElementById('name').classList.add('readonly-input');
            }
            
            if (contact.Email) {
                document.getElementById('email').value = contact.Email;
                // Make email readonly since it came from API
                document.getElementById('email').readOnly = true;
                document.getElementById('email').classList.add('readonly-input');
            }
        }

        // Find and set the default account's company name
        let defaultAccountName = '';
        if (data.accountInfo && data.accountInfo.length > 0) {
            // Find the account with isDefaultAccount = true
            const defaultAccount = data.accountInfo.find(account => account.isDefaultAccount === true);
            if (defaultAccount) {
                defaultAccountName = defaultAccount.accountName;
                document.getElementById('companyName').value = defaultAccountName;
                // Make company name readonly since it came from API
                document.getElementById('companyName').readOnly = true;
                document.getElementById('companyName').classList.add('readonly-input');
            }
        }

        // Populate address dropdown with account addresses
        const addressSelect = document.getElementById('customerType');
        addressSelect.innerHTML = '<option value="">Select Address</option>';
        
        if (data.accountInfo && data.accountInfo.length > 0) {
            data.accountInfo.forEach((account, index) => {
                if (account.billingAddress && account.billingAddress.trim() !== '') {
                    const option = document.createElement('option');
                    option.value = 'account_' + index;
                    option.textContent = account.billingAddress;
                    option.dataset.accountId = account.SF_AccountId;
                    option.dataset.accountName = account.accountName;
                    option.dataset.address = account.billingAddress;
                    
                    if (account.isDefaultAccount) {
                        option.selected = true;
                        // Auto-populate assets from default account
                        if (account.assetList && account.assetList.length > 0) {
                            populateAssetDropdown(account.assetList);
                        }
                    }
                    
                    addressSelect.appendChild(option);
                }
            });
            
            // For existing customers with addresses
            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = 'Want to add another Address?';
            addressSelect.appendChild(newOption);
        } else {
            // For new customers without addresses
            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = 'Add Address';
            addressSelect.appendChild(newOption);
        }

        // Auto-populate Model Number from old Service Ticket (legacy data)
        if (data.assetInfo && data.assetInfo.length > 0) {
            // Get the most recent asset/service ticket
            const latestAsset = data.assetInfo[0];
            
            if (latestAsset.modelNumber || latestAsset.product2Id) {
                // Try to find and select the model in the dropdown
                const modelSelect = document.getElementById('modelNumber');
                const modelValue = latestAsset.product2Id || latestAsset.modelNumber;
                
                // Check if this model exists in the dropdown
                for (let i = 0; i < modelSelect.options.length; i++) {
                    if (modelSelect.options[i].value === modelValue) {
                        modelSelect.value = modelValue;
                        // Trigger change event to populate product name and category
                        modelSelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }
            
            // Auto-populate Serial Number if available
            if (latestAsset.serialNumber) {
                document.getElementById('serialNumber').value = latestAsset.serialNumber;
            }
            
            // Auto-populate Purchase Date if available
            if (latestAsset.purchaseDate) {
                document.getElementById('purchaseDate').value = latestAsset.purchaseDate;
            }
            
            // Auto-populate Warranty Status if available
            if (latestAsset.warrantyStatus) {
                document.getElementById('warrantyStatus').value = latestAsset.warrantyStatus;
                // Trigger change event to show/hide invoice group
                document.getElementById('warrantyStatus').dispatchEvent(new Event('change'));
            }
        }
    }

    // --- Populate Asset Dropdown ---
    function populateAssetDropdown(assets) {
        const assetSelect = document.getElementById('assetNumber');
        
        // Clear existing options except the first one
        while (assetSelect.options.length > 1) {
            assetSelect.remove(1);
        }
        
        // Store all assets globally
        window.allAssets = assets || [];
        
        // Add assets to select
        if (Array.isArray(assets)) {
            assets.forEach(asset => {
                const option = document.createElement('option');
                option.value = asset.assetId;
                option.textContent = asset.assetName + ' (SN: ' + asset.serialNumber + ')';
                option.dataset.assetId = asset.assetId;
                option.dataset.assetName = asset.assetName;
                option.dataset.serialNumber = asset.serialNumber;
                option.dataset.purchaseDate = asset.purchaseDate;
                option.dataset.warrantyType = asset.warrantyType;
                option.dataset.productId = asset.productId;
                
                assetSelect.appendChild(option);
            });
        }
        
        // Add "Other (Add New)" option at the end
        const otherOption = document.createElement('option');
        otherOption.value = 'other';
        otherOption.textContent = 'Other (Add New)';
        assetSelect.appendChild(otherOption);
        
        // Function to render dropdown list
        function renderAssetDropdownList(assetsToShow) {
            assetDropdownList.innerHTML = assetsToShow.map(asset => {
                // return `<div class="model-dropdown-item" data-value="${asset.assetId}" data-name="${asset.assetName}" data-serial="${asset.serialNumber}">${asset.assetName} (SN: ${asset.serialNumber})</div>`;
                return `<div class="model-dropdown-item" data-value="${asset.assetId}" data-name="${asset.assetName}" data-serial="${asset.serialNumber}">${asset.assetName}</div>`;
            }).join('');
            
            // Add "Other" option
            assetDropdownList.innerHTML += `<div class="model-dropdown-item" data-value="other" data-name="Other" data-serial="">Other (Add New)</div>`;
            
            // Add click handlers to items
            document.querySelectorAll('.model-dropdown-item').forEach(item => {
                item.addEventListener('click', () => {
                    const value = item.dataset.value;
                    
                    // Set the select value
                    assetSelect.value = value;
                    
                    // Hide dropdown
                    assetDropdownWrapper.classList.add('hidden');
                    assetSearchInput.value = '';
                    
                    if (value === 'other') {
                        // Show model selection and fetch products
                        modelSelectionGroup.classList.remove('hidden');
                        modelNumber.setAttribute('required', 'required');
                        manualModelGroup.classList.add('hidden');
                        manualModel.removeAttribute('required');
                        
                        // Clear previous asset data
                        document.getElementById('productName').value = '';
                        document.getElementById('productCategory').value = '';
                        document.getElementById('serialNumber').value = '';
                        document.getElementById('purchaseDate').value = '';
                        document.getElementById('warrantyStatus').value = '';
                        document.getElementById('warrantyStatus').dispatchEvent(new Event('change'));
                        
                        // Make Serial Number and Purchase Date EDITABLE for new asset
                        document.getElementById('serialNumber').readOnly = false;
                        document.getElementById('serialNumber').classList.remove('readonly-input');
                        document.getElementById('purchaseDate').readOnly = false;
                        document.getElementById('purchaseDate').classList.remove('readonly-input');
                        
                        // Fetch products from Salesforce
                        fetchProducts();
                    } else {
                        // Asset selected - hide model selection
                        modelSelectionGroup.classList.add('hidden');
                        modelNumber.removeAttribute('required');
                        manualModelGroup.classList.add('hidden');
                        manualModel.removeAttribute('required');
                        
                        // Auto-fill asset details
                        const selectedAsset = assetsToShow.find(a => a.assetId === value);
                        if (selectedAsset) {
                            document.getElementById('productName').value = selectedAsset.assetName;
                            document.getElementById('productCategory').value = selectedAsset.warrantyType || '';
                            document.getElementById('serialNumber').value = selectedAsset.serialNumber;
                            document.getElementById('purchaseDate').value = selectedAsset.purchaseDate;
                            
                            // Make Serial Number and Purchase Date READONLY for existing asset
                            document.getElementById('serialNumber').readOnly = true;
                            document.getElementById('serialNumber').classList.add('readonly-input');
                            document.getElementById('purchaseDate').readOnly = true;
                            document.getElementById('purchaseDate').classList.add('readonly-input');
                            
                            if (selectedAsset.warrantyType) {
                                document.getElementById('warrantyStatus').value = selectedAsset.warrantyType;
                                document.getElementById('warrantyStatus').dispatchEvent(new Event('change'));
                            }
                        }
                    }
                    
                    // Trigger change event
                    assetSelect.dispatchEvent(new Event('change'));
                });
            });
        }
        
        // Initial render of all assets
        renderAssetDropdownList(window.allAssets);
        
        // Handle select click to show dropdown
        assetSelect.addEventListener('click', () => {
            assetDropdownWrapper.classList.remove('hidden');
            assetSearchInput.focus();
        });
        
        // Handle search input
        assetSearchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                renderAssetDropdownList(window.allAssets);
            } else {
                // Filter assets
                const filtered = window.allAssets.filter(asset => {
                    const assetName = (asset.assetName || '').toLowerCase();
                    const serialNumber = (asset.serialNumber || '').toLowerCase();
                    
                    return assetName.includes(searchTerm) || serialNumber.includes(searchTerm);
                });
                
                if (filtered.length === 0) {
                    assetDropdownList.innerHTML = '<div class="model-dropdown-item disabled">No assets found</div>';
                } else {
                    renderAssetDropdownList(filtered);
                }
            }
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target !== assetSelect && e.target !== assetSearchInput && e.target !== assetDropdownWrapper && !assetDropdownWrapper.contains(e.target)) {
                assetDropdownWrapper.classList.add('hidden');
                assetSearchInput.value = '';
            }
        });
    }

    // --- Populate Product Dropdown ---
    function populateProductDropdown(products) {
        const modelSelect = document.getElementById('modelNumber');
        const modelDropdownWrapper = document.getElementById('modelDropdownWrapper');
        const modelSearchInput = document.getElementById('modelSearchInput');
        const modelDropdownList = document.getElementById('modelDropdownList');
        
        // Store all products globally for search
        window.allSalesforceProducts = products || [];
        window.allProducts = products || [];
        
        // Clear existing options except the first one
        while (modelSelect.options.length > 1) {
            modelSelect.remove(1);
        }
        
        // Add products to select
        if (Array.isArray(products)) {
            products.forEach(product => {
                const option = document.createElement('option');
                option.value = product.salesforceId || product.id;
                
                // Display format: "ModelNo - Name (Family)"
                let displayText = product.name || '';
                if (product.modelNo) {
                    displayText = product.modelNo + ' - ' + displayText;
                }
                if (product.family) {
                    displayText = displayText + ' (' + product.family + ')';
                }
                
                option.textContent = displayText;
                option.dataset.productId = product.salesforceId;
                option.dataset.productName = product.name;
                option.dataset.productCategory = product.family || '';
                option.dataset.modelNo = product.modelNo;
                
                modelSelect.appendChild(option);
            });
        }
        
        // Add "Other" option at the end
        const otherOption = document.createElement('option');
        otherOption.value = 'other';
        otherOption.textContent = 'Other (Add New)';
        modelSelect.appendChild(otherOption);
        
        // Function to render dropdown list
        function renderDropdownList(productsToShow) {
            modelDropdownList.innerHTML = productsToShow.map(product => {
                let displayText = product.name || '';
                // if (product.modelNo) {
                //     displayText = product.modelNo + ' - ' + displayText;
                // }
                // if (product.family) {
                //     displayText = displayText + ' (' + product.family + ')';
                // }
                
                // Display format: "ModelNo"
                if (product.modelNo) {
                    displayText = product.modelNo;
                }
                
                return `<div class="model-dropdown-item" data-value="${product.salesforceId || product.id}" data-name="${product.name}" data-category="${product.family || ''}">${displayText}</div>`;
            }).join('');
            
            // Add "Other" option
            modelDropdownList.innerHTML += `<div class="model-dropdown-item" data-value="other" data-name="Other" data-category="">Other (Add New)</div>`;
            
            // Add click handlers to items
            document.querySelectorAll('.model-dropdown-item').forEach(item => {
                item.addEventListener('click', () => {
                    const value = item.dataset.value;
                    const name = item.dataset.name;
                    const category = item.dataset.category;
                    
                    // Set the select value
                    modelSelect.value = value;
                    
                    // Update the display text
                    const selectedOption = modelSelect.options[modelSelect.selectedIndex];
                    modelSelect.dataset.displayText = selectedOption.textContent;
                    
                    // Hide dropdown
                    modelDropdownWrapper.classList.add('hidden');
                    modelSearchInput.value = '';
                    
                    // Auto-fill product name and category
                    if (value !== 'other') {
                        document.getElementById('productName').value = name;
                        document.getElementById('productCategory').value = category;
                        
                        // Hide manual model group if visible
                        manualModelGroup.classList.add('hidden');
                        manualModel.removeAttribute('required');
                    } else {
                        // For "Other" option
                        document.getElementById('productName').value = '';
                        document.getElementById('productCategory').value = '';
                        manualModelGroup.classList.remove('hidden');
                        manualModel.setAttribute('required', 'required');
                    }
                    
                    // Trigger change event
                    modelSelect.dispatchEvent(new Event('change'));
                });
            });
        }
        
        // Initial render of all products
        renderDropdownList(window.allProducts);
        
        // Handle select click to show dropdown
        modelSelect.addEventListener('click', () => {
            modelDropdownWrapper.classList.remove('hidden');
            modelSearchInput.focus();
        });
        
        // Handle search input
        modelSearchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                renderDropdownList(window.allProducts);
            } else {
                // Filter products
                const filtered = window.allProducts.filter(product => {
                    const modelNo = (product.modelNo || '').toLowerCase();
                    const name = (product.name || '').toLowerCase();
                    const family = (product.family || '').toLowerCase();
                    
                    return modelNo.includes(searchTerm) || name.includes(searchTerm) || family.includes(searchTerm);
                });
                
                if (filtered.length === 0) {
                    modelDropdownList.innerHTML = '<div class="model-dropdown-item disabled">No models found</div>';
                } else {
                    renderDropdownList(filtered);
                }
            }
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target !== modelSelect && e.target !== modelSearchInput && e.target !== modelDropdownWrapper && !modelDropdownWrapper.contains(e.target)) {
                modelDropdownWrapper.classList.add('hidden');
                modelSearchInput.value = '';
            }
        });
    }

    // --- Fetch Products from Salesforce ---
    async function fetchProducts() {
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

            if (data.success && data.products) {
                console.log('Products fetched:', data.products.length);
                // Store all Salesforce products globally for "Other" option
                window.allSalesforceProducts = data.products;
                window.allProducts = data.products;
                populateProductDropdown(data.products);
            } else if (data.products) {
                // Handle case where API returns products but success is not explicitly true
                console.log('Products fetched:', data.products.length);
                window.allSalesforceProducts = data.products;
                window.allProducts = data.products;
                populateProductDropdown(data.products);
            } else {
                console.error('Failed to fetch products:', data.message);
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        }
    }

    // --- Reveal Form Sections ---
    function revealForm() {
        // Change heading text to "1. Customer Information"
        const sectionTitle = document.getElementById('sectionTitle');
        if (sectionTitle) {
            sectionTitle.textContent = '1. Customer Information';
            sectionTitle.style.textAlign = 'left';
        }
        
        // Hide the entire OTP verification container
        if (otpVerificationContainer) {
            otpVerificationContainer.classList.add('hidden');
        }
        
        // Hide the entire mobile number input group (with Send OTP button)
        const mobileNumberGroup = mobileNumber.closest('.form-group');
        if (mobileNumberGroup) {
            mobileNumberGroup.classList.add('hidden');
        }
        
        // Hide only the OTP group (input and verify button)
        if (otpGroup) {
            otpGroup.classList.add('hidden');
        }
        
        // Make mobile number readonly and disabled
        if (mobileNumber) {
            mobileNumber.readOnly = true;
            mobileNumber.disabled = true;
            mobileNumber.classList.add('readonly-input');
        }
        
        // Show mobile number display section
        if (mobileNumberDisplaySection) {
            mobileNumberReadonly.value = mobileNumber.value;
            mobileNumberDisplaySection.classList.remove('hidden');
        }
        
        // Show rest of customer info
        restOfCustomerInfo.classList.remove('hidden');
        
        // Show product details section
        productDetailsSection.classList.remove('hidden');
        
        // Show issue details section
        issueDetailsSection.classList.remove('hidden');
        
        // Show submit button
        submitSection.classList.remove('hidden');
        
        // Smooth scroll to show the revealed content
        setTimeout(() => {
            restOfCustomerInfo.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }

    // --- OTP Logic with API Integration ---
    sendOtpBtn.addEventListener('click', async () => {
        const mobile = mobileNumber.value.trim();
        
        if (mobile.length !== 10 || !/^[0-9]{10}$/.test(mobile)) {
            showError('Please enter a valid 10-digit mobile number.');
            return;
        }

        showLoader(sendOtpBtn, 'Sending...');

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
                otpGroup.classList.remove('hidden');
                sendOtpBtn.textContent = 'Resend OTP';
                
                if (data.otp) {
                    alert('OTP sent to ' + mobile + '\n\nFor testing: ' + data.otp);
                } else {
                    showSuccess('OTP sent to ' + mobile);
                }
                
                otpInput.focus();
            } else {
                showError(data.message || 'Failed to send OTP. Please try again.');
            }
        } catch (error) {
            console.error('Error sending OTP:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            hideLoader(sendOtpBtn);
        }
    });

    verifyOtpBtn.addEventListener('click', async () => {
        const mobile = mobileNumber.value.trim();
        const otp = otpInput.value.trim();
        
        if (otp.length < 4) {
            showError('Please enter a valid OTP.');
            return;
        }

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
                verifyOtpBtn.textContent = 'Verified ✓';
                verifyOtpBtn.classList.remove('btn-secondary');
                verifyOtpBtn.classList.add('btn-primary');
                verifyOtpBtn.disabled = true;
                otpInput.readOnly = true;
                mobileNumber.readOnly = true;
                sendOtpBtn.disabled = true;
                
                // Set OTP verification flag
                isOtpVerified = true;

                customerData = data.customerData;
                populateFormWithCustomerData(customerData);
                
                // Fetch products after OTP verification
                await fetchProducts();
                
                revealForm();
            } else {
                let errorMsg = data.message || 'Verification failed';
                if (data.debug) {
                    errorMsg += '\n\nDebug: ' + data.debug;
                    console.error('Verification Error Details:', data);
                }
                showError(errorMsg);
            }
        } catch (error) {
            console.error('Error verifying OTP:', error);
            showError('Network error. Please check your connection and try again.\n\nError: ' + error.message);
        } finally {
            hideLoader(verifyOtpBtn);
        }
    });

    // --- Address Logic ---
    customerType.addEventListener('change', (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        
        if (e.target.value === 'new') {
            newAddressFields.classList.remove('hidden');
            addressInputs.forEach(input => {
                if (input.id !== 'address2' && input.id !== 'landmark') {
                    input.setAttribute('required', 'required');
                }
            });
            // Show all assets when adding new address
            if (customerData && customerData.assetList) {
                populateAssetDropdown(customerData.assetList);
            }
        } else if (e.target.value === '') {
            // No selection
            newAddressFields.classList.add('hidden');
            addressInputs.forEach(input => input.removeAttribute('required'));
            // Clear asset dropdown
            assetNumber.innerHTML = '<option value="">Select an Asset...</option>';
        } else if (e.target.value.startsWith('account_')) {
            // Existing account selected - show assets from that account
            newAddressFields.classList.add('hidden');
            addressInputs.forEach(input => input.removeAttribute('required'));
            
            const accountIndex = parseInt(e.target.value.split('_')[1]);
            const selectedAccount = customerData.accountInfo[accountIndex];
            
            if (selectedAccount && selectedAccount.assetList && selectedAccount.assetList.length > 0) {
                // Populate asset dropdown with assets from this account
                populateAssetDropdown(selectedAccount.assetList);
            } else if (customerData && customerData.assetList) {
                // No assets for this account - show all assets
                populateAssetDropdown(customerData.assetList);
            }
        }
        
        // IMPORTANT: Do NOT update company name based on address selection
        // Company name is set from the default account only and should never change
    });

    // --- Product Logic ---
    modelNumber.addEventListener('change', (e) => {
        const selectedValue = e.target.value;
        const selectedOption = e.target.options[e.target.selectedIndex];

        if (selectedValue === 'other') {
            manualModelGroup.classList.remove('hidden');
            manualModel.setAttribute('required', 'required');
            productName.value = '';
            productCategory.value = '';
            productName.readOnly = false;
            productCategory.readOnly = false;
            productName.classList.remove('readonly-input');
            productCategory.classList.remove('readonly-input');
        } else if (selectedValue === '') {
            // No selection
            manualModelGroup.classList.add('hidden');
            manualModel.removeAttribute('required');
            productName.value = '';
            productCategory.value = '';
            productName.readOnly = true;
            productCategory.readOnly = true;
            productName.classList.add('readonly-input');
            productCategory.classList.add('readonly-input');
        } else {
            // Product selected from Salesforce
            manualModelGroup.classList.add('hidden');
            manualModel.removeAttribute('required');
            productName.readOnly = true;
            productCategory.readOnly = true;
            productName.classList.add('readonly-input');
            productCategory.classList.add('readonly-input');

            // Get product data from selected option
            const productName_val = selectedOption.dataset.productName || '';
            const productCategory_val = selectedOption.dataset.productCategory || '';
            
            productName.value = productName_val;
            productCategory.value = productCategory_val;
        }
    });

    // --- Warranty Logic ---
    warrantyStatus.addEventListener('change', (e) => {
        const selectedValue = e.target.value;
        
        // Show GST/PAN note for all warranty options
        if (selectedValue) {
            gstPanNoteGroup.classList.remove('hidden');
            gstNumberGroup.classList.remove('hidden');
            gstCertificateGroup.classList.remove('hidden');
            panNumberGroup.classList.remove('hidden');
            panFileGroup.classList.remove('hidden');
            
            // Make all GST and PAN fields required
            gstNumber.setAttribute('required', 'required');
            gstCertificate.setAttribute('required', 'required');
            panNumber.setAttribute('required', 'required');
            panFile.setAttribute('required', 'required');
        } else {
            // Hide GST/PAN fields if no warranty selected
            gstPanNoteGroup.classList.add('hidden');
            gstNumberGroup.classList.add('hidden');
            gstCertificateGroup.classList.add('hidden');
            panNumberGroup.classList.add('hidden');
            panFileGroup.classList.add('hidden');
            
            gstNumber.removeAttribute('required');
            gstCertificate.removeAttribute('required');
            panNumber.removeAttribute('required');
            panFile.removeAttribute('required');
        }
        
        // Show invoice copy only for 1 year standard warranty
        if (selectedValue === '1_year_standard') {
            invoiceGroup.classList.remove('hidden');
            invoiceCopy.setAttribute('required', 'required');
        } else {
            invoiceGroup.classList.add('hidden');
            invoiceCopy.removeAttribute('required');
        }
    });

    // --- File Upload UI ---
    invoiceCopy.addEventListener('change', function() {
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameDisplay.style.color = 'var(--primary-color)';
        } else {
            fileNameDisplay.textContent = 'Click to browse or drag file here';
            fileNameDisplay.style.color = '';
        }
    });

    gstCertificate.addEventListener('change', function() {
        const fileNameDisplay = document.getElementById('gstCertificateDisplay');
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameDisplay.style.color = 'var(--primary-color)';
        } else {
            fileNameDisplay.textContent = 'Click to browse or drag file here';
            fileNameDisplay.style.color = '';
        }
    });

    panFile.addEventListener('change', function() {
        const fileNameDisplay = document.getElementById('panFileDisplay');
        if (this.files && this.files.length > 0) {
            fileNameDisplay.textContent = this.files[0].name;
            fileNameDisplay.style.color = 'var(--primary-color)';
        } else {
            fileNameDisplay.textContent = 'Click to browse or drag file here';
            fileNameDisplay.style.color = '';
        }
    });

    // --- GST and PAN Real-Time Validation ---
    
    /**
     * GST Number Validation on blur and input
     */
    gstNumber.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showGSTError('');
            return;
        }
        
        if (!validateGST(this.value)) {
            showGSTError('Invalid GST Number format. Required: 15 characters (e.g., 22AAAAA0000A1Z5)');
            return;
        }
        
        // Validate PAN match if PAN is also filled
        if (panNumber.value.trim() !== '') {
            if (!validateGSTPANMatch(this.value, panNumber.value)) {
                showGSTError('PAN Number does not match the PAN embedded in GST Number (positions 3-12)');
                return;
            }
        }
        
        showGSTError('');
    });
    
    gstNumber.addEventListener('input', function() {
        // Convert to uppercase automatically
        this.value = this.value.toUpperCase();
    });
    
    /**
     * PAN Number Validation on blur and input
     */
    panNumber.addEventListener('blur', function() {
        if (this.value.trim() === '') {
            showPANError('');
            return;
        }
        
        if (!validatePAN(this.value)) {
            showPANError('Invalid PAN Number format. Required: 10 characters (e.g., ABCDE1234F)');
            return;
        }
        
        // Validate PAN match if GST is also filled
        if (gstNumber.value.trim() !== '') {
            if (!validateGSTPANMatch(gstNumber.value, this.value)) {
                showPANError('PAN Number does not match the PAN embedded in GST Number (positions 3-12)');
                return;
            }
        }
        
        showPANError('');
    });
    
    panNumber.addEventListener('input', function() {
        // Convert to uppercase automatically
        this.value = this.value.toUpperCase();
    });

    // --- Form Submission ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!isOtpVerified) {
            alert('Please verify your mobile number with OTP before submitting.');
            return;
        }

        // Validate that Asset is selected
        if (!assetNumber.value || assetNumber.value === '') {
            alert('Please select an Asset before submitting.');
            assetNumber.focus();
            return;
        }

        // If "Other" is selected, validate Model Number
        if (assetNumber.value === 'other') {
            if (!modelNumber.value || modelNumber.value === '') {
                alert('Please select a Model Number before submitting.');
                modelNumber.focus();
                return;
            }

            // Validate that Product Name is filled (should be auto-filled)
            if (!productName.value || productName.value.trim() === '') {
                alert('Product Name is required. Please select a valid Model Number.');
                modelNumber.focus();
                return;
            }

            // Validate that Product Category is filled (should be auto-filled)
            if (!productCategory.value || productCategory.value.trim() === '') {
                alert('Product Category is required. Please select a valid Model Number.');
                modelNumber.focus();
                return;
            }
        }

        // Collect form data
        const formData = {};
        
        // Determine scenario based on customer data
        const isExistingCustomer = customerData && customerData.contactInfo;
        const selectedAddress = document.getElementById('customerType').value;
        const selectedAsset = assetNumber.value;
        
        if (isExistingCustomer) {
            // Scenario 1 or 2: Existing customer
            formData.contactId = customerData.contactInfo.SF_ContactId;
            
            // Check if address is selected (existing account)
            if (selectedAddress && selectedAddress.startsWith('account_')) {
                const accountIndex = parseInt(selectedAddress.split('_')[1]);
                const selectedAccount = customerData.accountInfo[accountIndex];
                formData.accountId = selectedAccount.SF_AccountId;
            } else if (selectedAddress === 'new') {
                // New address for existing customer - need to create new account
                formData.accountName = document.getElementById('companyName').value;
                formData.accountPhone = mobileNumber.value;
                formData.accountCategory = 'Direct';
                formData.billingStreet = document.getElementById('address1').value;
                formData.billingCity = document.getElementById('city').value;
                formData.billingState = document.getElementById('state').value;
                formData.billingCountry =  document.getElementById('country').value || 'India';
                formData.billingPostalCode = document.getElementById('zipcode').value;
            }
            
            // Check if existing asset or new asset
            if (selectedAsset !== 'other') {
                // Existing asset selected - use it
                formData.assetId = selectedAsset;
            } else {
                // New asset - create new asset with selected model
                formData.assetName = document.getElementById('productName').value;
                formData.product2Id = modelNumber.value;
                formData.purchaseDate = document.getElementById('purchaseDate').value;
                formData.warrantyType = mapWarrantyTypeToSalesforce(document.getElementById('warrantyStatus').value);
                formData.serialNumber = document.getElementById('serialNumber').value;
                formData.price = 0; // Price not captured in form
            }
            
        } else {
            // Scenario 3: New customer - create everything
            formData.accountName = document.getElementById('companyName').value;
            formData.accountPhone = mobileNumber.value;
            formData.accountCategory = 'Direct';
            
            // Billing Address
            formData.billingStreet = document.getElementById('address1').value;
            formData.billingCity = document.getElementById('city').value;
            formData.billingState = document.getElementById('state').value;
            formData.billingCountry = 'India';
            formData.billingPostalCode = document.getElementById('zipcode').value;
            
            // Contact Info
            const fullName = document.getElementById('name').value.split(' ');
            formData.firstName = fullName[0] || '';
            formData.lastName = fullName.slice(1).join(' ') || fullName[0];
            formData.contactPhone = mobileNumber.value;
            formData.contactEmail = document.getElementById('email').value;
            
            // Asset Info
            formData.assetName = document.getElementById('productName').value;
            formData.product2Id = modelNumber.value;
            formData.purchaseDate = document.getElementById('purchaseDate').value;
            formData.Warranty_Type__c = mapWarrantyTypeToSalesforce(document.getElementById('warrantyStatus').value);
            formData.serialNumber = document.getElementById('serialNumber').value;
            formData.price = 0; // Price not captured in form
        }
        
        // Service Request Info (common for all scenarios)
        formData.serviceCategory = document.getElementById('serviceCategory').value;
        formData.priority = 'Normal';
        formData.callType = 'Regular';
        
        // Add GST and PAN information if warranty is selected
        const selectedWarranty = document.getElementById('warrantyStatus').value;
        if (selectedWarranty) {
            const gstNumberVal = document.getElementById('gstNumber').value.trim();
            const panNumberVal = document.getElementById('panNumber').value.trim();
            
            // Validate GST Number if provided
            if (gstNumberVal) {
                if (!validateGST(gstNumberVal)) {
                    alert('Invalid GST Number format. Required: 15 characters (e.g., 22AAAAA0000A1Z5)');
                    gstNumber.focus();
                    return;
                }
                formData.gstin = gstNumberVal.toUpperCase();
            }
            
            // Validate PAN Number if provided
            if (panNumberVal) {
                if (!validatePAN(panNumberVal)) {
                    alert('Invalid PAN Number format. Required: 10 characters (e.g., ABCDE1234F)');
                    panNumber.focus();
                    return;
                }
                formData.pan = panNumberVal.toUpperCase();
            }
            
            // Validate PAN and GST match if both are provided
            if (gstNumberVal && panNumberVal) {
                if (!validateGSTPANMatch(gstNumberVal, panNumberVal)) {
                    alert('PAN Number does not match the PAN embedded in GST Number (positions 3-12)');
                    panNumber.focus();
                    return;
                }
            }
        }
        
        // Add issue description if provided
        const issueDesc = document.getElementById('issueDescription').value;
        if (issueDesc && issueDesc.trim() !== '') {
            formData.description = issueDesc;
        }
        
        // Add preferred visit date if provided
        const preferredVisit = document.getElementById('preferredVisit').value;
        if (preferredVisit) {
            formData.preferredVisitDate = preferredVisit;
        }

        console.log('Form Data to submit:', formData);

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        showLoader(submitBtn, 'Submitting...');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'submitServiceRequest',
                    formData: formData
                })
            });

            const data = await response.json();

            if (data.success) {
                // Generate ticket ID
                const ticketId = data.caseId || 'TRU-' + Math.floor(10000 + Math.random() * 90000);

                // Hide form and show success message
                form.classList.add('hidden');
                document.querySelector('.form-header').classList.add('hidden');

                ticketIdDisplay.textContent = ticketId;
                
                // Populate service request details
                document.getElementById('detailMobileNumber').textContent = mobileNumber.value;
                document.getElementById('detailPurpose').textContent = document.getElementById('serviceCategory').options[document.getElementById('serviceCategory').selectedIndex].text;
                document.getElementById('detailModel').textContent = document.getElementById('productName').value || document.getElementById('modelNumber').options[document.getElementById('modelNumber').selectedIndex].text;
                document.getElementById('detailSerial').textContent = document.getElementById('serialNumber').value;
                
                // Format warranty status for display
                const warrantySelect = document.getElementById('warrantyStatus');
                const warrantyText = warrantySelect.options[warrantySelect.selectedIndex].text;
                document.getElementById('detailWarranty').textContent = warrantyText;
                
                document.getElementById('detailStatus').textContent = data.status || 'Pending';
                
                // Show service request details
                serviceRequestDetails.style.display = 'block';
                
                successMessage.classList.remove('hidden');
                failureMessage.classList.add('hidden');

                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                // Show failure message
                form.classList.add('hidden');
                document.querySelector('.form-header').classList.add('hidden');
                
                document.getElementById('failureMessage-text').textContent = data.message || 'Failed to submit service request. Please try again.';
                document.getElementById('failureErrorMessage').textContent = 'Error: ' + (data.message || 'Unknown error');
                
                failureMessage.classList.remove('hidden');
                successMessage.classList.add('hidden');

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            
            // Show failure message
            form.classList.add('hidden');
            document.querySelector('.form-header').classList.add('hidden');
            
            document.getElementById('failureMessage-text').textContent = 'Network error. Please check your connection and try again.';
            document.getElementById('failureErrorMessage').textContent = 'Error: ' + error.message;
            
            failureMessage.classList.remove('hidden');
            successMessage.classList.add('hidden');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            hideLoader(submitBtn);
        }
    });
});
