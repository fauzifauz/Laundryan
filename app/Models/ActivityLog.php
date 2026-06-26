<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'email', 'role', 'category', 'activity_type',
        'description', 'module', 'reference_id', 'ip_address', 'device', 'browser',
        'user_agent', 'data_before', 'data_after'
    ];

    protected $casts = [
        'data_before' => 'array',
        'data_after' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActivityTypeAttribute($value)
    {
        $map = [
            'Order Dibuat' => 'Order Created',
            'Order Dibatalkan' => 'Order Cancelled',
            'Order Selesai' => 'Order Completed',
            'Status Order Berubah' => 'Order Status Changed',
            'Kurir Di-Assign' => 'Courier Assigned',
            'Metode Pembayaran Diubah' => 'Payment Method Changed',
            'Pembayaran Berhasil' => 'Payment Successful',
            'Pembayaran Gagal' => 'Payment Failed',
            'Refund Dilakukan' => 'Refund Processed',
            'User Ditambahkan' => 'User Added',
            'Status User Berubah' => 'User Status Changed',
            'Role User Berubah' => 'User Role Changed',
            'User Dihapus' => 'User Deleted',
            'Transaksi Dibuat' => 'Transaction Created',
            'Transaksi Diupdate' => 'Transaction Updated',
            'Transaksi Dihapus' => 'Transaction Deleted',
            'Check In' => 'Check In',
            'Check Out' => 'Check Out',
            'Gaji Diproses' => 'Payroll Processed',
            'Harga Layanan Berubah' => 'Service Price Changed',
            'Harga Tipe Item Berubah' => 'Item Type Price Changed',
            'Pajak Berubah' => 'Tax Rate Changed',
            'Landing Page Diupdate' => 'Landing Page Updated',
            'Login Berhasil' => 'Successful Login',
            'Login Gagal' => 'Failed Login',
            'Login dari Device Baru' => 'Login from New Device',
            'Login via Google' => 'Login via Google',
            'Ganti Password' => 'Password Changed',
            'Logout' => 'Logout',
        ];
        return $map[$value] ?? $value;
    }

    public function getDescriptionAttribute($value)
    {
        if (empty($value)) return $value;

        // Replace status labels inside the string
        $statusMap = [
            'Menunggu Pembayaran' => 'Pending Payment',
            'Menunggu Penjemputan' => 'Waiting for Pickup',
            'Sedang Dijemput' => 'Picking Up',
            'Sudah Dijemput' => 'Picked Up',
            'Dalam Perjalanan ke Laundry' => 'In Transit to Laundry',
            'Tiba di Laundry' => 'Arrived at Laundry',
            'Sedang Dicuci' => 'Washing',
            'Sedang Dikeringkan & Disetrika' => 'Drying & Ironing',
            'Sedang Dikemas' => 'Packing',
            'Siap Dikirim' => 'Ready for Delivery',
            'Sedang Dikirim' => 'Delivering',
            'Selesai' => 'Completed',
            'Dibatalkan' => 'Cancelled',
        ];
        
        $translated = $value;
        foreach ($statusMap as $indo => $eng) {
            $translated = str_replace($indo, $eng, $translated);
        }

        // Patterns translation
        $patterns = [
            '/Gagal login menggunakan email (.*)/i' => 'Failed login attempt using email $1',
            '/User "(.*)" login dari IP (.*)/i' => 'User "$1" logged in from IP $2',
            '/Login terdeteksi dari perangkat baru \((.*)\)/i' => 'Login detected from new device ($1)',
            '/User "(.*)" login menggunakan Google/i' => 'User "$1" logged in via Google',
            '/User "(.*)" mengubah password/i' => 'User "$1" changed password',
            '/User "(.*)" logout/i' => 'User "$1" logged out',
            '/Order #(.*) dibuat oleh (.*)/i' => 'Order #$1 created by $2',
            '/Order #(.*) dibatalkan oleh (.*)/i' => 'Order #$1 cancelled by $2',
            '/Order #(.*) ditandai selesai oleh Kurir (.*)/i' => 'Order #$1 marked as completed by Courier $2',
            '/Order #(.*) diubah ke status "(.*)" oleh (.*)/i' => 'Order #$1 status changed to "$2" by $3',
            '/Kurir "(.*)" ditugaskan ke Order #(.*)/i' => 'Courier "$1" assigned to Order #$2',
            '/Metode pembayaran Order #(.*) diubah ke (.*)/i' => 'Payment method for Order #$1 changed to $2',
            '/Pembayaran Order #(.*) sebesar (.*) berhasil/i' => 'Payment for Order #$1 of $2 was successful',
            '/Pembayaran Order #(.*) gagal/i' => 'Payment for Order #$1 failed',
            '/Refund Order #(.*) sebesar (.*)/i' => 'Refund for Order #$1 of $2 processed',
            '/Pengguna baru "(.*)" dengan role "(.*)" ditambahkan/i' => 'New user "$1" with role "$2" added',
            '/Status pengguna "(.*)" diubah ke aktif/i' => 'Status for user "$1" changed to active',
            '/Status pengguna "(.*)" diubah ke nonaktif/i' => 'Status for user "$1" changed to inactive',
            '/Role pengguna "(.*)" diubah ke (.*)/i' => 'Role for user "$1" changed to $2',
            '/Pengguna "(.*)" telah dihapus dari sistem/i' => 'User "$1" has been deleted from the system',
            '/Transaksi pendapatan baru sebesar (.*) ditambahkan/i' => 'New income transaction of $1 added',
            '/Transaksi pengeluaran baru sebesar (.*) ditambahkan/i' => 'New expense transaction of $1 added',
            '/Transaksi pendapatan diubah/i' => 'Transaction for income updated',
            '/Transaksi pengeluaran diubah/i' => 'Transaction for expense updated',
            '/Transaksi pendapatan sebesar (.*) dihapus/i' => 'Transaction for income of $1 deleted',
            '/Transaksi pengeluaran sebesar (.*) dihapus/i' => 'Transaction for expense of $1 deleted',
            '/(.*) "(.*)" melakukan Check In/i' => '$1 "$2" performed Check In',
            '/(.*) "(.*)" melakukan Check Out/i' => '$1 "$2" performed Check Out',
            '/Gaji untuk karyawan "(.*)" sebesar (.*) telah diproses/i' => 'Payroll for employee "$1" of $2 processed',
            '/Harga layanan "(.*)" diubah dari (.*) ke (.*)/i' => 'Price for service "$1" changed from $2 to $3',
            '/Harga tipe item "(.*)" diubah dari (.*) ke (.*)/i' => 'Price for item type "$1" changed from $2 to $3',
            '/Pajak (.*) diubah dari (.*) ke (.*)/i' => 'Tax $1 changed from $2 to $3',
            '/Konten landing page \((.*) section\) diupdate oleh (.*)/i' => 'Landing page content ($1 section) updated by $2',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $temp = preg_replace($pattern, $replacement, $translated);
            if ($temp !== null && $temp !== $translated) {
                $translated = $temp;
                break;
            }
        }

        return $translated;
    }

    public static function log($category, $activityType, $description, $module = null, $referenceId = null, $dataBefore = null, $dataAfter = null, $user = null)
    {
        $user = $user ?: auth()->user();
        
        $userAgent = Request::header('User-Agent');
        $ipAddress = Request::ip();

        // Extract Browser and Device
        $browser = self::parseBrowser($userAgent);
        $device = self::parseDevice($userAgent);

        return self::create([
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : null,
            'email' => $user ? $user->email : ($user ? $user->email : null),
            'role' => $user ? $user->role : 'sistem',
            'category' => $category,
            'activity_type' => $activityType,
            'description' => $description,
            'module' => $module,
            'reference_id' => $referenceId,
            'ip_address' => $ipAddress,
            'device' => $device,
            'browser' => $browser,
            'user_agent' => $userAgent,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
        ]);
    }

    public static function parseBrowser($ua)
    {
        if (empty($ua)) return 'Unknown';
        if (preg_match('/MSIE/i', $ua) && !preg_match('/Opera/i', $ua)) return 'Internet Explorer';
        if (preg_match('/Firefox/i', $ua)) return 'Firefox';
        if (preg_match('/Chrome/i', $ua) && !preg_match('/Edg/i', $ua)) return 'Chrome';
        if (preg_match('/Safari/i', $ua) && !preg_match('/Chrome/i', $ua)) return 'Safari';
        if (preg_match('/Opera|OPR/i', $ua)) return 'Opera';
        if (preg_match('/Edg/i', $ua)) return 'Edge';
        return 'Browser';
    }

    public static function parseDevice($ua)
    {
        if (empty($ua)) return 'Unknown';
        if (preg_match('/Windows/i', $ua)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS X/i', $ua)) return 'Mac';
        if (preg_match('/iPhone/i', $ua)) return 'iPhone';
        if (preg_match('/iPad/i', $ua)) return 'iPad';
        if (preg_match('/Android/i', $ua)) return 'Android';
        if (preg_match('/Linux/i', $ua)) return 'Linux';
        return 'Device';
    }
}
