<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Services\ReportService;
use DoubleE\Services\PdfService;

class ReportController extends BaseController
{
    private ReportService $reportService;
    private PdfService $pdfService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->reportService = new ReportService();
        $this->pdfService = new PdfService();
    }

    /**
     * Report selection dashboard.
     */
    public function index(): Response
    {
        return $this->render('reports/index', [
            'pageTitle' => 'Financial Reports',
        ]);
    }

    /**
     * Trial Balance report.
     */
    public function trialBalance(): Response
    {
        $asOfDate = trim((string) $this->request->get('as_of_date', ''));

        if ($asOfDate === '') {
            $asOfDate = date('Y-m-d');
        }

        $report = $this->reportService->generateTrialBalance($asOfDate);

        return $this->render('reports/trial-balance', [
            'pageTitle' => 'Trial Balance',
            'report'    => $report,
            'asOfDate'  => $asOfDate,
        ]);
    }

    /**
     * Balance Sheet report.
     */
    public function balanceSheet(): Response
    {
        $asOfDate = trim((string) $this->request->get('as_of_date', ''));

        if ($asOfDate === '') {
            $asOfDate = date('Y-m-d');
        }

        $report = $this->reportService->generateBalanceSheet($asOfDate);

        return $this->render('reports/balance-sheet', [
            'pageTitle' => 'Balance Sheet',
            'report'    => $report,
            'asOfDate'  => $asOfDate,
        ]);
    }

    /**
     * Income Statement (P&L) report.
     */
    public function incomeStatement(): Response
    {
        $fromDate = trim((string) $this->request->get('from_date', ''));
        $toDate   = trim((string) $this->request->get('to_date', ''));

        if ($fromDate === '') {
            $fromDate = date('Y-m-01');
        }
        if ($toDate === '') {
            $toDate = date('Y-m-d');
        }

        $report = $this->reportService->generateIncomeStatement($fromDate, $toDate);

        return $this->render('reports/income-statement', [
            'pageTitle' => 'Income Statement',
            'report'    => $report,
            'fromDate'  => $fromDate,
            'toDate'    => $toDate,
        ]);
    }

    /**
     * Cash Flow Statement report.
     */
    public function cashFlow(): Response
    {
        $fromDate = trim((string) $this->request->get('from_date', ''));
        $toDate   = trim((string) $this->request->get('to_date', ''));

        if ($fromDate === '') {
            $fromDate = date('Y-m-01');
        }
        if ($toDate === '') {
            $toDate = date('Y-m-d');
        }

        $report = $this->reportService->generateCashFlowStatement($fromDate, $toDate);

        return $this->render('reports/cash-flow', [
            'pageTitle' => 'Cash Flow Statement',
            'report'    => $report,
            'fromDate'  => $fromDate,
            'toDate'    => $toDate,
        ]);
    }

    /**
     * Export a report as PDF.
     */
    public function exportPdf(string $report): Response
    {
        $validReports = ['trial-balance', 'balance-sheet', 'income-statement', 'cash-flow'];

        if (!in_array($report, $validReports, true)) {
            $this->flash('error', 'Invalid report type.');
            return $this->redirect('/reports');
        }

        $asOfDate = trim((string) $this->request->get('as_of_date', ''));
        $fromDate = trim((string) $this->request->get('from_date', ''));
        $toDate   = trim((string) $this->request->get('to_date', ''));

        switch ($report) {
            case 'trial-balance':
                if ($asOfDate === '') {
                    $asOfDate = date('Y-m-d');
                }
                $data = $this->reportService->generateTrialBalance($asOfDate);
                $data['asOfDate'] = $asOfDate;
                $template = 'reports/trial-balance';
                $filename = 'trial-balance-' . $asOfDate . '.pdf';
                break;

            case 'balance-sheet':
                if ($asOfDate === '') {
                    $asOfDate = date('Y-m-d');
                }
                $data = $this->reportService->generateBalanceSheet($asOfDate);
                $data['asOfDate'] = $asOfDate;
                $template = 'reports/balance-sheet';
                $filename = 'balance-sheet-' . $asOfDate . '.pdf';
                break;

            case 'income-statement':
                if ($fromDate === '') {
                    $fromDate = date('Y-m-01');
                }
                if ($toDate === '') {
                    $toDate = date('Y-m-d');
                }
                $data = $this->reportService->generateIncomeStatement($fromDate, $toDate);
                $data['fromDate'] = $fromDate;
                $data['toDate'] = $toDate;
                $template = 'reports/income-statement';
                $filename = 'income-statement-' . $fromDate . '-to-' . $toDate . '.pdf';
                break;

            case 'cash-flow':
                if ($fromDate === '') {
                    $fromDate = date('Y-m-01');
                }
                if ($toDate === '') {
                    $toDate = date('Y-m-d');
                }
                $data = $this->reportService->generateCashFlowStatement($fromDate, $toDate);
                $data['fromDate'] = $fromDate;
                $data['toDate'] = $toDate;
                $template = 'reports/cash-flow';
                $filename = 'cash-flow-' . $fromDate . '-to-' . $toDate . '.pdf';
                break;
        }

        $pdf = $this->pdfService->renderReportPdf($template, $data);

        $this->response->setHeader('Content-Type', 'application/pdf');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->response->setBody($pdf);

        return $this->response;
    }
}
