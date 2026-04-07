/**
 * Double-E Accounting — Journal Entry Form JavaScript
 *
 * Handles dynamic line items, debit/credit mutual exclusion,
 * running totals, balance validation, and submit gating.
 */
(function () {
    'use strict';

    var linesBody      = document.getElementById('lines-body');
    var btnAddLine     = document.getElementById('btn-add-line');
    var totalDebitsEl  = document.getElementById('total-debits');
    var totalCreditsEl = document.getElementById('total-credits');
    var diffAmountEl   = document.getElementById('difference-amount');
    var diffRowEl      = document.getElementById('difference-row');
    var submitButtons  = document.querySelectorAll('.btn-submit');

    var accountGroups  = window.__jeAccounts || [];
    var lineIndex      = 0;

    /**
     * Build the <select> HTML for accounts, grouped by type.
     */
    function buildAccountOptions(idx) {
        var html = '<select class="form-select form-select-sm" name="line_account[' + idx + ']" style="border-radius: 0;" required>';
        html += '<option value="">-- Select Account --</option>';

        for (var g = 0; g < accountGroups.length; g++) {
            var group = accountGroups[g];
            html += '<optgroup label="' + escapeHtml(group.type) + '">';
            for (var a = 0; a < group.accounts.length; a++) {
                var acct = group.accounts[a];
                html += '<option value="' + acct.id + '">' + escapeHtml(acct.number) + ' - ' + escapeHtml(acct.name) + '</option>';
            }
            html += '</optgroup>';
        }

        html += '</select>';
        return html;
    }

    /**
     * Escape HTML special characters.
     */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * Add a new line item row to the table.
     */
    function addLine() {
        var idx = lineIndex++;
        var tr = document.createElement('tr');
        tr.setAttribute('data-line-index', idx);

        tr.innerHTML =
            '<td>' + buildAccountOptions(idx) + '</td>' +
            '<td>' +
                '<input type="text" class="form-control form-control-sm" name="line_description[' + idx + ']" placeholder="Line description" style="border-radius: 0;">' +
            '</td>' +
            '<td>' +
                '<input type="number" class="form-control form-control-sm text-end line-debit" name="line_debit[' + idx + ']" step="0.01" min="0" placeholder="0.00" style="border-radius: 0;">' +
            '</td>' +
            '<td>' +
                '<input type="number" class="form-control form-control-sm text-end line-credit" name="line_credit[' + idx + ']" step="0.01" min="0" placeholder="0.00" style="border-radius: 0;">' +
            '</td>' +
            '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-line" style="border-radius: 0;" title="Remove line">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</td>';

        linesBody.appendChild(tr);

        // Bind events on the new row
        var debitInput  = tr.querySelector('.line-debit');
        var creditInput = tr.querySelector('.line-credit');
        var removeBtn   = tr.querySelector('.btn-remove-line');

        debitInput.addEventListener('input', function () {
            if (parseFloat(this.value) > 0) {
                creditInput.value = '';
            }
            recalculate();
        });

        creditInput.addEventListener('input', function () {
            if (parseFloat(this.value) > 0) {
                debitInput.value = '';
            }
            recalculate();
        });

        removeBtn.addEventListener('click', function () {
            removeLine(tr);
        });

        return tr;
    }

    /**
     * Remove a line item row, enforcing the 2-line minimum.
     */
    function removeLine(tr) {
        var rows = linesBody.querySelectorAll('tr');
        if (rows.length <= 2) {
            return; // Keep at least 2 lines
        }
        tr.remove();
        recalculate();
    }

    /**
     * Recalculate totals and update the UI.
     */
    function recalculate() {
        var debits  = linesBody.querySelectorAll('.line-debit');
        var credits = linesBody.querySelectorAll('.line-credit');

        var totalDebit  = 0;
        var totalCredit = 0;

        for (var i = 0; i < debits.length; i++) {
            totalDebit += parseFloat(debits[i].value) || 0;
        }
        for (var j = 0; j < credits.length; j++) {
            totalCredit += parseFloat(credits[j].value) || 0;
        }

        var difference = Math.round((totalDebit - totalCredit) * 100) / 100;

        totalDebitsEl.textContent  = formatNumber(totalDebit);
        totalCreditsEl.textContent = formatNumber(totalCredit);
        diffAmountEl.textContent   = formatNumber(Math.abs(difference));

        // Color the difference row
        if (difference === 0 && totalDebit > 0) {
            diffAmountEl.style.color = '#198754'; // green (balanced)
            diffRowEl.className = '';
        } else if (difference !== 0) {
            diffAmountEl.style.color = '#dc3545'; // red (unbalanced)
            diffRowEl.className = '';
        } else {
            diffAmountEl.style.color = '#6c757d'; // muted (no entries yet)
            diffRowEl.className = '';
        }

        // Enable/disable submit buttons based on balance
        var isBalanced = (difference === 0 && totalDebit > 0);
        for (var k = 0; k < submitButtons.length; k++) {
            submitButtons[k].disabled = !isBalanced;
        }
    }

    /**
     * Format a number with exactly 2 decimal places and comma separators.
     */
    function formatNumber(num) {
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    /**
     * Initialize the form with the minimum required lines.
     */
    function init() {
        if (!linesBody || !btnAddLine) {
            return; // Not on the journal entry form page
        }

        // Add initial 2 lines
        addLine();
        addLine();

        // Bind "Add Line" button
        btnAddLine.addEventListener('click', function () {
            addLine();
        });

        // Initial calculation
        recalculate();
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
