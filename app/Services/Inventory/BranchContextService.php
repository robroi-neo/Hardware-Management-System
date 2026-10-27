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

        if (! $branchId) {
            abort(422, 'Branch is not selected. Please select a branch first.');
        }

        return $branchId;
    }

    /**
     * Branch to filter a list by: admins get what they asked for (null = all
     * branches); everyone else is locked to their session branch.
     */
    public function scopedBranchId(Request $request, mixed $requested = null): ?int
    {
        if ($request->user()?->hasRole('admin')) {
            return ((int) $requested) ?: null;
        }

        return $this->selectedBranchId($request);
    }

    /**
     * Whether the current user may see or act on a record from this branch.
     */
    public function canAccessBranch(Request $request, ?int $branchId): bool
    {
        if ($request->user()?->hasRole('admin')) {
            return true;
        }

        return $branchId !== null && $branchId === $this->selectedBranchId($request);
    }
}
