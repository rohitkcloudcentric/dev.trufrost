<?php

/**
 * Template Name: Check Service Request Status
 * Description: Check service request status by mobile number via Salesforce Apex REST API
 */
get_header();
?>

<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/template/style.css?v=<?php echo filemtime(get_stylesheet_directory() . '/template/style.css'); ?>">

<style>
    /* Dedicated styling for Service Request Status feature */
    body,
    .main-content {
        font-family: var(--tf-font);
    }

    .status-hero {
        background: linear-gradient(135deg, #1554d1 0%, #0b3a93 100%);
        color: #ffffff;
        border-radius: var(--tf-radius);
        padding: 36px 30px;
        margin-bottom: 28px;
        box-shadow: 0 12px 30px rgba(21, 84, 209, 0.25);
        position: relative;
        overflow: hidden;
    }

    .status-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
        pointer-events: none;
    }

    .status-hero h1 {
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
        margin-bottom: 8px;
        color: #ffffff;
    }

    .status-hero p {
        font-size: 1rem;
        opacity: 0.9;
        max-width: 620px;
        margin-bottom: 0;
    }

    .search-box-card {
        background: #ffffff;
        border: 1px solid var(--tf-border);
        border-radius: var(--tf-radius);
        padding: 28px;
        box-shadow: var(--tf-shadow);
        margin-bottom: 28px;
    }

    .search-input-group {
        display: flex;
        gap: 12px;
        max-width: 640px;
    }

    .search-input-group input {
        font-size: 1.05rem;
        padding: 12px 16px;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .search-input-group button {
        padding: 12px 28px;
        font-weight: 700;
        font-size: 1rem;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }

    .summary-card {
        background: #ffffff;
        border: 1px solid var(--tf-border);
        border-radius: var(--tf-radius);
        padding: 20px 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 16px rgba(16, 24, 40, 0.04);
        transition: var(--transition);
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(16, 24, 40, 0.08);
    }

    .summary-card .info p {
        margin: 0;
        font-size: 0.84rem;
        color: var(--tf-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-card .info h3 {
        margin: 4px 0 0;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--tf-ink);
    }

    .summary-card .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .icon-box.total {
        background: #eef4ff;
        color: var(--tf-primary);
    }

    .icon-box.open {
        background: #fef3c7;
        color: #d97706;
    }

    .icon-box.closed {
        background: #dcfce7;
        color: #15803d;
    }

    .icon-box.cancelled {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .status-badge.open {
        background: #fffbe6;
        color: #b7791f;
        border: 1px solid #ffe58f;
    }

    .status-badge.closed {
        background: #f6ffed;
        color: #389e0d;
        border: 1px solid #b7eb8f;
    }

    .status-badge.cancelled {
        background: #fff2f0;
        color: #cf1322;
        border: 1px solid #ffa39e;
    }

    .status-badge.default {
        background: #f5f5f5;
        color: #595959;
        border: 1px solid #d9d9d9;
    }

    .sr-table-card {
        background: #ffffff;
        border: 1px solid var(--tf-border);
        border-radius: var(--tf-radius);
        box-shadow: var(--tf-shadow);
        overflow: hidden;
    }

    .filter-bar {
        padding: 16px 24px;
        background: #f8fafc;
        border-bottom: 1px solid var(--tf-border);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .filter-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 7px 16px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        border: 1px solid var(--tf-border);
        background: #ffffff;
        color: var(--tf-muted);
        cursor: pointer;
        transition: var(--transition);
    }

    .filter-tab.active,
    .filter-tab:hover {
        background: var(--tf-primary);
        color: #ffffff;
        border-color: var(--tf-primary);
    }

    .table-responsive {
        margin: 0;
    }

    .table-sr {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-sr th {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--tf-border);
    }

    .table-sr td {
        padding: 16px 18px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
        color: var(--tf-ink);
    }

    .table-sr tbody tr:hover {
        background: #f8fafc;
    }

    .ticket-number {
        font-weight: 700;
        color: var(--tf-primary);
    }

    .asset-title {
        font-weight: 600;
        color: var(--tf-ink);
    }

    .sub-text {
        font-size: 0.8rem;
        color: var(--tf-muted);
        display: block;
        margin-top: 2px;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--tf-muted);
    }

    .empty-state i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 14px;
        display: block;
    }

    .empty-state h5 {
        font-weight: 700;
        color: var(--tf-ink);
        margin-bottom: 6px;
    }

    /* Pagination Bar Styling */
    .pagination-bar {
        padding: 16px 24px;
        background: #ffffff;
        border-top: 1px solid var(--tf-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 0.88rem;
        color: var(--tf-muted);
        font-weight: 500;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .page-btn {
        min-width: 36px;
        height: 36px;
        padding: 0 12px;
        border-radius: 6px;
        border: 1px solid var(--tf-border);
        background: #ffffff;
        color: var(--tf-ink);
        font-size: 0.88rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
    }

    .page-btn:hover:not(:disabled):not(.active) {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .page-btn.active {
        background: var(--tf-primary);
        color: #ffffff;
        border-color: var(--tf-primary);
    }

    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Spinner Loader */
    .spinner-border-sm-custom {
        width: 1.2rem;
        height: 1.2rem;
        border-width: 0.15em;
    }
</style>

<body>
    <main class="main-content">
        <div class="container service-shell">

            <div class="form-header">
                <div class="header-title d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h1>Check Service Request Status</h1>
                        <p>Track your service ticket history and live real-time status from Trufrost.</p>
                    </div>
                    <div>
                        <a href="<?php echo site_url('/service-request/'); ?>" class="btn btn-secondary shadow-sm">
                            <i class="bi bi-plus-circle-fill text-primary"></i> Raise New Request
                        </a>
                    </div>
                </div>
                <span class="mandatory-note">* Marked fields are mandatory.</span>
            </div>

            <!-- Toast Messages Region -->
            <div id="statusToastRegion" class="toast-region" aria-live="polite" aria-atomic="true"></div>

            <!-- Search & OTP Card Section -->
            <div class="search-box-card" style="background: #ffffff; border: 1px solid var(--tf-border); border-radius: var(--tf-radius); padding: 28px; box-shadow: var(--tf-shadow); margin-bottom: 28px;">
                <form id="srStatusForm" novalidate>
                    <h3 style="display: flex; align-items: center; gap: 10px; margin: 0 0 22px; color: var(--tf-ink); font-size: 1.08rem; font-weight: 800;">
                        <span style="width: 4px; height: 22px; background: var(--tf-primary); border-radius: 999px; display: inline-block;"></span>
                        Please enter your registered WhatsApp mobile number to verify OTP and view request status.
                    </h3>

                    <div class="row g-3 align-items-start">
                        <!-- Mobile Number Field -->
                        <div class="col-12 col-md-6" id="srMobileGroupCol">
                            <div class="form-group verification-field mb-0">
                                <label for="srMobileNumber" class="form-label fw-bold mb-2">Mobile Number <span class="required-asterisk">*</span> <i class="bi bi-info-circle help-icon" data-bs-toggle="tooltip" title="Enter registered 10-digit mobile number"></i></label>
                                <div class="input-group input-group-equal">
                                    <div class="field-with-error flex-grow-1">
                                        <input type="tel" id="srMobileNumber" class="form-control" placeholder="Enter 10-digit number" pattern="[0-9]{10}" maxlength="10" required style="min-height: 44px;">
                                        <div id="srMobileError" class="error-message mt-2" style="display:none; color: var(--tf-danger); font-size: 0.85rem; font-weight: 600;"></div>
                                    </div>
                                    <button type="button" id="srSendOtpBtn" class="btn btn-secondary" style="min-height: 44px; white-space: nowrap;">
                                        <i class="bi bi-chat-dots me-1"></i> Send OTP
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- OTP Input Field Group (Initially Hidden in Same Row) -->
                        <div class="col-12 col-md-6" id="srOtpGroupCol" style="display: none;">
                            <div class="form-group verification-field mb-0">
                                <label for="srOtpInput" class="form-label fw-bold mb-2">Enter OTP <span class="required-asterisk">*</span></label>
                                <div class="input-group input-group-equal">
                                    <div class="field-with-error flex-grow-1">
                                        <input type="text" id="srOtpInput" class="form-control" placeholder="Enter 4-digit OTP" maxlength="4" style="min-height: 44px;">
                                        <div id="srOtpError" class="error-message mt-2" style="display:none; color: var(--tf-danger); font-size: 0.85rem; font-weight: 600;"></div>
                                    </div>
                                    <button type="button" id="srVerifyOtpBtn" class="btn btn-primary" style="min-height: 44px; white-space: nowrap;">
                                        <i class="bi bi-shield-check me-1"></i> Verify OTP
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Global Status Loader Container -->
            <div id="statusLoaderContainer" class="text-center py-5" style="display: none; background: #ffffff; border: 1px solid var(--tf-border); border-radius: var(--tf-radius); box-shadow: var(--tf-shadow); margin-bottom: 28px;">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="statusLoaderTitle">Fetching Service Request Status...</h5>
                <p class="text-muted small mb-0" id="statusLoaderSubtitle">Connecting to Salesforce REST API, please wait.</p>
            </div>

            <!-- Results Section (Initially Hidden until OTP Verified) -->
            <div id="statusResultsSection" style="display: none;">

                <!-- Account Title Header -->
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-4 gap-2">
                    <div>
                        <h4 id="accountNameHeader" class="fw-bold mb-1" style="color: var(--tf-ink);">Service History</h4>
                        <span id="accountPhoneSubtitle" class="text-muted small"></span>
                    </div>
                </div>

                <!-- Metrics Summary Cards -->
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="info">
                            <p>Total Requests</p>
                            <h3 id="statTotalCount">0</h3>
                        </div>
                        <div class="icon-box total">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="info">
                            <p>Open Requests</p>
                            <h3 id="statOpenCount">0</h3>
                        </div>
                        <div class="icon-box open">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="info">
                            <p>Closed Calls</p>
                            <h3 id="statClosedCount">0</h3>
                        </div>
                        <div class="icon-box closed">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="info">
                            <p>Cancelled Calls</p>
                            <h3 id="statCancelledCount">0</h3>
                        </div>
                        <div class="icon-box cancelled">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>

                <!-- Table Card with Filters -->
                <div class="sr-table-card">
                    <div class="filter-bar">
                        <div class="filter-tabs" id="filterTabs">
                            <button class="filter-tab active" data-filter="ALL">All Requests (<span id="tabCountAll">0</span>)</button>
                            <button class="filter-tab" data-filter="OPEN">Open (<span id="tabCountOpen">0</span>)</button>
                            <button class="filter-tab" data-filter="CLOSED">Call Closed (<span id="tabCountClosed">0</span>)</button>
                            <button class="filter-tab" data-filter="CANCELLED">Call Cancelled (<span id="tabCountCancelled">0</span>)</button>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="min-width: 380px;">
                            <select id="statusDropdownFilter" class="form-select form-select-sm" style="width: 160px; min-height: 31px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                <option value="ALL">All Statuses</option>
                                <option value="OPEN">Open</option>
                                <option value="CLOSED">Call Closed</option>
                                <option value="CANCELLED">Call Cancelled</option>
                            </select>
                            <div class="position-relative flex-grow-1">
                                <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Search ticket, model, serial...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sr align-middle">
                            <thead>
                                <tr>
                                    <th>Ticket / Case</th>
                                    <th>Status</th>
                                    <th>Asset & Model</th>
                                    <th>Address</th>
                                    <th>Purpose</th>
                                    <th>Date Opened</th>
                                    <th>Remark / Reason</th>
                                </tr>
                            </thead>
                            <tbody id="serviceRequestsTableBody">
                                <!-- Dynamic Rows populate here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="srEmptyState" class="empty-state" style="display: none;">
                        <i class="bi bi-inbox"></i>
                        <h5>No Service Requests Found</h5>
                        <p class="mb-0">No records matched your search criteria.</p>
                    </div>

                    <!-- Pagination Bar -->
                    <div id="paginationBar" class="pagination-bar" style="display: none;">
                        <div class="pagination-info" id="paginationInfo">
                            Showing 1 to 10 of 0 entries
                        </div>
                        <div class="pagination-controls" id="paginationControls">
                            <!-- Dynamic Page Buttons -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const srStatusForm = document.getElementById('srStatusForm');
            const srMobileNumber = document.getElementById('srMobileNumber');
            const srSendOtpBtn = document.getElementById('srSendOtpBtn');
            const srOtpGroupCol = document.getElementById('srOtpGroupCol');
            const srOtpInput = document.getElementById('srOtpInput');
            const srVerifyOtpBtn = document.getElementById('srVerifyOtpBtn');
            const srMobileError = document.getElementById('srMobileError');
            const srOtpError = document.getElementById('srOtpError');
            const statusResultsSection = document.getElementById('statusResultsSection');

            const statTotalCount = document.getElementById('statTotalCount');
            const statOpenCount = document.getElementById('statOpenCount');
            const statClosedCount = document.getElementById('statClosedCount');
            const statCancelledCount = document.getElementById('statCancelledCount');

            const accountNameHeader = document.getElementById('accountNameHeader');
            const accountPhoneSubtitle = document.getElementById('accountPhoneSubtitle');

            const tabCountAll = document.getElementById('tabCountAll');
            const tabCountOpen = document.getElementById('tabCountOpen');
            const tabCountClosed = document.getElementById('tabCountClosed');
            const tabCountCancelled = document.getElementById('tabCountCancelled');

            const serviceRequestsTableBody = document.getElementById('serviceRequestsTableBody');
            const srEmptyState = document.getElementById('srEmptyState');
            const filterTabs = document.getElementById('filterTabs');
            const tableSearchInput = document.getElementById('tableSearchInput');

            const paginationBar = document.getElementById('paginationBar');
            const paginationInfo = document.getElementById('paginationInfo');
            const paginationControls = document.getElementById('paginationControls');

            let rawServiceRequests = [];
            let currentFilter = 'ALL';
            let currentSearchQuery = '';
            let currentPage = 1;
            const recordsPerPage = 10;
            let verifiedMobileNumber = '';

            const apiHandlerUrl = '<?php echo get_stylesheet_directory_uri(); ?>/template/api-handler.php';

            const statusLoaderContainer = document.getElementById('statusLoaderContainer');
            const statusLoaderTitle = document.getElementById('statusLoaderTitle');
            const statusLoaderSubtitle = document.getElementById('statusLoaderSubtitle');

            function showStatusLoader(title = 'Fetching Service Request Status...', subtitle = 'Please wait.') {
                if (statusLoaderTitle) statusLoaderTitle.textContent = title;
                if (statusLoaderSubtitle) statusLoaderSubtitle.textContent = subtitle;
                if (statusLoaderContainer) statusLoaderContainer.style.display = 'block';
                if (statusResultsSection) statusResultsSection.style.display = 'none';
            }

            function hideStatusLoader() {
                if (statusLoaderContainer) statusLoaderContainer.style.display = 'none';
            }

            // Check existing session on load
            async function checkExistingSession() {
                try {
                    showStatusLoader('Checking Session...', 'Verifying existing authentication...');
                    const response = await fetch(apiHandlerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'checkSession'
                        })
                    });
                    const data = await response.json();
                    if (data.success && data.verified && data.mobile) {
                        verifiedMobileNumber = data.mobile;
                        srMobileNumber.value = verifiedMobileNumber;
                        srMobileNumber.readOnly = true;
                        srSendOtpBtn.style.display = 'none';
                        srOtpGroupCol.style.display = 'none';
                        await fetchAndDisplayStatus(verifiedMobileNumber);
                    } else {
                        hideStatusLoader();
                    }
                } catch (e) {
                    console.error('Error checking existing session:', e);
                    hideStatusLoader();
                }
            }

            checkExistingSession();

            let timerInterval = null;

            // Send OTP handler
            srSendOtpBtn.addEventListener('click', async () => {
                srMobileError.style.display = 'none';
                srOtpError.style.display = 'none';
                const mobile = srMobileNumber.value.trim();

                if (!mobile || !/^[0-9]{10}$/.test(mobile)) {
                    srMobileError.textContent = 'Please enter a valid 10-digit mobile number.';
                    srMobileError.style.display = 'block';
                    return;
                }

                srSendOtpBtn.disabled = true;
                srSendOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm spinner-border-sm-custom me-2" role="status" aria-hidden="true"></span> Sending...';

                let isSuccess = false;

                try {
                    const response = await fetch(apiHandlerUrl, {
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
                        srOtpGroupCol.style.display = 'block';
                        srOtpInput.focus();
                        srMobileError.style.display = 'none';
                        if (data.otp) {
                            console.log('OTP generated (test mode):', data.otp);
                        }
                    } else {
                        srMobileError.textContent = data.message || 'Failed to send OTP. Please try again.';
                        srMobileError.style.display = 'block';
                    }
                } catch (err) {
                    console.error('Error sending OTP:', err);
                    srMobileError.textContent = 'Network or server error while sending OTP.';
                    srMobileError.style.display = 'block';
                } finally {
                    if (isSuccess) {
                        if (timerInterval) clearInterval(timerInterval);
                        srSendOtpBtn.disabled = true;
                        srMobileNumber.disabled = true;
                        let timeLeft = 300; // 5 minutes
                        const formatTime = (seconds) => {
                            const mins = Math.floor(seconds / 60);
                            const secs = seconds % 60;
                            return `${mins}:${secs.toString().padStart(2, '0')}`;
                        };
                        srSendOtpBtn.textContent = `Resend OTP (${formatTime(timeLeft)})`;

                        timerInterval = setInterval(() => {
                            timeLeft--;
                            srSendOtpBtn.textContent = `Resend OTP (${formatTime(timeLeft)})`;

                            if (timeLeft <= 0) {
                                clearInterval(timerInterval);
                                srSendOtpBtn.disabled = false;
                                srMobileNumber.disabled = false;
                                srSendOtpBtn.innerHTML = '<i class="bi bi-chat-dots me-1"></i> Resend OTP';
                            }
                        }, 1000);
                    } else {
                        srSendOtpBtn.disabled = false;
                        srSendOtpBtn.innerHTML = '<i class="bi bi-chat-dots me-1"></i> Send OTP';
                    }
                }
            });

            // Verify OTP handler
            srVerifyOtpBtn.addEventListener('click', async () => {
                srMobileError.style.display = 'none';
                srOtpError.style.display = 'none';
                const mobile = srMobileNumber.value.trim();
                const otp = srOtpInput.value.trim();

                if (!mobile || !/^[0-9]{10}$/.test(mobile)) {
                    srMobileError.textContent = 'Please enter a valid 10-digit mobile number.';
                    srMobileError.style.display = 'block';
                    return;
                }

                if (!otp || otp.length < 4) {
                    srOtpError.textContent = 'Please enter the 4-digit OTP sent to your WhatsApp.';
                    srOtpError.style.display = 'block';
                    return;
                }

                srVerifyOtpBtn.disabled = true;
                srVerifyOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm spinner-border-sm-custom me-2" role="status" aria-hidden="true"></span> Verifying...';

                try {
                    const response = await fetch(apiHandlerUrl, {
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
                        verifiedMobileNumber = mobile;
                        srMobileNumber.readOnly = true;
                        srSendOtpBtn.style.display = 'none';
                        srOtpGroupCol.style.display = 'none';
                        await fetchAndDisplayStatus(verifiedMobileNumber);
                    } else {
                        srOtpError.textContent = data.message || 'Invalid OTP. Please check and try again.';
                        srOtpError.style.display = 'block';
                    }
                } catch (err) {
                    console.error('Error verifying OTP:', err);
                    srOtpError.textContent = 'Network or server error while verifying OTP.';
                    srOtpError.style.display = 'block';
                } finally {
                    srVerifyOtpBtn.disabled = false;
                    srVerifyOtpBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Verify OTP';
                }
            });

            srStatusForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (srOtpGroupCol.style.display !== 'none') {
                    srVerifyOtpBtn.click();
                } else {
                    srSendOtpBtn.click();
                }
            });

            async function fetchAndDisplayStatus(mobile) {
                showStatusLoader('Fetching Service Request Status...', 'Please wait.');
                try {
                    const response = await fetch(apiHandlerUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'getServiceRequestStatus',
                            mobileNumber: mobile
                        })
                    });

                    const data = await response.json();

                    if (data.success && Array.isArray(data.serviceRequests)) {
                        rawServiceRequests = data.serviceRequests;
                        currentPage = 1;
                        displayServiceRequests(data, mobile);
                    } else {
                        const errorMsg = data.message || 'Failed to retrieve service requests for this mobile number.';
                        srMobileError.textContent = errorMsg;
                        srMobileError.style.display = 'block';
                        statusResultsSection.style.display = 'none';
                    }
                } catch (err) {
                    console.error('Error fetching Service Request Status:', err);
                    srMobileError.textContent = 'Network or server error while checking status. Please try again.';
                    srMobileError.style.display = 'block';
                    statusResultsSection.style.display = 'none';
                } finally {
                    hideStatusLoader();
                }
            }

            function displayServiceRequests(data, mobile) {
                const list = data.serviceRequests || [];

                // Derive Statistics
                const total = list.length;
                const openRequests = list.filter(item => (item.status || '').toLowerCase() === 'open');
                const closedRequests = list.filter(item => (item.status || '').toLowerCase().includes('closed'));
                const cancelledRequests = list.filter(item => (item.status || '').toLowerCase().includes('cancelled'));

                statTotalCount.textContent = data.totalServiceRequests || total;
                statOpenCount.textContent = openRequests.length;
                statClosedCount.textContent = closedRequests.length;
                statCancelledCount.textContent = cancelledRequests.length;

                tabCountAll.textContent = total;
                tabCountOpen.textContent = openRequests.length;
                tabCountClosed.textContent = closedRequests.length;
                tabCountCancelled.textContent = cancelledRequests.length;

                if (list.length > 0 && list[0].accountName) {
                    accountNameHeader.textContent = list[0].accountName;
                    accountPhoneSubtitle.textContent = `Registered Mobile: +91 ${mobile} • ${total} Ticket(s) Found`;
                } else {
                    accountNameHeader.textContent = 'Service Request History';
                    accountPhoneSubtitle.textContent = `Registered Mobile: +91 ${mobile}`;
                }

                statusResultsSection.style.display = 'block';
                renderTableRows();
            }

            function renderTableRows() {
                let filtered = rawServiceRequests;

                // Apply Status Tab Filter
                if (currentFilter === 'OPEN') {
                    filtered = filtered.filter(item => (item.status || '').toLowerCase() === 'open');
                } else if (currentFilter === 'CLOSED') {
                    filtered = filtered.filter(item => (item.status || '').toLowerCase().includes('closed'));
                } else if (currentFilter === 'CANCELLED') {
                    filtered = filtered.filter(item => (item.status || '').toLowerCase().includes('cancelled'));
                }

                // Apply Table Search Filter
                if (currentSearchQuery.trim() !== '') {
                    const q = currentSearchQuery.toLowerCase().trim();
                    filtered = filtered.filter(item => {
                        return (item.ticketNumber || '').toLowerCase().includes(q) ||
                            (item.caseNumber || '').toLowerCase().includes(q) ||
                            (item.assetName || '').toLowerCase().includes(q) ||
                            (item.modelNumber || '').toLowerCase().includes(q) ||
                            (item.serialNumber || '').toLowerCase().includes(q) ||
                            (item.purpose || '').toLowerCase().includes(q);
                    });
                }

                const totalFiltered = filtered.length;
                serviceRequestsTableBody.innerHTML = '';

                if (totalFiltered === 0) {
                    srEmptyState.style.display = 'block';
                    paginationBar.style.display = 'none';
                    return;
                }

                srEmptyState.style.display = 'none';

                // Calculate Pagination
                const totalPages = Math.ceil(totalFiltered / recordsPerPage);
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIndex = (currentPage - 1) * recordsPerPage;
                const endIndex = Math.min(startIndex + recordsPerPage, totalFiltered);
                const paginatedItems = filtered.slice(startIndex, endIndex);

                paginatedItems.forEach(sr => {
                    const tr = document.createElement('tr');

                    // Status Badge class
                    const st = (sr.status || '').toLowerCase();
                    let badgeClass = 'default';
                    let badgeIcon = 'bi-info-circle-fill';

                    if (st === 'open') {
                        badgeClass = 'open';
                        badgeIcon = 'bi-clock-history';
                    } else if (st.includes('closed')) {
                        badgeClass = 'closed';
                        badgeIcon = 'bi-check-circle-fill';
                    } else if (st.includes('cancelled')) {
                        badgeClass = 'cancelled';
                        badgeIcon = 'bi-x-circle-fill';
                    }

                    const assetName = sr.assetName || (sr.modelNumber ? `${sr.modelNumber} - ${sr.serialNumber || ''}` : 'N/A');
                    const serialNum = sr.serialNumber ? `SN: ${sr.serialNumber}` : '';
                    const addressText = sr.address && sr.address.trim() !== '' ? sr.address : 'N/A';
                    const openDate = sr.dateTimeOpen ? sr.dateTimeOpen : 'N/A';
                    const closeDate = sr.dateTimeClosed ? `<br><small class="text-muted">Closed: ${sr.dateTimeClosed}</small>` : '';
                    const remark = sr.serviceRequestClosedRemark || sr.customerComplaint || '—';

                    tr.innerHTML = `
                    <td> 
                        <span class="ticket-number">${sr.ticketNumber || 'N/A'}</span>
                        <span class="sub-text">Case #${sr.caseNumber || 'N/A'}</span>
                    </td>
                    <td>
                        <span class="status-badge ${badgeClass}">
                            <i class="bi ${badgeIcon}"></i> ${sr.status || 'Unknown'}
                        </span>
                    </td>
                    <td>
                        <span class="asset-title">${assetName}</span>
                        ${serialNum ? `<span class="sub-text">${serialNum}</span>` : ''}
                    </td>
                    <td>
                        <span class="small text-secondary" style="max-width: 200px; display: inline-block;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${addressText}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">${sr.purpose || 'Service'}</span>
                    </td>
                    <td>
                        <span class="small">${openDate}</span>
                        ${closeDate}
                    </td>
                    <td>
                        <span class="small text-secondary">${remark}</span>
                    </td>
                `;
                    serviceRequestsTableBody.appendChild(tr);
                });

                // Render Pagination UI
                renderPaginationControls(startIndex + 1, endIndex, totalFiltered, totalPages);
            }

            function renderPaginationControls(start, end, total, totalPages) {
                if (total <= recordsPerPage) {
                    paginationBar.style.display = 'none';
                    return;
                }

                paginationBar.style.display = 'flex';
                paginationInfo.textContent = `Showing ${start} to ${end} of ${total} entries`;

                paginationControls.innerHTML = '';

                // Previous Button
                const prevBtn = document.createElement('button');
                prevBtn.className = 'page-btn';
                prevBtn.disabled = currentPage === 1;
                prevBtn.innerHTML = '<i class="bi bi-chevron-left"></i>';
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        renderTableRows();
                    }
                });
                paginationControls.appendChild(prevBtn);

                // Page Number Buttons
                for (let i = 1; i <= totalPages; i++) {
                    // Show first page, last page, and pages around current page
                    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                        const pageBtn = document.createElement('button');
                        pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
                        pageBtn.textContent = i;
                        pageBtn.addEventListener('click', () => {
                            currentPage = i;
                            renderTableRows();
                        });
                        paginationControls.appendChild(pageBtn);
                    } else if (
                        (i === 2 && currentPage > 3) ||
                        (i === totalPages - 1 && currentPage < totalPages - 2)
                    ) {
                        const dots = document.createElement('span');
                        dots.className = 'px-1 text-muted';
                        dots.textContent = '...';
                        paginationControls.appendChild(dots);
                    }
                }

                // Next Button
                const nextBtn = document.createElement('button');
                nextBtn.className = 'page-btn';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.innerHTML = '<i class="bi bi-chevron-right"></i>';
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderTableRows();
                    }
                });
                paginationControls.appendChild(nextBtn);
            }

            // Tab Filter Click Handlers
            filterTabs.addEventListener('click', (e) => {
                const btn = e.target.closest('.filter-tab');
                if (!btn) return;
                filterTabs.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                btn.classList.add('active');
                currentFilter = btn.getAttribute('data-filter');
                if (statusDropdownFilter) {
                    statusDropdownFilter.value = currentFilter;
                }
                currentPage = 1;
                renderTableRows();
            });

            // Status Dropdown Filter Handler
            const statusDropdownFilter = document.getElementById('statusDropdownFilter');
            if (statusDropdownFilter) {
                statusDropdownFilter.addEventListener('change', (e) => {
                    currentFilter = e.target.value;
                    filterTabs.querySelectorAll('.filter-tab').forEach(t => {
                        if (t.getAttribute('data-filter') === currentFilter) {
                            t.classList.add('active');
                        } else {
                            t.classList.remove('active');
                        }
                    });
                    currentPage = 1;
                    renderTableRows();
                });
            }

            // Search Input Filter
            tableSearchInput.addEventListener('input', (e) => {
                currentSearchQuery = e.target.value;
                currentPage = 1;
                renderTableRows();
            });
        });
    </script>
</body>

<?php get_footer(); ?>