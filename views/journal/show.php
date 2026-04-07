<?php
/**
 * Journal Entry detail view.
 *
 * Variables: $entry (with 'lines' array), $reversingEntry, $reversedBy
 */

$statusBadges = [
    'draft'  => 'secondary',
    'posted' => 'success',
    'voided' => 'danger',
];
$badge = $statusBadges[$entry['status']] ?? 'secondary';

$isDraft  = ($entry['status'] === 'draft');
$isPosted = ($entry['status'] === 'posted');
$isVoided = ($entry['status'] === 'voided');

$lines       = $entry['lines'] ?? [];
$totalDebit  = 0;
$totalCredit = 0;
foreach ($lines as $line) {
    $totalDebit  += (float) $line['debit'];
    $totalCredit += (float) $line['credit'];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        Journal Entry
        <code class="text-dark ms-1"><?= \DoubleE\Core\View::e($entry['entry_number']) ?></code>
    </h4>
    <div class="d-flex gap-2">
        <a href="/journal" class="btn btn-outline-secondary" style="border-radius: 0;">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <?php if ($isDraft): ?>
            <a href="/journal/<?= (int) $entry['id'] ?>/edit" class="btn btn-outline-dark" style="border-radius: 0;">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form method="POST" action="/journal/<?= (int) $entry['id'] ?>/post" class="d-inline">
                <?= \DoubleE\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-dark" style="border-radius: 0;" onclick="return confirm('Post this journal entry? This action cannot be undone.');">
                    <i class="bi bi-check-lg me-1"></i> Post
                </button>
            </form>
        <?php endif; ?>
        <?php if ($isPosted): ?>
            <button type="button" class="btn btn-outline-danger" style="border-radius: 0;" data-bs-toggle="modal" data-bs-target="#voidModal">
                <i class="bi bi-x-circle me-1"></i> Void
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Entry Header -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Entry Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Entry Number</div>
                    <div class="col-md-9 fw-medium">
                        <code><?= \DoubleE\Core\View::e($entry['entry_number']) ?></code>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Date</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($entry['entry_date']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Description</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($entry['description']) ?></div>
                </div>
                <?php if (!empty($entry['reference'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Reference</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($entry['reference']) ?></div>
                </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Status</div>
                    <div class="col-md-9">
                        <span class="badge text-bg-<?= $badge ?>" style="border-radius: 0;">
                            <?= ucfirst(\DoubleE\Core\View::e($entry['status'])) ?>
                        </span>
                        <?php if ($isPosted && !empty($entry['posted_at'])): ?>
                            <span class="text-muted small ms-2">Posted on <?= \DoubleE\Core\View::e($entry['posted_at']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($entry['notes'])): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Notes</div>
                    <div class="col-md-9"><?= nl2br(\DoubleE\Core\View::e($entry['notes'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($isVoided): ?>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Voided On</div>
                    <div class="col-md-9"><?= \DoubleE\Core\View::e($entry['voided_at'] ?? '') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Void Reason</div>
                    <div class="col-md-9 text-danger"><?= \DoubleE\Core\View::e($entry['void_reason'] ?? '') ?></div>
                </div>
                <?php if ($reversingEntry !== null): ?>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Reversing Entry</div>
                    <div class="col-md-9">
                        <a href="/journal/<?= (int) $reversingEntry['id'] ?>" class="text-decoration-none">
                            <code><?= \DoubleE\Core\View::e($reversingEntry['entry_number']) ?></code>
                            &mdash; <?= \DoubleE\Core\View::e($reversingEntry['description']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php if ($reversedBy !== null): ?>
                <hr>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Reversed From</div>
                    <div class="col-md-9">
                        This is a reversing entry for
                        <a href="/journal/<?= (int) $reversedBy['id'] ?>" class="text-decoration-none">
                            <code><?= \DoubleE\Core\View::e($reversedBy['entry_number']) ?></code>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Line Items -->
        <div class="card border-0 shadow-sm" style="border-radius: 0;">
            <div class="card-header bg-white border-bottom py-3" style="border-radius: 0;">
                <h6 class="mb-0">Line Items</h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 130px;">Account #</th>
                            <th>Account Name</th>
                            <th>Description</th>
                            <th style="width: 140px;" class="text-end">Debit</th>
                            <th style="width: 140px;" class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lines)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No line items.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lines as $line): ?>
                                <tr>
                                    <td>
                                        <code class="text-dark"><?= \DoubleE\Core\View::e($line['account_number'] ?? '') ?></code>
                                    </td>
                                    <td>
                                        <a href="/ledger/account/<?= (int) $line['account_id'] ?>" class="text-decoration-none">
                                            <?= \DoubleE\Core\View::e($line['account_name'] ?? '') ?>
                                        </a>
                                    </td>
                                    <td class="text-muted"><?= \DoubleE\Core\View::e($line['description'] ?? '') ?></td>
                                    <td class="text-end font-monospace">
                                        <?= (float) $line['debit'] > 0 ? number_format((float) $line['debit'], 2) : '' ?>
                                    </td>
                                    <td class="text-end font-monospace">
                                        <?= (float) $line['credit'] > 0 ? number_format((float) $line['credit'], 2) : '' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="3" class="text-end">Totals:</td>
                            <td class="text-end font-monospace"><?= number_format($totalDebit, 2) ?></td>
                            <td class="text-end font-monospace"><?= number_format($totalCredit, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($isPosted): ?>
<!-- Void Modal -->
<div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0;">
            <form method="POST" action="/journal/<?= (int) $entry['id'] ?>/void">
                <?= \DoubleE\Core\Csrf::field() ?>
                <div class="modal-header border-bottom">
                    <h6 class="modal-title" id="voidModalLabel">Void Journal Entry</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        You are about to void entry <strong><?= \DoubleE\Core\View::e($entry['entry_number']) ?></strong>.
                        A reversing entry will be created automatically. This action cannot be undone.
                    </p>
                    <div class="mb-0">
                        <label for="void_reason" class="form-label">Reason for Voiding <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="void_reason"
                                  name="void_reason"
                                  rows="3"
                                  required
                                  placeholder="Explain why this entry is being voided..."
                                  style="border-radius: 0;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 0;">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 0;">
                        <i class="bi bi-x-circle me-1"></i> Void Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
