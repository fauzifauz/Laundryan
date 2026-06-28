<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today())
            ->first();

        $leaveQuotaUsed = Attendance::leavePermissionQuotaUsed(auth()->id());
        $leaveQuotaMax = Attendance::LEAVE_PERMISSION_QUOTA_PER_YEAR;
        $canSubmitLeaveRequest = Attendance::canSubmitLeavePermission(auth()->id());

        return view('kurir.attendance.index', compact(
            'attendance',
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

    public function checkOut()
    {
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

        return redirect()->back()->with('success', 'Permit/Leave request submitted successfully.');
    }
}
