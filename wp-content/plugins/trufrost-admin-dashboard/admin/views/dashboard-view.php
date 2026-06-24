<?php
/**
 * Admin Dashboard View Template
 * Scoped inside .trufrost-admin-wrap to protect other WordPress admin pages
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="trufrost-admin-wrap container-fluid my-4">
    <!-- Header -->
    <header class="dashboard-header d-flex flex-wrap align-items-center justify-content-between pb-3 mb-4 border-bottom border-secondary-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="logo-box">
                <i class="bi bi-shield-fill-check fs-2 text-primary"></i>
            </div>
            <div>
                <h1 class="h2 m-0 font-weight-bold">Trufrost CRM Dashboard</h1>
                <p class="text-muted m-0 fs-6">Manage OTP Verifications and Salesforce Service Requests</p>
            </div>
        </div>
        <div class="header-actions mt-3 mt-md-0">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2 fs-6">
                <i class="bi bi-clock-history me-1"></i> Live CRM Sync
            </span>
        </div>
    </header>

    <!-- Analytics Dashboard Cards -->
    <div class="row g-4 mb-4">
        <!-- Card 1: Service Requests Analytics -->
        <div class="col-md-6 col-lg-6">
            <div class="card stat-card border-0 shadow-sm h-100 position-relative overflow-hidden sr-gradient-card">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title m-0 text-white opacity-75 fs-5 font-weight-semibold">Service Requests</h4>
                        <div class="icon-circle bg-white bg-opacity-25 text-white">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="display-6 font-weight-bold" id="stat-total-sr">0</div>
                            <small class="text-white-50 fs-6">Total Requests</small>
                        </div>
                        <div class="col-6 border-start border-white border-opacity-25 ps-4">
                            <div class="display-6 font-weight-bold" id="stat-today-sr">0</div>
                            <small class="text-white-50 fs-6">Today's Requests</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: OTP Verifications Analytics -->
        <div class="col-md-6 col-lg-6">
            <div class="card stat-card border-0 shadow-sm h-100 position-relative overflow-hidden otp-gradient-card">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4 class="card-title m-0 text-white opacity-75 fs-5 font-weight-semibold">OTP Verifications</h4>
                        <div class="icon-circle bg-white bg-opacity-25 text-white">
                            <i class="bi bi-chat-left-dots fs-4"></i>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-3">
                            <div class="display-6 font-weight-bold" id="stat-total-otp">0</div>
                            <small class="text-white-50 fs-7">Total OTPs</small>
                        </div>
                        <div class="col-3 border-start border-white border-opacity-25 ps-3">
                            <div class="display-6 font-weight-bold" id="stat-today-otp">0</div>
                            <small class="text-white-50 fs-7">Today's OTPs</small>
                        </div>
                        <div class="col-3 border-start border-white border-opacity-25 ps-3">
                            <div class="display-6 font-weight-bold text-success-light" id="stat-verified-otp">0</div>
                            <small class="text-white-50 fs-7">Verified</small>
                        </div>
                        <div class="col-3 border-start border-white border-opacity-25 ps-3">
                            <div class="display-6 font-weight-bold text-danger-light" id="stat-failed-otp">0</div>
                            <small class="text-white-50 fs-7">Failed/Unused</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs nav-fill mb-4 custom-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active py-3 fs-5" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests-pane" type="button" role="tab" aria-controls="requests-pane" aria-selected="true">
                <i class="bi bi-tools me-2 text-primary"></i> Service Requests
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-3 fs-5" id="otp-tab" data-bs-toggle="tab" data-bs-target="#otp-pane" type="button" role="tab" aria-controls="otp-pane" aria-selected="false">
                <i class="bi bi-shield-lock me-2 text-success"></i> OTP Verifications
            </button>
        </li>
    </ul>

    <!-- Tabs Content Panes -->
    <div class="tab-content" id="dashboardTabsContent">
        
        <!-- PANE 1: Service Requests -->
        <div class="tab-pane fade show active" id="requests-pane" role="tabpanel" aria-labelledby="requests-tab">
            <!-- Filter Section (Collapsible Card) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="m-0 font-weight-bold text-dark"><i class="bi bi-funnel-fill me-2 text-primary"></i> Filter Records</h5>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#srFiltersCollapse" aria-expanded="true" aria-controls="srFiltersCollapse">
                        <i class="bi bi-chevron-bar-contract" id="srFiltersCollapseIcon"></i> Toggle Filters
                    </button>
                </div>
                <div class="collapse show" id="srFiltersCollapse">
                    <div class="card-body bg-light-subtle border-top border-light-subtle p-4">
                        <form id="srFiltersForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="sr_filter_name" class="form-label font-weight-semibold">Customer Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="sr_filter_name" name="customer_name" placeholder="Search by name...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="sr_filter_mobile" class="form-label font-weight-semibold">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="sr_filter_mobile" name="mobile_number" placeholder="Search by mobile...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="sr_filter_email" class="form-label font-weight-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="text" class="form-control" id="sr_filter_email" name="email" placeholder="Search by email...">
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="sr_filter_status" class="form-label font-weight-semibold">Salesforce Status</label>
                                <select class="form-select" id="sr_filter_status" name="salesforce_status">
                                    <option value="">All Statuses</option>
                                    <option value="success">Success</option>
                                    <option value="failed">Failed</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="sr_filter_date_from" class="form-label font-weight-semibold">From Date</label>
                                <input type="date" class="form-control" id="sr_filter_date_from" name="date_from">
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="sr_filter_date_to" class="form-label font-weight-semibold">To Date</label>
                                <input type="date" class="form-control" id="sr_filter_date_to" name="date_to">
                            </div>
                            <div class="col-md-12 col-lg-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Filter</button>
                                <button type="button" class="btn btn-secondary" id="srClearBtn" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm position-relative">
                <!-- Card Header -->
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white fs-6 py-2 px-3 font-weight-semibold" id="srRecordsCount">0 Records</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Global Search -->
                        <div class="input-group" style="max-width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" id="srGlobalSearch" placeholder="Global Search...">
                        </div>
                        <!-- Bulk CSV Export Button -->
                        <button id="srExportBtn" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Export CSV</button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-responsive" style="min-height: 200px;">
                    <div class="table-loading-overlay justify-content-center align-items-center d-none" id="srTableOverlay">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <table class="table table-hover table-striped align-middle mb-0 trufrost-table" id="srTable">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="sortable" data-column="id">ID <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="mobile_number">Mobile Number <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="customer_name">Customer Name <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="email">Email <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="salesforce_status">Salesforce Status <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col">Salesforce Response</th>
                                <th scope="col">Form Data</th>
                                <th scope="col" class="sortable" data-column="created_at">Created Date <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody id="srTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="card-footer bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fs-7">Showing</span>
                        <select class="form-select form-select-sm w-auto" id="srPageLimit">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted fs-7">records per page</span>
                    </div>
                    <nav aria-label="Service Requests Pagination">
                        <ul class="pagination pagination-sm m-0 border-0" id="srPagination">
                            <!-- Populated dynamically -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- PANE 2: OTP Verifications -->
        <div class="tab-pane fade" id="otp-pane" role="tabpanel" aria-labelledby="otp-tab">
            <!-- Filter Section (Collapsible Card) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="m-0 font-weight-bold text-dark"><i class="bi bi-funnel-fill me-2 text-success"></i> Filter Records</h5>
                    <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#otpFiltersCollapse" aria-expanded="true" aria-controls="otpFiltersCollapse">
                        <i class="bi bi-chevron-bar-contract" id="otpFiltersCollapseIcon"></i> Toggle Filters
                    </button>
                </div>
                <div class="collapse show" id="otpFiltersCollapse">
                    <div class="card-body bg-light-subtle border-top border-light-subtle p-4">
                        <form id="otpFiltersForm" class="row g-3">
                            <div class="col-md-4 col-lg-3">
                                <label for="otp_filter_mobile" class="form-label font-weight-semibold">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone text-success"></i></span>
                                    <input type="text" class="form-control" id="otp_filter_mobile" name="mobile_number" placeholder="Search by mobile...">
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="otp_filter_verified" class="form-label font-weight-semibold">OTP Status</label>
                                <select class="form-select" id="otp_filter_verified" name="is_verified">
                                    <option value="">All Statuses</option>
                                    <option value="1">Verified</option>
                                    <option value="0">Not Verified</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="otp_filter_used" class="form-label font-weight-semibold">Used Status</label>
                                <select class="form-select" id="otp_filter_used" name="is_used">
                                    <option value="">All Statuses</option>
                                    <option value="1">Used</option>
                                    <option value="0">Unused</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <label for="otp_filter_attempts" class="form-label font-weight-semibold">Attempts Count</label>
                                <input type="number" class="form-control" id="otp_filter_attempts" name="attempts" placeholder="Exact attempts..." min="0">
                            </div>
                            <div class="col-md-4 col-lg-4">
                                <label for="otp_filter_date_from" class="form-label font-weight-semibold">From Date</label>
                                <input type="date" class="form-control" id="otp_filter_date_from" name="date_from">
                            </div>
                            <div class="col-md-4 col-lg-4">
                                <label for="otp_filter_date_to" class="form-label font-weight-semibold">To Date</label>
                                <input type="date" class="form-control" id="otp_filter_date_to" name="date_to">
                            </div>
                            <div class="col-md-12 col-lg-4 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-success w-100"><i class="bi bi-search me-1"></i> Filter</button>
                                <button type="button" class="btn btn-secondary" id="otpClearBtn" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm position-relative">
                <!-- Card Header -->
                <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white fs-6 py-2 px-3 font-weight-semibold" id="otpRecordsCount">0 Records</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Global Search -->
                        <div class="input-group" style="max-width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0" id="otpGlobalSearch" placeholder="Global Search...">
                        </div>
                        <!-- Bulk CSV Export Button -->
                        <button id="otpExportBtn" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i> Export CSV</button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="table-responsive" style="min-height: 200px;">
                    <div class="table-loading-overlay justify-content-center align-items-center d-none" id="otpTableOverlay">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <table class="table table-hover table-striped align-middle mb-0 trufrost-table" id="otpTable">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col" class="sortable" data-column="id">ID <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="otp_code">OTP Code <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="mobile_number">Mobile Number <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col">Access Token (Masked)</th>
                                <th scope="col">Salesforce Token (Masked)</th>
                                <th scope="col" class="sortable" data-column="is_verified">Verified <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="is_used">Used <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="attempts">Attempts <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="created_at">Created Date <i class="bi bi-arrow-down-up sort-icon"></i></th>
                                <th scope="col" class="sortable" data-column="expires_at">Expiry Date <i class="bi bi-arrow-down-up sort-icon"></i></th>
                            </tr>
                        </thead>
                        <tbody id="otpTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="card-footer bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fs-7">Showing</span>
                        <select class="form-select form-select-sm w-auto" id="otpPageLimit">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted fs-7">records per page</span>
                    </div>
                    <nav aria-label="OTP Verifications Pagination">
                        <ul class="pagination pagination-sm m-0 border-0" id="otpPagination">
                            <!-- Populated dynamically -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Popups for viewing details (Reusable Bootstrap 5 Modal) -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title font-weight-bold d-flex align-items-center gap-2" id="detailsModalLabel">
                    <i class="bi bi-file-earmark-code fs-4"></i> Detail View
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light-subtle">
                <div class="token-container d-none mb-3 border rounded p-3 bg-white shadow-xs">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fs-7 font-weight-semibold" id="tokenTypeLabel">Token Details:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="modalCopyTokenBtn">
                            <i class="bi bi-copy me-1"></i> Copy to Clipboard
                        </button>
                    </div>
                    <textarea class="form-control font-monospace fs-7 text-dark bg-light border-0" id="modalTokenValue" rows="4" readonly></textarea>
                </div>
                <div class="json-container d-none">
                    <pre><code class="language-json font-monospace fs-7" id="modalJsonValue"></code></pre>
                </div>
            </div>
            <div class="modal-footer bg-light border-top border-light-subtle">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="liveToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-5" id="toastIcon"></i>
                <span id="toastMessage">Notification Message</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>


