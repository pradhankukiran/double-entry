<?php

declare(strict_types=1);

namespace DoubleE\Services;

use DoubleE\Models\Account;

class ChartOfAccountsService
{
    private Account $accountModel;

    public function __construct()
    {
        $this->accountModel = new Account();
    }

    /**
     * Build a nested tree structure from the flat account hierarchy.
     * Each node includes a 'children' array of its child accounts.
     *
     * @return array Tree of accounts with nested children
     */
    public function getHierarchyTree(): array
    {
        $accounts = $this->accountModel->getHierarchy();

        // Index all accounts by ID for O(1) lookup
        $indexed = [];
        foreach ($accounts as &$account) {
            $account['children'] = [];
            $indexed[$account['id']] = &$account;
        }
        unset($account);

        // Build the tree by assigning children to their parents
        $tree = [];
        foreach ($indexed as &$account) {
            if (!empty($account['parent_id']) && isset($indexed[$account['parent_id']])) {
                $indexed[$account['parent_id']]['children'][] = &$account;
            } else {
                $tree[] = &$account;
            }
        }

        return $tree;
    }

    /**
     * Validate that an account number is unique.
     *
     * @param string   $number    The account number to validate
     * @param int|null $excludeId ID to exclude (for updates)
     */
    public function validateAccountNumber(string $number, ?int $excludeId = null): bool
    {
        $existing = $this->accountModel->findByNumber($number);

        if ($existing === null) {
            return true;
        }

        return $excludeId !== null && (int) $existing['id'] === $excludeId;
    }

    /**
     * Check whether an account can be safely deactivated.
     * An account cannot be deactivated if it has children, transactions, or is a system account.
     */
    public function canDeactivate(int $accountId): bool
    {
        $account = $this->accountModel->find($accountId);

        if ($account === null) {
            return false;
        }

        // System accounts cannot be deactivated
        if (!empty($account['is_system'])) {
            return false;
        }

        // Accounts with children cannot be deactivated
        $children = $this->accountModel->getChildren($accountId);
        if (!empty($children)) {
            return false;
        }

        // Accounts with transactions cannot be deactivated
        if ($this->accountModel->hasTransactions($accountId)) {
            return false;
        }

        return true;
    }

    /**
     * Build a formatted list of accounts for use in <select> dropdowns.
     * Accounts are indented by depth to reflect the hierarchy.
     *
     * @return array List of ['id' => int, 'label' => string, 'number' => string]
     */
    public function buildAccountDropdown(bool $activeOnly = true): array
    {
        $tree = $this->getHierarchyTree();
        $options = [];

        $this->flattenTreeForDropdown($tree, $options, 0);

        return $options;
    }

    /**
     * Recursively flatten a tree into an indented dropdown list.
     */
    private function flattenTreeForDropdown(array $nodes, array &$options, int $depth): void
    {
        foreach ($nodes as $node) {
            $indent = str_repeat("\xC2\xA0\xC2\xA0", $depth); // Non-breaking spaces for indentation
            $options[] = [
                'id'     => (int) $node['id'],
                'label'  => $indent . $node['account_number'] . ' - ' . $node['name'],
                'number' => $node['account_number'],
            ];

            if (!empty($node['children'])) {
                $this->flattenTreeForDropdown($node['children'], $options, $depth + 1);
            }
        }
    }
}
