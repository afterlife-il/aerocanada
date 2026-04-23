/**
 * AeroCanada Industries - ERP v2 Main JavaScript
 * ================================================
 * Core UI interactions, AJAX setup, toast notifications,
 * DataTable factory, modal helpers, form utilities.
 */

'use strict';

const ACI = (function($) {

    // ── Private State ──────────────────────────────────────────
    const _state = {
        sidebarCollapsed: localStorage.getItem('aci_sidebar_collapsed') === '1',
        draftTimers: {},
        searchDebounce: null
    };

    // ── CSRF Token ─────────────────────────────────────────────
    function _getCSRFToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    // ── AJAX Global Setup ──────────────────────────────────────
    function _initAjax() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': _getCSRFToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    window.location.href = '/pages/v2/auth/login';
                    return;
                }
                if (xhr.status === 403) {
                    ACI.toast('Access denied. You do not have permission.', 'error');
                    return;
                }
                if (xhr.status === 422 && xhr.responseJSON) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors || {};
                    Object.keys(errors).forEach(function(field) {
                        const $input = $('[name="' + field + '"]');
                        $input.addClass('is-invalid');
                        $input.closest('.mb-3, .form-group')
                              .find('.invalid-feedback')
                              .text(errors[field])
                              .show();
                    });
                    ACI.toast('Please correct the highlighted errors.', 'warning');
                    return;
                }
                ACI.toast('An unexpected error occurred. Please try again.', 'error');
            }
        });

        // Refresh CSRF on token mismatch
        $(document).ajaxError(function(event, xhr) {
            if (xhr.status === 419) {
                $.get('/pages/v2/api/csrf-refresh', function(data) {
                    $('meta[name="csrf-token"]').attr('content', data.token);
                    $.ajaxSetup({ headers: { 'X-CSRF-Token': data.token } });
                    ACI.toast('Session refreshed. Please try again.', 'info');
                });
            }
        });
    }

    // ── Sidebar ────────────────────────────────────────────────
    function _initSidebar() {
        const $sidebar = $('#aci-sidebar');
        const $body = $('body');
        const $toggle = $('#sidebar-toggle');
        const $overlay = $('#mobile-overlay');
        const isMobile = window.innerWidth < 992;

        // Restore state from localStorage
        if (_state.sidebarCollapsed && !isMobile) {
            $sidebar.addClass('collapsed');
            $body.addClass('sidebar-collapsed');
        }

        $toggle.on('click', function() {
            if (window.innerWidth < 992) {
                // Mobile: slide in/out
                $sidebar.toggleClass('mobile-open');
                $overlay.toggleClass('active');
            } else {
                // Desktop: collapse/expand
                $sidebar.toggleClass('collapsed');
                $body.toggleClass('sidebar-collapsed');
                _state.sidebarCollapsed = $sidebar.hasClass('collapsed');
                localStorage.setItem('aci_sidebar_collapsed', _state.sidebarCollapsed ? '1' : '0');
            }
        });

        $overlay.on('click', function() {
            $sidebar.removeClass('mobile-open');
            $overlay.removeClass('active');
        });

        // Sidebar menu search filter
        $('#sidebar-search').on('input', function() {
            const query = $(this).val().toLowerCase().trim();
            if (!query) {
                $('.aci-nav-link, .aci-nav-section-title').show();
                return;
            }
            $('.aci-nav-link').each(function() {
                const text = $(this).find('.aci-sidebar-text').text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
            // Show section titles if at least one child is visible
            $('.aci-nav-section-title').each(function() {
                const $section = $(this).next('.collapse');
                const hasVisible = $section.find('.aci-nav-link:visible').length > 0;
                $(this).toggle(hasVisible);
            });
        });

        // Handle resize
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 992) {
                    $sidebar.removeClass('mobile-open');
                    $overlay.removeClass('active');
                }
            }, 150);
        });
    }

    // ── Toast Notification System ──────────────────────────────
    const _toastIcons = {
        success: 'fa-solid fa-circle-check',
        error:   'fa-solid fa-circle-xmark',
        warning: 'fa-solid fa-triangle-exclamation',
        info:    'fa-solid fa-circle-info'
    };

    function toast(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;

        const $container = $('#aci-toasts');
        const id = 'toast-' + Date.now();
        const html =
            '<div class="aci-toast toast-' + type + '" id="' + id + '">' +
                '<i class="toast-icon ' + (_toastIcons[type] || _toastIcons.info) + '"></i>' +
                '<div class="toast-body">' + message + '</div>' +
                '<button class="toast-close" aria-label="Close">' +
                    '<i class="fa-solid fa-xmark"></i>' +
                '</button>' +
            '</div>';

        $container.append(html);

        const $toast = $('#' + id);
        $toast.find('.toast-close').on('click', function() {
            _dismissToast($toast);
        });

        setTimeout(function() {
            _dismissToast($toast);
        }, duration);
    }

    function _dismissToast($toast) {
        if ($toast.hasClass('toast-out')) return;
        $toast.addClass('toast-out');
        setTimeout(function() { $toast.remove(); }, 300);
    }

    // ── DataTables Factory ─────────────────────────────────────
    function dataTable(selector, url, columns, options) {
        const defaults = {
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                type: 'POST',
                headers: { 'X-CSRF-Token': _getCSRFToken() },
                error: function(xhr) {
                    ACI.toast('Failed to load table data.', 'error');
                }
            },
            columns: columns,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc']],
            language: {
                search: '',
                searchPlaceholder: 'Filter records...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                emptyTable: 'No records found',
                processing: '<div class="aci-spinner" style="width:28px;height:28px;margin:0 auto;"></div>'
            },
            dom: '<"row align-items-center"<"col-auto"l><"col"f>>' +
                 'rt' +
                 '<"row align-items-center mt-2"<"col-sm-5"i><"col-sm-7"p>>',
            drawCallback: function() {
                // Reinitialize tooltips on redraw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        };

        const config = $.extend(true, {}, defaults, options || {});
        return $(selector).DataTable(config);
    }

    // ── Modal Helper ───────────────────────────────────────────
    function modal(title, content, size) {
        size = size || ''; // '', 'modal-lg', 'modal-xl', 'modal-sm'

        // Remove existing dynamic modal
        $('#aci-dynamic-modal').remove();

        const html =
            '<div class="modal fade aci-modal" id="aci-dynamic-modal" tabindex="-1">' +
                '<div class="modal-dialog ' + size + ' modal-dialog-centered">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title">' + title + '</h5>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
                        '</div>' +
                        '<div class="modal-body">' + content + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $('body').append(html);
        const bsModal = new bootstrap.Modal('#aci-dynamic-modal');
        bsModal.show();

        // Clean up on close
        $('#aci-dynamic-modal').on('hidden.bs.modal', function() {
            $(this).remove();
        });

        return bsModal;
    }

    // ── Confirm Delete Dialog ──────────────────────────────────
    function confirmDelete(message) {
        message = message || 'Are you sure you want to delete this record? This action cannot be undone.';

        return new Promise(function(resolve, reject) {
            const html =
                '<div class="aci-confirm-backdrop" id="aci-confirm">' +
                    '<div class="aci-confirm-dialog">' +
                        '<div class="text-center mb-3">' +
                            '<div style="width:50px;height:50px;border-radius:50%;background:#FEE2E2;display:inline-flex;align-items:center;justify-content:center;">' +
                                '<i class="fa-solid fa-trash" style="color:#DC2626;font-size:1.25rem;"></i>' +
                            '</div>' +
                        '</div>' +
                        '<h6 class="text-center mb-2" style="font-weight:700;">Confirm Deletion</h6>' +
                        '<p class="text-center text-muted" style="font-size:0.85rem;">' + message + '</p>' +
                        '<div class="d-flex gap-2 mt-3">' +
                            '<button class="btn btn-aci-outline flex-fill" id="aci-confirm-cancel">Cancel</button>' +
                            '<button class="btn btn-danger flex-fill" id="aci-confirm-ok">Delete</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('body').append(html);

            $('#aci-confirm-ok').on('click', function() {
                $('#aci-confirm').remove();
                resolve(true);
            });

            $('#aci-confirm-cancel, #aci-confirm').on('click', function(e) {
                if (e.target === this) {
                    $('#aci-confirm').remove();
                    reject(false);
                }
            });
        });
    }

    // ── Form Validation ────────────────────────────────────────
    function initFormValidation(formSelector) {
        const $form = $(formSelector);

        $form.on('submit', function(e) {
            const form = this;
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(form).addClass('was-validated');
        });

        // Clear invalid state on input
        $form.on('input change', '.form-control, .form-select', function() {
            $(this).removeClass('is-invalid');
            $(this).closest('.mb-3, .form-group').find('.invalid-feedback').hide();
        });
    }

    // ── Auto-save Draft to localStorage ────────────────────────
    function initAutoSave(formSelector, key) {
        const $form = $(formSelector);
        if (!$form.length) return;

        const storageKey = 'aci_draft_' + (key || window.location.pathname);

        // Restore draft
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(function(name) {
                    const $field = $form.find('[name="' + name + '"]');
                    if ($field.length && $field.attr('type') !== 'password' && $field.attr('type') !== 'hidden') {
                        if ($field.is(':checkbox')) {
                            $field.prop('checked', data[name]);
                        } else {
                            $field.val(data[name]);
                        }
                    }
                });
                ACI.toast('Draft restored from your last session.', 'info');
            } catch (e) {
                localStorage.removeItem(storageKey);
            }
        }

        // Save draft on change (debounced)
        $form.on('input change', ':input', function() {
            clearTimeout(_state.draftTimers[storageKey]);
            _state.draftTimers[storageKey] = setTimeout(function() {
                const formData = {};
                $form.find(':input').each(function() {
                    const name = $(this).attr('name');
                    if (!name || $(this).attr('type') === 'password' || $(this).attr('type') === 'hidden') return;
                    if ($(this).is(':checkbox')) {
                        formData[name] = $(this).is(':checked');
                    } else {
                        formData[name] = $(this).val();
                    }
                });
                localStorage.setItem(storageKey, JSON.stringify(formData));
            }, 1000);
        });

        // Clear draft on successful submit
        $form.on('submit', function() {
            localStorage.removeItem(storageKey);
        });
    }

    function clearDraft(key) {
        const storageKey = 'aci_draft_' + (key || window.location.pathname);
        localStorage.removeItem(storageKey);
    }

    // ── Global Search with Debounce ────────────────────────────
    function _initGlobalSearch() {
        const $input = $('#global-search');
        if (!$input.length) return;

        $input.on('input', function() {
            const query = $(this).val().trim();
            clearTimeout(_state.searchDebounce);

            if (query.length < 2) return;

            _state.searchDebounce = setTimeout(function() {
                $.get('/pages/v2/api/search', { q: query }, function(data) {
                    // Display results in a dropdown
                    _showSearchResults(data.results || []);
                });
            }, 350);
        });

        // Close results on click outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.aci-navbar-search').length) {
                $('#global-search-results').remove();
            }
        });
    }

    function _showSearchResults(results) {
        $('#global-search-results').remove();

        if (!results.length) return;

        let html = '<div id="global-search-results" style="position:absolute;top:100%;left:0;right:0;background:var(--aci-white);border:var(--aci-border);border-radius:var(--aci-border-radius);box-shadow:var(--aci-shadow-lg);max-height:300px;overflow-y:auto;z-index:1050;">';
        results.forEach(function(item) {
            html += '<a href="' + item.url + '" class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none" style="border-bottom:var(--aci-border-light);color:var(--aci-gray-700);font-size:0.85rem;">';
            html += '<i class="' + (item.icon || 'fa-solid fa-circle') + '" style="width:16px;text-align:center;color:var(--aci-gray-400);"></i>';
            html += '<div><strong>' + item.title + '</strong>';
            if (item.subtitle) html += '<br><small style="color:var(--aci-gray-500);">' + item.subtitle + '</small>';
            html += '</div></a>';
        });
        html += '</div>';

        $('.aci-navbar-search').css('position', 'relative').append(html);
    }

    // ── Keyboard Shortcuts ─────────────────────────────────────
    function _initKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Skip if user is typing in an input
            if ($(e.target).is('input, textarea, select, [contenteditable]')) return;

            // Ctrl+N or Cmd+N: Quick create (context-dependent)
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                const path = window.location.pathname;
                if (path.includes('/parts'))     window.location.href = '/pages/v2/parts/create';
                else if (path.includes('/rfq'))  window.location.href = '/pages/v2/rfq/create';
                else if (path.includes('/companies')) window.location.href = '/pages/v2/companies/create';
                return;
            }

            // Ctrl+S or Cmd+S: Submit nearest form
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const $form = $('form.aci-form:visible').first();
                if ($form.length) {
                    $form.trigger('submit');
                }
                return;
            }

            // Escape: close modals
            if (e.key === 'Escape') {
                $('#aci-confirm').remove();
            }

            // / : focus search
            if (e.key === '/') {
                e.preventDefault();
                $('#global-search').focus();
            }
        });
    }

    // ── Page Loading Indicator ──────────────────────────────────
    function showLoading() {
        $('#aci-loading').addClass('active');
    }

    function hideLoading() {
        $('#aci-loading').removeClass('active');
    }

    // ── Theme Toggle ───────────────────────────────────────────
    function _initThemeToggle() {
        const $btn = $('#theme-toggle');
        const savedTheme = localStorage.getItem('aci_theme') || 'light';

        if (savedTheme === 'dark') {
            $('html').attr('data-theme', 'dark');
            $btn.find('i').removeClass('fa-moon').addClass('fa-sun');
        }

        $btn.on('click', function() {
            const current = $('html').attr('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            $('html').attr('data-theme', next);
            localStorage.setItem('aci_theme', next);
            $(this).find('i').toggleClass('fa-moon fa-sun');
        });
    }

    // ── Tooltips and Popovers Init ─────────────────────────────
    function _initTooltips() {
        $('[data-bs-toggle="tooltip"]').tooltip();
        $('[data-bs-toggle="popover"]').popover();
    }

    // ── Initialize Everything ──────────────────────────────────
    function init() {
        _initAjax();
        _initSidebar();
        _initGlobalSearch();
        _initKeyboardShortcuts();
        _initThemeToggle();
        _initTooltips();
    }

    // ── Public API ─────────────────────────────────────────────
    return {
        init:               init,
        toast:              toast,
        dataTable:          dataTable,
        modal:              modal,
        confirmDelete:      confirmDelete,
        initFormValidation: initFormValidation,
        initAutoSave:       initAutoSave,
        clearDraft:         clearDraft,
        showLoading:        showLoading,
        hideLoading:        hideLoading,
        getCSRFToken:       _getCSRFToken
    };

})(jQuery);

// Initialize on DOM ready
$(function() {
    ACI.init();
});
