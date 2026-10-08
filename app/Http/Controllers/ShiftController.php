<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\PosSetting;
use App\Models\Shift;
use App\Models\ShiftCashMovement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShiftController extends Controller
{
    /**
     * Get active shift data and current POS settings
     */
    public function current(): JsonResponse
    {
        $shift = Shift::where('status', 'open')->latest()->first();
        $requireShift = (bool) PosSetting::get('require_user_shift', true);
        $permissions = PosSetting::get('shift_report_permission', ['cash_movement' => true, 'sales_summary' => true]);

        if (!$shift) {
            return response()->json([
                'success' => true,
                'has_active_shift' => false,
                'shift' => null,
                'metrics' => null,
                'settings' => [
                    'require_user_shift' => $requireShift,
                    'shift_report_permission' => $permissions,
                ],
            ]);
        }

        $metrics = $shift->calculateMetrics();
        $movements = $shift->cashMovements()->latest()->take(20)->get();

        return response()->json([
            'success' => true,
            'has_active_shift' => true,
            'shift' => $shift,
            'metrics' => $metrics,
            'recent_movements' => $movements,
            'settings' => [
                'require_user_shift' => $requireShift,
                'shift_report_permission' => $permissions,
            ],
        ]);
    }

    /**
     * Start a new shift
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'cashier_name' => 'nullable|string|max:100',
            'open_cash_drawer' => 'nullable|boolean',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $existingShift = Shift::where('status', 'open')->first();
        if ($existingShift) {
            return response()->json([
                'success' => false,
                'message' => 'A shift is already open by ' . $existingShift->opened_by,
            ], 422);
        }

        $cashierName = $request->cashier_name ?: (auth()->user()->name ?? 'Admin');

        // Server-side branch resolution: user assigned branch, or owner/manager selection
        $user = auth()->user();
        if ($user && $user->branch_id) {
            $branchId = $user->branch_id;
        } elseif ($user && ($user->isOwner() || $user->isManager())) {
            $branchId = $request->branch_id;
        } else {
            $branchId = null;
        }

        $shift = Shift::create([
            'opened_by' => $cashierName,
            'opened_at' => now(),
            'starting_cash' => $request->starting_cash,
            'cash_in' => 0,
            'cash_out' => 0,
            'expected_cash' => $request->starting_cash,
            'status' => 'open',
            'authorized_by' => auth()->id(),
            'branch_id' => $branchId,
        ]);

        // Audit log
        AuditLog::log(
            'shift_started',
            'shifts',
            "Shift started by {$cashierName} with starting cash ₱" . number_format($shift->starting_cash, 2),
            null,
            'shift',
            $shift->id,
            ['starting_cash' => $shift->starting_cash]
        );

        return response()->json([
            'success' => true,
            'shift' => $shift,
            'metrics' => $shift->calculateMetrics(),
            'message' => 'Shift started successfully!',
        ]);
    }

    /**
     * Record Cash In or Cash Out during the active shift
     */
    public function recordCashMovement(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:cash_in,cash_out',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $shift = Shift::where('status', 'open')->latest()->first();
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found. Please start a shift first.',
            ], 422);
        }

        $cashierName = auth()->user()->name ?? $shift->opened_by;

        $movement = ShiftCashMovement::create([
            'shift_id' => $shift->id,
            'type' => $request->type,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'cashier_name' => $cashierName,
            'authorized_by' => auth()->id(),
        ]);

        $metrics = $shift->calculateMetrics();

        // Update cached expected cash on shift
        $shift->update([
            'cash_in' => $metrics['cash_in'],
            'cash_out' => $metrics['cash_out'],
            'expected_cash' => $metrics['expected_cash'],
        ]);

        $typeLabel = $request->type === 'cash_in' ? 'Cash In (Pay-in)' : 'Cash Out (Payout/Drop)';
        AuditLog::log(
            'shift_cash_movement',
            'shifts',
            "{$typeLabel} of ₱" . number_format($request->amount, 2) . " for '{$request->reason}' by {$cashierName}",
            null,
            'shift',
            $shift->id,
            ['type' => $request->type, 'amount' => $request->amount, 'reason' => $request->reason]
        );

        return response()->json([
            'success' => true,
            'movement' => $movement,
            'metrics' => $metrics,
            'message' => "{$typeLabel} recorded successfully!",
        ]);
    }

    /**
     * End / Close shift with cash reconciliation and supervisor/manager security authorization
     */
    public function end(Request $request): JsonResponse
    {
        $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'discrepancy_reason' => 'nullable|string|max:500',
            'auth_email' => 'required|email',
            'auth_password' => 'required|string',
        ]);

        // Security Authorization Check: Verify Supervisor, Manager, or Owner credentials
        $authorizer = User::where('email', $request->auth_email)->first();
        if (!$authorizer || !Hash::check($request->auth_password, $authorizer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password. Authorization failed.',
            ], 401);
        }

        if (!$authorizer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'The authorizing account is currently inactive.',
            ], 403);
        }

        if (!$authorizer->isAtLeast('manager')) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Ending a shift requires authorization from a Manager or Owner.',
            ], 403);
        }

        $shift = Shift::where('status', 'open')->latest()->first();
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found to close.',
            ], 422);
        }

        $metrics = $shift->calculateMetrics();
        $expectedCash = $metrics['expected_cash'];
        $actualCash = (float) $request->actual_cash;
        $difference = $actualCash - $expectedCash;

        $status = 'balanced';
        if (round($difference, 2) < 0) {
            $status = 'short';
        } elseif (round($difference, 2) > 0) {
            $status = 'over';
        }

        $closedBy = auth()->user()->name ?? 'Admin';

        $shift->update([
            'closed_by' => $closedBy,
            'authorized_by' => $authorizer->id,
            'closed_at' => now(),
            'cash_in' => $metrics['cash_in'],
            'cash_out' => $metrics['cash_out'],
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'status' => $status,
            'discrepancy_reason' => $request->discrepancy_reason,
        ]);

        AuditLog::log(
            'shift_ended',
            'shifts',
            "Shift ended by {$closedBy}, authorized by {$authorizer->name} ({$authorizer->role}). Expected: ₱" . number_format($expectedCash, 2) . ", Actual: ₱" . number_format($actualCash, 2) . " (Status: Cash " . ucfirst($status) . ", Diff: ₱" . number_format($difference, 2) . ")",
            null,
            'shift',
            $shift->id,
            [
                'expected_cash' => $expectedCash,
                'actual_cash' => $actualCash,
                'difference' => $difference,
                'status' => $status,
                'authorized_by' => $authorizer->name,
                'authorizer_role' => $authorizer->role,
            ]
        );

        return response()->json([
            'success' => true,
            'shift' => $shift,
            'metrics' => $metrics,
            'authorized_by' => $authorizer->name,
            'authorizer_role' => ucfirst($authorizer->role),
            'status_label' => $status === 'balanced' ? 'Cash Balanced' : ($status === 'short' ? 'Cash Short' : 'Cash Over'),
            'message' => "Shift successfully closed and authorized by {$authorizer->name} (" . ucfirst($authorizer->role) . ")!",
        ]);
    }

    /**
     * Update POS Settings (User shift toggle, Shift Report Permissions)
     */
    public function updateSettings(Request $request): JsonResponse
    {
        if (!auth()->user()->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Managers and Owners can modify POS shift settings.',
            ], 403);
        }

        $request->validate([
            'require_user_shift' => 'required|boolean',
            'shift_report_permission' => 'nullable|array',
        ]);

        PosSetting::set('require_user_shift', $request->boolean('require_user_shift') ? '1' : '0');

        if ($request->has('shift_report_permission')) {
            PosSetting::set('shift_report_permission', $request->shift_report_permission);
        }

        AuditLog::log(
            'pos_settings_updated',
            'settings',
            'Updated POS Shift Settings by ' . auth()->user()->name,
            null,
            'setting',
            null,
            ['require_user_shift' => $request->require_user_shift]
        );

        return response()->json([
            'success' => true,
            'settings' => [
                'require_user_shift' => (bool) PosSetting::get('require_user_shift', true),
                'shift_report_permission' => PosSetting::get('shift_report_permission', ['cash_movement' => true, 'sales_summary' => true]),
            ],
            'message' => 'Shift settings saved successfully!',
        ]);
    }

    /**
     * Trigger manual drawer open
     */
    public function openDrawer(): JsonResponse
    {
        AuditLog::log(
            'drawer_opened_manually',
            'drawer',
            'Cash drawer opened manually by ' . (auth()->user()->name ?? 'Cashier')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cash drawer pulse triggered successfully!',
        ]);
    }
}
