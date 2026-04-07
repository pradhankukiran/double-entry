<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Core\Database;
use DoubleE\Models\RecurringTemplate;
use DoubleE\Models\AuditLog;

class RecurringTransactionService
{
    private Database $db;
    private RecurringTemplate $templateModel;
    private JournalEntryService $journalEntryService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->templateModel = new RecurringTemplate();
        $this->journalEntryService = new JournalEntryService();
    }

    /**
     * Process all due recurring templates.
     *
     * Finds every active template whose next_run_date is today or earlier,
     * creates the corresponding journal entry, advances the schedule, and
     * deactivates the template if it has reached its occurrence limit or end date.
     *
     * @return array Summary with 'processed' count and 'entries' list of created entry IDs
     */
    public function processDue(): array
    {
        $dueTemplates = $this->templateModel->getDue();
        $entries = [];

        foreach ($dueTemplates as $template) {
            try {
                $entryId = $this->createFromTemplate((int) $template['id']);
                $entries[] = [
                    'template_id'   => (int) $template['id'],
                    'template_name' => $template['name'],
                    'entry_id'      => $entryId,
                ];
            } catch (\Throwable $e) {
                // Log the error but continue processing other templates
                $entries[] = [
                    'template_id'   => (int) $template['id'],
                    'template_name' => $template['name'],
                    'error'         => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => count($entries),
            'entries'   => $entries,
        ];
    }

    /**
     * Create a journal entry from a recurring template.
     *
     * Steps performed:
     * 1. Load the template and its lines
     * 2. Create a journal entry with the template's line items
     * 3. Optionally auto-post the entry if the template is configured for it
     * 4. Advance next_run_date and increment occurrences_created
     * 5. Deactivate the template if limits are reached
     *
     * @param int $templateId The recurring template ID
     *
     * @return int The newly created journal entry ID
     *
     * @throws \RuntimeException If the template is not found or not active
     */
    public function createFromTemplate(int $templateId): int
    {
        $template = $this->templateModel->getWithLines($templateId);

        if ($template === null) {
            throw new \RuntimeException("Recurring template #{$templateId} not found.");
        }

        if (!(int) $template['is_active']) {
            throw new \RuntimeException("Recurring template #{$templateId} is not active.");
        }

        $entryId = (int) $this->db->transaction(function () use ($template, $templateId) {
            // Build journal entry lines from the template
            $journalLines = [];
            foreach ($template['lines'] as $line) {
                $journalLines[] = [
                    'account_id'  => (int) $line['account_id'],
                    'debit'       => $line['debit'],
                    'credit'      => $line['credit'],
                    'description' => $line['description'] ?? '',
                ];
            }

            // Create the journal entry using today as the entry date
            $header = [
                'entry_date'  => date('Y-m-d'),
                'description' => $template['name'],
                'reference'   => "REC-{$templateId}",
            ];

            $userId = (int) $template['created_by'];

            if ((int) $template['auto_post']) {
                $entryId = $this->journalEntryService->createAndPost($header, $journalLines, $userId);
            } else {
                $entryId = $this->journalEntryService->create($header, $journalLines, $userId);
            }

            // Advance the schedule
            $nextRunDate = $this->calculateNextRunDate(
                $template['next_run_date'],
                $template['frequency']
            );

            $newOccurrences = (int) $template['occurrences_created'] + 1;

            // Determine if the template should be deactivated
            $isActive = 1;

            if ($template['total_occurrences'] !== null && $newOccurrences >= (int) $template['total_occurrences']) {
                $isActive = 0;
            }

            if ($template['end_date'] !== null && $nextRunDate > $template['end_date']) {
                $isActive = 0;
            }

            $this->templateModel->update($templateId, [
                'last_run_date'       => date('Y-m-d'),
                'next_run_date'       => $nextRunDate,
                'occurrences_created' => $newOccurrences,
                'is_active'           => $isActive,
            ]);

            return $entryId;
        });

        AuditLog::log(
            'recurring_template.processed',
            'recurring_template',
            $templateId,
            null,
            [
                'entry_id'     => $entryId,
                'auto_posted'  => (bool) $template['auto_post'],
                'occurrences'  => (int) $template['occurrences_created'] + 1,
            ]
        );

        return $entryId;
    }

    /**
     * Calculate the next run date by adding the appropriate interval to the current date.
     *
     * @param string $currentDate The current run date (Y-m-d)
     * @param string $frequency   One of: daily, weekly, monthly, quarterly, annually
     *
     * @return string The next run date in Y-m-d format
     */
    public function calculateNextRunDate(string $currentDate, string $frequency): string
    {
        $date = new \DateTimeImmutable($currentDate);

        $next = match ($frequency) {
            'daily'     => $date->modify('+1 day'),
            'weekly'    => $date->modify('+1 week'),
            'monthly'   => $date->modify('+1 month'),
            'quarterly' => $date->modify('+3 months'),
            'annually'  => $date->modify('+1 year'),
            default     => $date->modify('+1 month'),
        };

        return $next->format('Y-m-d');
    }
}
