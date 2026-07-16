<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->first();

        // Get filter inputs
        $period      = $request->input('period', 'monthly');
        $dateVal     = $request->input('date', now()->toDateString());
        $weekVal     = $request->input('week', now()->format('Y-\WW'));
        $filterMonth = $request->input('filter_month', now()->format('Y-m'));
        $filterYear  = $request->input('filter_year', now()->year);

        // Determine start and end dates
        if ($period === 'daily' && $request->filled('date')) {
            $startDate = Carbon::parse($dateVal);
            $endDate   = $startDate->copy();
        } elseif ($period === 'weekly' && $request->filled('week')) {
            try {
                $startDate = Carbon::parse($weekVal . '-1');
                $endDate   = $startDate->copy()->endOfWeek();
            } catch (\Exception $e) {
                $startDate = now()->startOfWeek();
                $endDate   = now()->endOfWeek();
            }
        } elseif ($period === 'monthly' && $request->filled('filter_month')) {
            $startDate = Carbon::parse($filterMonth . '-01')->startOfMonth();
            $endDate   = $startDate->copy()->endOfMonth();
        } elseif ($period === 'yearly') {
            $startDate = Carbon::create($filterYear, 1, 1)->startOfYear();
            $endDate   = $startDate->copy()->endOfYear();
        } else {
            // Default to monthly current
            $startDate = now()->startOfMonth();
            $endDate   = now()->endOfMonth();
        }

        // Cap end date at today so we don't display future days as absent
        $capEndDate = $endDate->gt(today()) ? today() : $endDate;

        // Generate date range descending
        $dates = [];
        if ($startDate->lte($capEndDate)) {
            $curr = $startDate->copy();
            while ($curr->lte($capEndDate)) {
                $dates[] = $curr->toDateString();
                $curr->addDay();
            }
        }
        $dates = array_reverse($dates);

        // Query attendance records for the range
        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy('date');

        // Leave & Permission history lists
        $leaveHistory = Attendance::where('user_id', auth()->id())
            ->where('status', 'leave')
            ->latest('date')
            ->get();

        $permissionHistory = Attendance::where('user_id', auth()->id())
            ->where('status', 'permit')
            ->latest('date')
            ->get();

        $leaveQuotaUsed = Attendance::leavePermissionQuotaUsed(auth()->id());
        $leaveQuotaMax = Attendance::LEAVE_PERMISSION_QUOTA_PER_YEAR;
        $canSubmitLeaveRequest = Attendance::canSubmitLeavePermission(auth()->id());

        return view('kurir.attendance.index', compact(
            'attendance',
            'dates',
            'attendances',
            'leaveHistory',
            'permissionHistory',
            'period',
            'dateVal',
            'weekVal',
            'filterMonth',
            'filterYear',
            'leaveQuotaUsed',
            'leaveQuotaMax',
            'canSubmitLeaveRequest'
        ));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // Base64 from camera
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string',
        ]);

        $approvedExcused = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->whereIn('status', ['leave', 'permit'])
            ->where('approval_status', 'approved')
            ->first();

        if ($approvedExcused) {
            $label = $approvedExcused->status === 'leave' ? 'Leave' : 'Permission';
            return redirect()->back()->with('error', "You are currently on {$label}. Attendance is not allowed.");
        }

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->first();

        if ($attendance) {
            return redirect()->back()->with('error', 'Already checked in today.');
        }

        // Handle base64 photo
        $img = $request->photo;
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $fileName = 'attendance/' . auth()->id() . '_' . time() . '.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $data);

        Attendance::create([
            'user_id' => auth()->id(),
            'date' => Carbon::today(),
            'check_in' => Carbon::now()->toTimeString(),
            'photo_path' => $fileName,
            'status' => Carbon::now()->hour > 8 ? 'late' : 'present', // Hardcoded 8 AM limit
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name ?: 'Unknown Location',
        ]);

        return redirect()->back()->with('success', 'Checked in successfully.');
    }

    public function checkOut(Request $request)
    {
        $approvedExcused = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->whereIn('status', ['leave', 'permit'])
            ->where('approval_status', 'approved')
            ->first();

        if ($approvedExcused) {
            $label = $approvedExcused->status === 'leave' ? 'Leave' : 'Permission';
            return redirect()->back()->with('error', "You are currently on {$label}. Attendance is not allowed.");
        }

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Check-in first.');
        }

        if ($attendance->check_out) {
            return redirect()->back()->with('error', 'Already checked out.');
        }

        $attendance->update([
            'check_out' => Carbon::now()->toTimeString(),
            'latitude' => $request->latitude ?: $attendance->latitude,
            'longitude' => $request->longitude ?: $attendance->longitude,
            'location_name' => $request->location_name ?: $attendance->location_name,
        ]);

        return redirect()->back()->with('success', 'Checked out successfully.');
    }

    public function applyPermitLeave(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:permit,leave',
            'document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'reason' => 'nullable|string|max:255',
        ]);

        // Check if attendance already exists for this date
        $existing = Attendance::where('user_id', auth()->id())
            ->where('date', $request->date)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Attendance record already exists for the selected date.');
        }

        if (!Attendance::canSubmitLeavePermission(auth()->id())) {
            return redirect()->back()->with('error', 'Your annual leave/permission quota has been fully used. You cannot submit another request this year.');
        }

        // Handle file upload
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $extension = $file->getClientOriginalExtension();
            $filename = auth()->id() . '_' . time() . '.' . $extension;
            $file->storeAs('attendance/documents', $filename, 'public');
            $fileName = 'attendance/documents/' . $filename;
        } else {
            return redirect()->back()->with('error', 'Document upload failed.');
        }

        Attendance::create([
            'user_id' => auth()->id(),
            'date' => $request->date,
            'status' => $request->type, // permit or leave
            'approval_status' => 'pending',
            'document_path' => $fileName,
            'reject_reason' => $request->reason,
        ]);

        $message = $request->type === 'leave'
            ? 'Leave request submitted successfully.'
            : 'Permission request submitted successfully.';

        return redirect()->back()->with('success', $message);
    }
}
