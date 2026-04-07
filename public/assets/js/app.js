/**
 * Double-E Accounting — Application JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash alerts after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });

    // Highlight active sidebar link based on current URL
    var currentPath = window.location.pathname;
    document.querySelectorAll('#sidebar .list-group-item').forEach(function (link) {
        var href = link.getAttribute('href');
        if (href === currentPath || (href !== '/' && currentPath.startsWith(href))) {
            link.classList.add('active');
        }
    });

    // Confirm dialogs for destructive actions
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Global search autocomplete
    (function () {
        var searchInput = document.getElementById('globalSearch');
        var resultsDiv = document.getElementById('searchResults');
        if (!searchInput || !resultsDiv) return;

        var debounceTimer = null;
        var currentQuery = '';

        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim();
            currentQuery = q;

            clearTimeout(debounceTimer);

            if (q.length < 2) {
                resultsDiv.style.display = 'none';
                resultsDiv.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function () {
                fetch('/search/autocomplete?q=' + encodeURIComponent(q))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        // Only update if query hasn't changed
                        if (searchInput.value.trim() !== currentQuery) return;

                        if (!data || data.length === 0) {
                            resultsDiv.style.display = 'none';
                            resultsDiv.innerHTML = '';
                            return;
                        }

                        var html = '';
                        var typeIcons = {
                            account: 'bi-journal-text',
                            contact: 'bi-person',
                            invoice: 'bi-receipt',
                            journal: 'bi-book',
                            payment: 'bi-credit-card'
                        };

                        data.forEach(function (item) {
                            var icon = typeIcons[item.type] || 'bi-search';
                            html += '<a class="dropdown-item py-2" href="' + item.url + '">'
                                + '<i class="bi ' + icon + ' me-2 text-muted"></i>'
                                + '<span>' + escapeHtml(item.label) + '</span>'
                                + '<span class="badge bg-light text-muted ms-2" style="border-radius: 0; font-weight: normal;">' + escapeHtml(item.type) + '</span>'
                                + '</a>';
                        });

                        html += '<div class="dropdown-divider"></div>';
                        html += '<a class="dropdown-item text-center small text-muted py-2" href="/search?q=' + encodeURIComponent(q) + '">'
                            + 'View all results'
                            + '</a>';

                        resultsDiv.innerHTML = html;
                        resultsDiv.style.display = 'block';
                    })
                    .catch(function () {
                        resultsDiv.style.display = 'none';
                    });
            }, 300);
        });

        // Hide dropdown on blur (with delay so clicks register)
        searchInput.addEventListener('blur', function () {
            setTimeout(function () {
                resultsDiv.style.display = 'none';
            }, 200);
        });

        // Show dropdown on focus if it has content
        searchInput.addEventListener('focus', function () {
            if (resultsDiv.innerHTML.trim() !== '' && searchInput.value.trim().length >= 2) {
                resultsDiv.style.display = 'block';
            }
        });

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    })();
});
