<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\Customer;
use App\Models\Golongan;
use App\Models\MeterPeriod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ChangeLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        $filters = [
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'role' => $request->query('role', 'all'),
        ];

        $databaseName = null;
        try {
            $databaseName = DB::connection()->getDatabaseName();
        } catch (\Throwable) {
            $databaseName = null;
        }

        try {
            if (!Schema::hasTable('change_logs')) {
                return Inertia::render('Logs/Index', [
                    'logs' => [],
                    'filters' => $filters,
                    'undoableIds' => [],
                    'missingTable' => true,
                    'databaseName' => $databaseName,
                ]);
            }
        } catch (\Throwable) {
            return Inertia::render('Logs/Index', [
                'logs' => [],
                'filters' => $filters,
                'undoableIds' => [],
                'missingTable' => true,
                'databaseName' => $databaseName,
            ]);
        }

        try {
            $logsQuery = ChangeLog::query()->with('user')->orderByDesc('created_at');

            if (!empty($filters['from'])) {
                $logsQuery->whereDate('created_at', '>=', $filters['from']);
            }

            if (!empty($filters['to'])) {
                $logsQuery->whereDate('created_at', '<=', $filters['to']);
            }

            if (in_array($filters['role'], [User::ROLE_ADMIN, User::ROLE_PETUGAS, User::ROLE_USER], true)) {
                $logsQuery->where('role', $filters['role']);
            }

            $logs = $logsQuery->limit(200)->get()->map(function (ChangeLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'role' => $log->role,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ] : null,
                    'created_at' => $log->created_at?->toDateTimeString(),
                    'undone_at' => $log->undone_at?->toDateTimeString(),
                    'undoable' => $log->undo !== null && $log->undone_at === null,
                ];
            });

            $undoableIds = ChangeLog::query()
                ->whereNotNull('undo')
                ->whereNull('undone_at')
                ->orderByDesc('created_at')
                ->limit(3)
                ->pluck('id')
                ->values()
                ->all();
        } catch (QueryException|\Throwable) {
            return Inertia::render('Logs/Index', [
                'logs' => [],
                'filters' => $filters,
                'undoableIds' => [],
                'missingTable' => true,
                'databaseName' => $databaseName,
            ]);
        }

        return Inertia::render('Logs/Index', [
            'logs' => $logs,
            'filters' => $filters,
            'undoableIds' => $undoableIds,
            'databaseName' => $databaseName,
        ]);
    }

    public function undo(Request $request, ChangeLog $changeLog): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        if (!Schema::hasTable('change_logs')) {
            return redirect()->back()->with('error', 'Tabel log belum tersedia.');
        }

        if ($changeLog->undone_at || empty($changeLog->undo)) {
            return redirect()->back()->with('error', 'Perubahan tidak dapat diurungkan.');
        }

        $undo = $changeLog->undo;
        $result = $this->applyUndo($undo);

        if (!$result) {
            return redirect()->back()->with('error', 'Gagal mengurungkan perubahan.');
        }

        $changeLog->update(['undone_at' => now()]);

        return redirect()->back()->with('status', 'Perubahan berhasil diurungkan.');
    }

    private function applyUndo(array $undo): bool
    {
        $type = $undo['type'] ?? null;

        if ($type === 'update') {
            $model = $undo['model'] ?? null;
            $id = $undo['id'] ?? null;
            $data = $undo['data'] ?? null;

            if (!in_array($model, [
                User::class,
                Area::class,
                Golongan::class,
                Customer::class,
                MeterPeriod::class,
            ], true)) {
                return false;
            }

            if (!$id || !is_array($data)) {
                return false;
            }

            return (bool) $model::query()->whereKey($id)->update($data);
        }

        return false;
    }

    private function ensureAdmin(?User $user): void
    {
        abort_if(!$user || !$user->isAdmin(), 403);
    }
}
