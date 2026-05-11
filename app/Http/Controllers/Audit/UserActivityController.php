<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $entityType = $request->query('entity_type', '');
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');

        $allowedSorts = ['created_at', 'entity_type', 'action', 'entity_id'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $entityTypes = collect([
            ['value' => 'sale', 'label' => 'Sales'],
            ['value' => 'purchase', 'label' => 'Purchases'],
            ['value' => 'user', 'label' => 'Users'],
            ['value' => 'product', 'label' => 'Products'],
            ['value' => 'other', 'label' => 'Other'],
        ]);

        $logsQuery = AuditLog::with('user');

        if ($search !== '') {
            $logsQuery->where(function ($query) use ($search) {
                $query->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhere('entity_id', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($entityType, ['sale', 'purchase', 'user', 'product', 'other'], true)) {
            $logsQuery->where('entity_type', $entityType);
        }

        $logs = $logsQuery
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        return view('modules.audit-logs.user-activity', [
            'logs' => $logs,
            'search' => $search,
            'entityType' => $entityType,
            'entityTypes' => $entityTypes,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }
}

