<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UserController extends Controller
{
    private function buildBaseQuery(Request $request, bool $includePeriod = false)
    {
        $query = User::query();

        // Search filter (Name, Email, Phone, Address, NIK)
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Start date filter (registered this week, etc)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        // New registrations filter (registered in the current month)
        if ($request->input('new_registrations') === '1') {
            if (\DB::connection()->getDriverName() === 'sqlite') {
                $query->whereRaw("strftime('%m', created_at) = ?", [sprintf('%02d', now()->month)])
                      ->whereRaw("strftime('%Y', created_at) = ?", [now()->year]);
            } else {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            }
        }

        // Returning customers filter
        if ($request->input('returning') === '1') {
            $query->has('customerOrders', '>=', 2);
        }

        // Month & Year filter (only applied for reports/exports, not main search)
        if ($includePeriod) {
            // Month filter
            if ($request->filled('month') && $request->input('month') !== 'all') {
                if (\DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("strftime('%m', created_at) = ?", [sprintf('%02d', $request->input('month'))]);
                } else {
                    $query->whereMonth('created_at', $request->input('month'));
                }
            }

            // Year filter
            if ($request->filled('year') && $request->input('year') !== 'all') {
                if (\DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw("strftime('%Y', created_at) = ?", [$request->input('year')]);
                } else {
                    $query->whereYear('created_at', $request->input('year'));
                }
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSortColumns = ['name', 'email', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        return $query->orderBy($sortBy, $sortDirection);
    }

    public function index(Request $request)
    {
        // Build base query (Do not include period month/year filters on main page display)
        $baseQuery = $this->buildBaseQuery($request, false);

        // Fetch paginated data for each role separately
        $admins = (clone $baseQuery)->where('role', 'admin')->paginate(10, ['*'], 'admin_page')->withQueryString();
        $karyawans = (clone $baseQuery)->where('role', 'karyawan')->paginate(10, ['*'], 'karyawan_page')->withQueryString();
        $kurirs = (clone $baseQuery)->where('role', 'kurir')->paginate(10, ['*'], 'kurir_page')->withQueryString();
        $pelanggans = (clone $baseQuery)->where('role', 'pelanggan')->paginate(10, ['*'], 'pelanggan_page')->withQueryString();

        // Get years available in system for filter dropdown
        if (\DB::connection()->getDriverName() === 'sqlite') {
            $years = User::selectRaw("strftime('%Y', created_at) as year")
                ->whereNotNull('created_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();
        } else {
            $years = User::selectRaw('YEAR(created_at) as year')
                ->whereNotNull('created_at')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->toArray();
        }

        if (empty($years)) {
            $years = [now()->year];
        }

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $totalUsersCount = User::count();
        $activeUsersCount = User::where('status', 'active')->count();
        $suspendedUsersCount = User::where('status', 'inactive')->count();
        $newRegistrationsCount = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        return view('admin.users.index', compact(
            'admins', 'karyawans', 'kurirs', 'pelanggans', 'years', 'months',
            'totalUsersCount', 'activeUsersCount', 'suspendedUsersCount', 'newRegistrationsCount'
        ));
    }

    public function show(User $user)
    {
        if (request()->wantsJson() || request()->ajax()) {
            $user->loadCount(['customerOrders', 'courierOrders', 'attendances']);
            
            // Format registration date
            $user->registered_at = $user->created_at->timezone('Asia/Jakarta')->format('d F Y, H:i');
            
            // Fetch recent orders depending on the role
            $recentOrders = collect();
            if ($user->role === 'pelanggan') {
                $recentOrders = $user->customerOrders()->latest()->take(5)->get();
            } elseif ($user->role === 'kurir') {
                $recentOrders = $user->courierOrders()->latest()->take(5)->get();
            }
                
            $formattedOrders = $recentOrders->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                    'url' => route('admin.orders.show', $order->id)
                ];
            });

            // Fetch recent attendances for roles that record attendance
            $recentAttendances = collect();
            if (in_array($user->role, ['karyawan', 'kurir'])) {
                $recentAttendances = $user->attendances()->latest()->take(5)->get();
            }
            
            $formattedAttendances = $recentAttendances->map(function($att) {
                return [
                    'id' => $att->id,
                    'date' => Carbon::parse($att->date)->format('d M Y'),
                    'check_in' => $att->check_in ? Carbon::parse($att->check_in)->format('H:i') : '--:--',
                    'check_out' => $att->check_out ? Carbon::parse($att->check_out)->format('H:i') : '--:--',
                    'status' => $att->status
                ];
            });

            return response()->json([
                'user' => $user,
                'recent_orders' => $formattedOrders,
                'recent_attendances' => $formattedAttendances
            ]);
        }

        return redirect()->route('admin.users.index', [
            'show_user_id' => $user->id,
            'role' => $user->role
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Simple status-only toggle (AJAX or form)
        if ($request->has('status') && !$request->has('name')) {
            $data = $request->validate([
                'status' => 'required|in:active,inactive',
            ]);
            
            $oldStatus = $user->status;
            $user->update(['status' => $data['status']]);
            
            if ($oldStatus === 'inactive' && $user->status === 'active') {
                try {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserApprovedMail($user));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send approval mail to {$user->email}: " . $e->getMessage());
                }
            }
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "User status updated to " . ($user->status === 'active' ? 'Active' : 'Inactive')
                ]);
            }
            
            return redirect()->route('admin.users.index')->with('success', "User status updated successfully.");
        }

        // Full edit update (NIK is completely removed from forms, but kept nullable in DB)
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,karyawan,kurir,pelanggan',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && \Storage::disk('public')->exists($user->photo)) {
                \Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }

        $oldStatus = $user->status;
        $user->update($data);

        // Send approval email if status changed to active
        if ($oldStatus === 'inactive' && $user->status === 'active') {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserApprovedMail($user));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send approval mail to {$user->email}: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.users.index', ['role' => $user->role])->with('success', "User details updated successfully.");
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,karyawan,kurir,pelanggan',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('users', 'public');
        }

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('admin.users.index', ['role' => $data['role']])->with('success', "User created successfully.");
    }

    public function edit(User $user)
    {
        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $role = $user->role;
        $user->delete();
        return redirect()->route('admin.users.index', ['role' => $role])->with('success', 'User deleted successfully.');
    }

    private function getPeriodLabel(Request $request): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $month = $request->input('month');
        $year = $request->input('year');

        if ($month && $month !== 'all' && $year && $year !== 'all') {
            return $months[$month] . ' ' . $year;
        } elseif ($month && $month !== 'all') {
            return $months[$month];
        } elseif ($year && $year !== 'all') {
            return 'Year ' . $year;
        }

        return 'All Time';
    }

    public function exportPdf(Request $request)
    {
        // Query all roles as requested
        $query = $this->buildBaseQuery($request, true);
        $users = $query->get();
        $periodLabel = $this->getPeriodLabel($request);
        
        $pdf = Pdf::loadView('admin.exports.users_pdf', compact('users', 'periodLabel'))
                  ->setPaper('A4', 'portrait');
        $slugPeriod = strtolower(str_replace(' ', '_', $periodLabel));
        
        return $pdf->download("laundryan_users_report_{$slugPeriod}_" . date('Ymd') . ".pdf");
    }

    public function exportCsv(Request $request)
    {
        // Query all roles as requested
        $query = $this->buildBaseQuery($request, true);
        $users = $query->get();
        $periodLabel = $this->getPeriodLabel($request);
        $slugPeriod = strtolower(str_replace(' ', '_', $periodLabel));

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"laundryan_users_report_{$slugPeriod}_" . date('Ymd') . ".csv\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            
            $roles = [
                'admin' => 'Administrators',
                'karyawan' => 'Staff',
                'kurir' => 'Couriers',
                'pelanggan' => 'Customers'
            ];
            
            $statusMap = [
                'active' => 'Active',
                'inactive' => 'Suspended'
            ];

            foreach ($roles as $roleKey => $roleTitle) {
                $roleUsers = $users->where('role', $roleKey);
                
                if ($roleUsers->count() > 0) {
                    // Separate block formatting for each role
                    fputcsv($file, []);
                    fputcsv($file, ["=== {$roleTitle} ==="]);
                    fputcsv($file, ['No', 'Full Name', 'Email', 'Phone', 'Address', 'Status', 'Registration Date']);
                    
                    foreach ($roleUsers->values() as $i => $user) {
                        fputcsv($file, [
                            $i + 1,
                            $user->name,
                            $user->email,
                            $user->phone ?: '-',
                            $user->address ?: '-',
                            $statusMap[$user->status] ?? ucfirst($user->status),
                            $user->created_at ? $user->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '-',
                        ]);
                    }
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
