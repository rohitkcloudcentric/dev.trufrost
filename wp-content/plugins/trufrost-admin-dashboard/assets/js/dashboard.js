/**
 * Trufrost CRM Admin Dashboard Script
 * Implements AJAX pagination, filtering, sorting, modals, copy tokens, and CSV exports
 */

jQuery(document).ready(function($) {
    // Check if configuration exists
    if (typeof trufrostDashboard === 'undefined') {
        return;
    }

    const ajaxUrl = trufrostDashboard.ajaxurl;
    const nonce = trufrostDashboard.nonce;

    // State Variables for Service Requests
    let srState = {
        page: 1,
        limit: 10,
        orderby: 'id',
        order: 'desc',
        search_global: ''
    };

    // State Variables for OTP Verifications
    let otpState = {
        page: 1,
        limit: 10,
        orderby: 'id',
        order: 'desc',
        search_global: ''
    };

    // Initialize Dashboard
    initDashboard();

    function initDashboard() {
        loadStats();
        loadServiceRequests();
        
        // Tab Activation Actions
        $('#dashboardTabs button').on('shown.bs.tab', function(e) {
            const tabId = $(e.target).attr('id');
            if (tabId === 'requests-tab') {
                loadServiceRequests();
            } else if (tabId === 'otp-tab') {
                loadOtpVerifications();
            }
        });

        // Set up filters
        setupFilters();

        // Set up sorting
        setupSorting();

        // Set up pagination size controls
        setupPaginationSize();

        // Set up exports
        setupExports();
    }

    /**
     * Show Toast Notifications
     */
    function showToast(message, type = 'success') {
        const toastEl = $('#liveToast');
        const iconEl = $('#toastIcon');
        const messageEl = $('#toastMessage');

        // Reset classes
        toastEl.removeClass('bg-success bg-danger bg-warning bg-info');
        iconEl.removeClass('bi-check-circle-fill bi-exclamation-triangle-fill bi-info-circle-fill');

        if (type === 'success') {
            toastEl.addClass('bg-success');
            iconEl.addClass('bi-check-circle-fill');
        } else if (type === 'error') {
            toastEl.addClass('bg-danger');
            iconEl.addClass('bi-exclamation-triangle-fill');
        } else {
            toastEl.addClass('bg-info');
            iconEl.addClass('bi-info-circle-fill');
        }

        messageEl.text(message);

        const toast = new bootstrap.Toast(toastEl[0]);
        toast.show();
    }

    /**
     * Load Overview Analytics Counts
     */
    function loadStats() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'trufrost_get_overview_stats',
                security: nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Animate text elements with counter
                    animateValue("stat-total-sr", data.total_service_requests);
                    animateValue("stat-today-sr", data.today_service_requests);
                    animateValue("stat-total-otp", data.total_otp_verifications);
                    animateValue("stat-today-otp", data.today_otp_verifications);
                    animateValue("stat-verified-otp", data.verified_otp_count);
                    animateValue("stat-failed-otp", data.failed_unused_otp_count);
                } else {
                    showToast('Failed to load dashboard statistics.', 'error');
                }
            },
            error: function() {
                showToast('Failed to connect to the server for stats.', 'error');
            }
        });
    }

    /**
     * Counter Animation Helper
     */
    function animateValue(id, endValue) {
        const obj = document.getElementById(id);
        if (!obj) return;
        
        let startValue = 0;
        let duration = 600; // ms
        let startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            obj.innerHTML = Math.floor(progress * (endValue - startValue) + startValue);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        }
        window.requestAnimationFrame(step);
    }

    /**
     * Fetch and load Service Requests table
     */
    function loadServiceRequests() {
        $('#srTableOverlay').removeClass('d-none');
        
        const filters = $('#srFiltersForm').serializeArray();
        let data = {
            action: 'trufrost_get_service_requests',
            security: nonce,
            page: srState.page,
            limit: srState.limit,
            orderby: srState.orderby,
            order: srState.order,
            search_global: srState.search_global
        };

        // Append form filters
        $.each(filters, function(i, field) {
            data[field.name] = field.value;
        });

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#srTableOverlay').addClass('d-none');
                if (response.success) {
                    renderSRTable(response.data.items);
                    renderPagination('srPagination', response.data.page, response.data.pages, function(newPage) {
                        srState.page = newPage;
                        loadServiceRequests();
                    });
                    $('#srRecordsCount').text(response.data.total_items + ' Records');
                } else {
                    showToast('Failed to load service requests.', 'error');
                }
            },
            error: function() {
                $('#srTableOverlay').addClass('d-none');
                showToast('Error connecting to retrieve Service Requests.', 'error');
            }
        });
    }

    /**
     * Render Service Requests Table Rows
     */
    function renderSRTable(items) {
        const body = $('#srTableBody');
        body.empty();

        if (!items || items.length === 0) {
            body.append('<tr><td colspan="8" class="text-center py-4 text-muted fs-6"><i class="bi bi-inbox me-2"></i>No service requests found matching the search criteria.</td></tr>');
            return;
        }

        $.each(items, function(index, item) {
            // Check status for badges
            let statusBadge = '';
            if (item.salesforce_status === 'success') {
                statusBadge = '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i>Success</span>';
            } else if (item.salesforce_status === 'failed') {
                statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle-fill me-1"></i>Failed</span>';
            } else {
                statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-hourglass-split me-1"></i>Pending</span>';
            }

            // Shorten JSON data or set view button
            const viewResponseBtn = item.salesforce_response ? 
                `<button type="button" class="btn btn-sm btn-outline-primary btn-sm-action view-response-json" data-id="${item.id}"><i class="bi bi-eye"></i> View Response</button>` : 
                '<span class="text-muted fs-7">No Response</span>';

            const viewFormDataBtn = item.form_data ? 
                `<button type="button" class="btn btn-sm btn-outline-secondary btn-sm-action view-form-json" data-id="${item.id}"><i class="bi bi-filetype-json"></i> View Form Data</button>` : 
                '<span class="text-muted fs-7">No Data</span>';

            // Cache data on element to retrieve on popup
            const tr = $('<tr></tr>');
            tr.append(`<td><strong>#${item.id}</strong></td>`);
            tr.append(`<td><a href="tel:${item.mobile_number}" class="text-decoration-none font-monospace">${item.mobile_number}</a></td>`);
            tr.append(`<td>${escapeHtml(item.customer_name)}</td>`);
            tr.append(`<td><a href="mailto:${item.email}" class="text-decoration-none">${escapeHtml(item.email)}</a></td>`);
            tr.append(`<td>${statusBadge}</td>`);
            tr.append(`<td>${viewResponseBtn}</td>`);
            tr.append(`<td>${viewFormDataBtn}</td>`);
            tr.append(`<td class="text-nowrap fs-7 text-muted">${item.created_at}</td>`);

            // Store JSON data inside DOM elements securely
            tr.find('.view-response-json').data('json', item.salesforce_response);
            tr.find('.view-form-json').data('json', item.form_data);

            body.append(tr);
        });

        // Trigger Event Handlers for modals
        setupModalsSR();
    }

    /**
     * Fetch and load OTP Verifications table
     */
    function loadOtpVerifications() {
        $('#otpTableOverlay').removeClass('d-none');
        
        const filters = $('#otpFiltersForm').serializeArray();
        let data = {
            action: 'trufrost_get_otp_verifications',
            security: nonce,
            page: otpState.page,
            limit: otpState.limit,
            orderby: otpState.orderby,
            order: otpState.order,
            search_global: otpState.search_global
        };

        // Append filters
        $.each(filters, function(i, field) {
            data[field.name] = field.value;
        });

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: data,
            success: function(response) {
                $('#otpTableOverlay').addClass('d-none');
                if (response.success) {
                    renderOTPTable(response.data.items);
                    renderPagination('otpPagination', response.data.page, response.data.pages, function(newPage) {
                        otpState.page = newPage;
                        loadOtpVerifications();
                    });
                    $('#otpRecordsCount').text(response.data.total_items + ' Records');
                } else {
                    showToast('Failed to load OTP verifications.', 'error');
                }
            },
            error: function() {
                $('#otpTableOverlay').addClass('d-none');
                showToast('Error connecting to retrieve OTP records.', 'error');
            }
        });
    }

    /**
     * Render OTP Table rows
     */
    function renderOTPTable(items) {
        const body = $('#otpTableBody');
        body.empty();

        if (!items || items.length === 0) {
            body.append('<tr><td colspan="10" class="text-center py-4 text-muted fs-6"><i class="bi bi-inbox me-2"></i>No OTP verification records found.</td></tr>');
            return;
        }

        const now = new Date();

        $.each(items, function(index, item) {
            // Status calculations
            let isVerifiedBadge = item.is_verified == '1' ? 
                '<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-shield-check me-1"></i>Verified</span>' : 
                '<span class="badge bg-secondary-subtle text-secondary border border-secondary">Unverified</span>';

            let isUsedBadge = item.is_used == '1' ? 
                '<span class="badge bg-info-subtle text-info border border-info"><i class="bi bi-key-fill me-1"></i>Used</span>' : 
                '<span class="badge bg-light-subtle text-dark border border-secondary-subtle">Unused</span>';

            // Expired Check
            const expires = new Date(item.expires_at.replace(/-/g, "/"));
            let isExpiredBadge = '';
            if (item.is_verified != '1' && item.is_used != '1' && expires < now) {
                isExpiredBadge = '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-calendar-x me-1"></i>Expired</span>';
            }

            // Tokens Masking & View Action
            const accessTokenMasked = item.access_token ? maskToken(item.access_token) : 'N/A';
            const sfTokenMasked = item.salesforce_token ? maskToken(item.salesforce_token) : 'N/A';

            const viewAccessBtn = item.access_token ? 
                `<button type="button" class="btn btn-sm btn-link p-0 ms-1 view-full-token" data-token-type="Access Token" data-token="${item.access_token}" title="View Access Token"><i class="bi bi-eye text-primary"></i></button>` : '';
            
            const viewSfBtn = item.salesforce_token ? 
                `<button type="button" class="btn btn-sm btn-link p-0 ms-1 view-full-token" data-token-type="Salesforce Token" data-token="${item.salesforce_token}" title="View Salesforce Token"><i class="bi bi-eye text-primary"></i></button>` : '';

            const tr = $('<tr></tr>');
            tr.append(`<td><strong>#${item.id}</strong></td>`);
            tr.append(`<td><span class="badge bg-dark font-monospace px-2 py-1">${item.otp_code}</span></td>`);
            tr.append(`<td><a href="tel:${item.mobile_number}" class="text-decoration-none font-monospace">${item.mobile_number}</a></td>`);
            tr.append(`<td><div class="token-mask-wrapper"><span class="token-mask-text">${accessTokenMasked}</span>${viewAccessBtn}</div></td>`);
            tr.append(`<td><div class="token-mask-wrapper"><span class="token-mask-text">${sfTokenMasked}</span>${viewSfBtn}</div></td>`);
            tr.append(`<td>${isVerifiedBadge}</td>`);
            tr.append(`<td>${isUsedBadge} ${isExpiredBadge}</td>`);
            tr.append(`<td><span class="badge bg-light text-dark border border-secondary-subtle px-2">${item.attempts} / 5</span></td>`);
            tr.append(`<td class="text-nowrap fs-7 text-muted">${item.created_at}</td>`);
            tr.append(`<td class="text-nowrap fs-7 text-muted">${item.expires_at}</td>`);

            body.append(tr);
        });

        // Trigger Event Handlers for tokens
        setupModalsOTP();
    }

    /**
     * Mask Token helper: displays first 6 and last 6, filled with dots
     */
    function maskToken(token) {
        if (!token) return '';
        if (token.length <= 16) return token;
        return token.substring(0, 6) + '...' + token.substring(token.length - 6);
    }

    /**
     * Modals trigger configuration for Service Requests JSON
     */
    function setupModalsSR() {
        $('.view-response-json, .view-form-json').off('click').on('click', function() {
            const rawJson = $(this).data('json');
            const title = $(this).hasClass('view-response-json') ? 'Salesforce Integration Response JSON' : 'Service Form Data JSON';
            
            $('#detailsModalLabel').html(`<i class="bi bi-filetype-json fs-4 text-primary"></i> ${title}`);
            
            // Hide Token UI, Show JSON UI
            $('.token-container').addClass('d-none');
            $('.json-container').removeClass('d-none');

            try {
                let parsed = rawJson;
                if (typeof rawJson === 'string') {
                    parsed = JSON.parse(rawJson);
                }
                const formatted = JSON.stringify(parsed, null, 4);
                $('#modalJsonValue').text(formatted);
            } catch(e) {
                // Handle non-JSON response string
                $('#modalJsonValue').text(rawJson || 'Empty or Invalid Data.');
            }

            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        });
    }

    /**
     * Modals configuration for OTP tokens
     */
    function setupModalsOTP() {
        $('.view-full-token').off('click').on('click', function() {
            const tokenType = $(this).data('token-type');
            const token = $(this).data('token');

            $('#detailsModalLabel').html(`<i class="bi bi-shield-lock-fill fs-4 text-success"></i> View ${tokenType}`);

            // Hide JSON UI, Show Token UI
            $('.json-container').addClass('d-none');
            $('.token-container').removeClass('d-none');

            $('#tokenTypeLabel').text(`${tokenType} Value:`);
            $('#modalTokenValue').val(token);

            // Configure Copy button
            $('#modalCopyTokenBtn').off('click').on('click', function() {
                navigator.clipboard.writeText(token).then(function() {
                    showToast('Token copied to clipboard successfully!', 'success');
                }, function(err) {
                    showToast('Failed to copy token.', 'error');
                });
            });

            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        });
    }

    /**
     * Render pagination links dynamically
     */
    function renderPagination(elementId, currentPage, totalPages, callback) {
        const pag = $('#' + elementId);
        pag.empty();

        if (totalPages <= 1) return;

        // Previous button
        const prevClass = currentPage === 1 ? 'disabled' : '';
        const prevItem = $(`<li class="page-item ${prevClass}"><a class="page-link" href="#" data-page="${currentPage - 1}"><i class="bi bi-chevron-left"></i></a></li>`);
        pag.append(prevItem);

        // Calculate visible pages
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            pag.append(`<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`);
            if (startPage > 2) {
                pag.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = currentPage === i ? 'active' : '';
            const pageItem = $(`<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
            pag.append(pageItem);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pag.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
            }
            pag.append(`<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
        }

        // Next button
        const nextClass = currentPage === totalPages ? 'disabled' : '';
        const nextItem = $(`<li class="page-item ${nextClass}"><a class="page-link" href="#" data-page="${currentPage + 1}"><i class="bi bi-chevron-right"></i></a></li>`);
        pag.append(nextItem);

        // Click actions
        pag.find('.page-link').off('click').on('click', function(e) {
            e.preventDefault();
            const selectedPage = parseInt($(this).data('page'));
            if (!isNaN(selectedPage) && selectedPage !== currentPage && selectedPage > 0 && selectedPage <= totalPages) {
                callback(selectedPage);
            }
        });
    }

    /**
     * Event listeners for Filters
     */
    function setupFilters() {
        // Service Requests filters
        $('#srFiltersForm').on('submit', function(e) {
            e.preventDefault();
            srState.page = 1;
            loadServiceRequests();
        });

        $('#srClearBtn').on('click', function() {
            $('#srFiltersForm')[0].reset();
            srState.page = 1;
            loadServiceRequests();
        });

        // Toggle filter cards icon states
        $('#srFiltersCollapse').on('show.bs.collapse hide.bs.collapse', function(e) {
            $('#srFiltersCollapseIcon').toggleClass('collapse-icon-rotate');
        });

        // Debounce Global Search for Service Requests
        let srSearchTimer;
        $('#srGlobalSearch').on('keyup input', function() {
            clearTimeout(srSearchTimer);
            srSearchTimer = setTimeout(function() {
                srState.search_global = $('#srGlobalSearch').val();
                srState.page = 1;
                loadServiceRequests();
            }, 400);
        });

        // OTP filters
        $('#otpFiltersForm').on('submit', function(e) {
            e.preventDefault();
            otpState.page = 1;
            loadOtpVerifications();
        });

        $('#otpClearBtn').on('click', function() {
            $('#otpFiltersForm')[0].reset();
            otpState.page = 1;
            loadOtpVerifications();
        });

        $('#otpFiltersCollapse').on('show.bs.collapse hide.bs.collapse', function(e) {
            $('#otpFiltersCollapseIcon').toggleClass('collapse-icon-rotate');
        });

        // Debounce Global Search for OTP
        let otpSearchTimer;
        $('#otpGlobalSearch').on('keyup input', function() {
            clearTimeout(otpSearchTimer);
            otpSearchTimer = setTimeout(function() {
                otpState.search_global = $('#otpGlobalSearch').val();
                otpState.page = 1;
                loadOtpVerifications();
            }, 400);
        });
    }

    /**
     * Sorting columns setup
     */
    function setupSorting() {
        // SR Sorting
        $('#srTable th.sortable').on('click', function() {
            const column = $(this).data('column');
            
            if (srState.orderby === column) {
                srState.order = srState.order === 'asc' ? 'desc' : 'asc';
            } else {
                srState.orderby = column;
                srState.order = 'asc';
            }

            // Sync visual arrow icons
            $('#srTable th.sortable').find('.sort-icon').removeClass('bi-arrow-up bi-arrow-down').addClass('bi-arrow-down-up');
            const icon = $(this).find('.sort-icon');
            icon.removeClass('bi-arrow-down-up');
            if (srState.order === 'asc') {
                icon.addClass('bi-arrow-up');
            } else {
                icon.addClass('bi-arrow-down');
            }

            loadServiceRequests();
        });

        // OTP Sorting
        $('#otpTable th.sortable').on('click', function() {
            const column = $(this).data('column');

            if (otpState.orderby === column) {
                otpState.order = otpState.order === 'asc' ? 'desc' : 'asc';
            } else {
                otpState.orderby = column;
                otpState.order = 'asc';
            }

            // Sync arrow icons
            $('#otpTable th.sortable').find('.sort-icon').removeClass('bi-arrow-up bi-arrow-down').addClass('bi-arrow-down-up');
            const icon = $(this).find('.sort-icon');
            icon.removeClass('bi-arrow-down-up');
            if (otpState.order === 'asc') {
                icon.addClass('bi-arrow-up');
            } else {
                icon.addClass('bi-arrow-down');
            }

            loadOtpVerifications();
        });
    }

    /**
     * Table rows limit selector
     */
    function setupPaginationSize() {
        $('#srPageLimit').on('change', function() {
            srState.limit = parseInt($(this).val());
            srState.page = 1;
            loadServiceRequests();
        });

        $('#otpPageLimit').on('change', function() {
            otpState.limit = parseInt($(this).val());
            otpState.page = 1;
            loadOtpVerifications();
        });
    }

    /**
     * Handle CSV Exports by redirecting with appropriate parameters
     */
    function setupExports() {
        $('#srExportBtn').on('click', function() {
            showToast('Preparing Service Requests CSV export. Downloading...', 'info');

            // Gather active filter parameters
            const filters = $('#srFiltersForm').serialize();
            const exportUrl = ajaxUrl + '?trufrost_export=service-requests&security=' + nonce + '&search_global=' + encodeURIComponent(srState.search_global) + '&' + filters;

            // Trigger file download
            window.location.href = exportUrl;
        });

        $('#otpExportBtn').on('click', function() {
            showToast('Preparing OTP Verifications CSV export. Downloading...', 'info');

            // Gather active filters
            const filters = $('#otpFiltersForm').serialize();
            const exportUrl = ajaxUrl + '?trufrost_export=otp-verifications&security=' + nonce + '&search_global=' + encodeURIComponent(otpState.search_global) + '&' + filters;

            window.location.href = exportUrl;
        });
    }

    /**
     * Escape HTML helper to prevent XSS
     */
    function escapeHtml(string) {
        if (!string) return '';
        return String(string).replace(/[&<>"']/g, function(s) {
            const entityMap = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };
            return entityMap[s];
        });
    }
});
