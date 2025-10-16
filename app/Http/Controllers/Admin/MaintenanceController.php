<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance management page.
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->hasPermission('schedule_maintenance') && !auth()->user()->hasPermission('enable_maintenance_mode')) {
            abort(403, 'You do not have permission to access maintenance management.');
        }

        $activeSchedules = MaintenanceSchedule::active()
            ->with('creator')
            ->orderBy('start_datetime', 'asc')
            ->get();

        $upcomingSchedules = MaintenanceSchedule::upcoming()
            ->with('creator')
            ->orderBy('start_datetime', 'asc')
            ->limit(10)
            ->get();

        $recentSchedules = MaintenanceSchedule::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.maintenance.index', compact('activeSchedules', 'upcomingSchedules', 'recentSchedules'));
    }

    /**
     * Store a new maintenance schedule.
     */
    public function store(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('schedule_maintenance')) {
            abort(403, 'You do not have permission to schedule maintenance.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.maintenance.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Check for overlapping maintenance schedules
        $overlapping = MaintenanceSchedule::where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_datetime', [$request->start_datetime, $request->end_datetime])
                    ->orWhereBetween('end_datetime', [$request->start_datetime, $request->end_datetime])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_datetime', '<=', $request->start_datetime)
                          ->where('end_datetime', '>=', $request->end_datetime);
                    });
            })
            ->exists();

        if ($overlapping) {
            return redirect()->route('admin.maintenance.index')
                ->withErrors(['overlap' => 'There is already a maintenance schedule during this time period.'])
                ->withInput();
        }

        MaintenanceSchedule::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance schedule created successfully.');
    }

    /**
     * Cancel a maintenance schedule.
     */
    public function cancel(MaintenanceSchedule $schedule)
    {
        // Check permission
        if (!auth()->user()->hasPermission('enable_maintenance_mode')) {
            abort(403, 'You do not have permission to cancel maintenance.');
        }

        $schedule->update([
            'is_active' => false,
            'is_completed' => true,
        ]);

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance schedule cancelled successfully.');
    }

    /**
     * Get maintenance status for API calls.
     */
    public function status()
    {
        $isActive = MaintenanceSchedule::hasActiveMaintenance();
        $currentMaintenance = null;

        if ($isActive) {
            $currentMaintenance = MaintenanceSchedule::getCurrentActiveMaintenance();
        }

        return response()->json([
            'is_active' => $isActive,
            'maintenance' => $currentMaintenance,
        ]);
    }

    /**
     * Enable immediate maintenance mode.
     */
    public function enableImmediate(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasPermission('enable_maintenance_mode')) {
            abort(403, 'You do not have permission to enable maintenance mode.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'duration' => 'required|integer|min:15|max:1440', // 15 minutes to 24 hours
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.maintenance.index')
                ->withErrors($validator)
                ->withInput();
        }

        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addMinutes((int) $request->duration);

        MaintenanceSchedule::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_datetime' => $startTime,
            'end_datetime' => $endTime,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Immediate maintenance mode enabled successfully.');
    }
}
