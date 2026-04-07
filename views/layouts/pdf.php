<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \DoubleE\Core\View::e($pageTitle ?? 'Report') ?> - Double-E Accounting</title>
    <style>
        /* Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1a1a1a;
            background: #fff;
            padding: 20px 30px;
        }

        /* Report header */
        .report-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #1a1a1a;
        }

        .report-header .company-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .report-header .report-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .report-header .report-date {
            font-size: 11px;
            color: #555;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th, td {
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: 600;
            background: #f5f5f5;
            border-bottom: 2px solid #999;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        td.amount, th.amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* Section headers */
        .section-header {
            font-size: 13px;
            font-weight: 700;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #999;
        }

        .subsection-header {
            font-size: 11px;
            font-weight: 600;
            color: #555;
            padding: 6px 8px;
            background: #fafafa;
        }

        /* Totals */
        tr.subtotal td {
            font-weight: 600;
            border-top: 1px solid #999;
            border-bottom: none;
        }

        tr.grand-total td {
            font-weight: 700;
            font-size: 13px;
            border-top: 2px solid #1a1a1a;
            border-bottom: 3px double #1a1a1a;
        }

        /* Status indicators */
        .balanced {
            color: #1a7f37;
            font-weight: 600;
        }

        .unbalanced {
            color: #cf222e;
            font-weight: 600;
        }

        .negative {
            color: #cf222e;
        }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #777;
            text-align: center;
        }

        /* Print button (hidden when printing) */
        .print-controls {
            text-align: right;
            margin-bottom: 16px;
        }

        .print-controls button {
            font-size: 12px;
            padding: 6px 16px;
            border: 1px solid #999;
            background: #f5f5f5;
            cursor: pointer;
        }

        .print-controls button:hover {
            background: #e5e5e5;
        }

        /* Print styles */
        @media print {
            body {
                padding: 0;
                font-size: 10px;
            }

            .print-controls {
                display: none;
            }

            .report-header {
                margin-bottom: 16px;
                padding-bottom: 10px;
            }

            .report-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 8px 30px;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            .section-header {
                page-break-after: avoid;
            }

            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="report-header">
        <div class="company-name">Double-E Accounting</div>
        <div class="report-title"><?= \DoubleE\Core\View::e($pageTitle ?? 'Report') ?></div>
        <div class="report-date">Generated: <?= date('F j, Y \a\t g:i A') ?></div>
    </div>

    <?= $content ?? '' ?>

    <div class="report-footer">
        Double-E Accounting &mdash; Report generated <?= date('Y-m-d H:i:s T') ?>
    </div>
</body>
</html>
