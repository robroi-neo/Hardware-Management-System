<?php

namespace App\Services\Inventory;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchContextService
{
    public function formData(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user?->hasRole('admin') ?? false;
        $sessionBranch = $request->session()->get('branch', []);

        return [
            'isAdmin' => $isAdmin,
            'branches' => $isAdmin ? Branch::all() : collect(),
            'userDefaultBranchId' => $sessionBranch['id'] ?? null,
        ];
    }

    public function selectedBranchId(Request $request): ?int
    {
        $sessionBranch = $request->session()->get('branch', []);
        $branchId = (int) ($sessionBranch['id'] ?? 0);

        return $branchId > 0 ? $branchId : null;
    }

    public function resolveTerminalBranchId(Request $request): int
    {
        $branchId = $this->selectedBranchId($request);
        if ($branchId) {
            return $branchId;
        }

        $sessionTerminal = $request->session()->get('pos_terminal', []);
        $branchId = (int) ($sessionTerminal['branch_id'] ?? 0);

        if ($branchId < 1) {
            abort(422, 'Branch is not selected. Please select a branch first.');
        }

        return $branchId;
    }
}
