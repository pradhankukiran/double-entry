<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\View;

class PdfService
{
    private View $view;

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Render a report view template within the PDF layout.
     *
     * Uses the 'layouts/pdf' layout which provides print-optimized styling
     * without sidebar or navigation elements.
     *
     * @param string $template View template path (e.g. 'reports/trial-balance')
     * @param array  $data     Data to pass to the template
     *
     * @return string Rendered HTML string
     */
    public function renderReportPdf(string $template, array $data): string
    {
        return $this->view->render($template, $data, 'layouts/pdf');
    }

    /**
     * Generate a PDF download from HTML content.
     *
     * Currently outputs print-friendly HTML with Content-Disposition header
     * for download. The browser's print-to-PDF can be used for true PDF output.
     *
     * @param string $html     Rendered HTML content
     * @param string $filename Suggested filename for the download (e.g. 'trial-balance.pdf')
     *
     * @return void
     */
    public function generatePdf(string $html, string $filename): void
    {
        // TODO: Replace with dompdf integration when available:
        //
        // use Dompdf\Dompdf;
        // use Dompdf\Options;
        //
        // $options = new Options();
        // $options->set('isHtml5ParserEnabled', true);
        // $options->set('isRemoteEnabled', false);
        // $options->set('defaultFont', 'sans-serif');
        //
        // $dompdf = new Dompdf($options);
        // $dompdf->loadHtml($html);
        // $dompdf->setPaper('A4', 'portrait');
        // $dompdf->render();
        // $dompdf->stream($filename, ['Attachment' => true]);

        // Fallback: serve the print-optimized HTML as a downloadable file
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $html;
        exit;
    }

    /**
     * Output the report HTML inline for browser viewing and printing.
     *
     * Sends the HTML directly to the browser. The user can then use
     * the browser's built-in print function (Ctrl+P) to save as PDF.
     *
     * @param string $html Rendered HTML content
     *
     * @return void
     */
    public function outputInline(string $html): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $html;
        exit;
    }
}
