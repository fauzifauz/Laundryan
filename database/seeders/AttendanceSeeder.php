<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure a good collection of staff and couriers exist
        $staffEmails = [
            'karyawan@laundryan.com' => 'Karyawan Dummy',
            'karyawan2@laundryan.com' => 'Budi Santoso',
            'karyawan3@laundryan.com' => 'Siti Aminah',
            'karyawan4@laundryan.com' => 'Dewi Lestari',
            'karyawan5@laundryan.com' => 'Eko Prasetyo',
        ];

        $courierEmails = [
            'kurir@laundryan.com' => 'Kurir Dummy',
            'kurir.pickup@laundryan.com' => 'Andi Kurir',
            'kurir.dijemput@laundryan.com' => 'Bowo Express',
            'kurir.diantar@laundryan.com' => 'Candra Logistik',
            'kurir5@laundryan.com' => 'Dedi Delivery',
        ];

        $users = [];

        foreach ($staffEmails as $email => $name) {
            $users[] = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => '0812' . rand(10000000, 99999999),
                    'role' => 'karyawan',
                    'status' => 'active',
                    'password' => bcrypt('password'),
                ]
            );
        }

        foreach ($courierEmails as $email => $name) {
            $users[] = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => '0813' . rand(10000000, 99999999),
                    'role' => 'kurir',
                    'status' => 'active',
                    'password' => bcrypt('password'),
                ]
            );
        }

        // 2. Generate records for the last 30 days
        $locations = [
            ['name' => 'Laundryan HQ Main Gate', 'lat' => -6.1664983, 'lng' => 106.5602886],
            ['name' => 'Laundryan Branch South', 'lat' => -6.175000, 'lng' => 106.575000],
            ['name' => 'Tangerang Center Point', 'lat' => -6.182000, 'lng' => 106.590000],
            ['name' => 'Sudirman Business Area', 'lat' => -6.211400, 'lng' => 106.822400],
        ];

        $statuses = ['present', 'present', 'present', 'present', 'late', 'absent', 'permit', 'leave'];
        $approvalStatuses = ['approved', 'rejected', 'pending'];

        // Seed entries for each of the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $currentDate = Carbon::now()->subDays($i);
            
            // Skip Sundays
            if ($currentDate->isSunday()) {
                continue;
            }

            foreach ($users as $user) {
                // Randomly select attendance status
                $status = $statuses[array_rand($statuses)];
                
                $loc = $locations[array_rand($locations)];
                
                $checkIn = null;
                $checkOut = null;
                $photoPath = null;
                $documentPath = null;
                $approvalStatus = null;
                $rejectReason = null;

                if ($status === 'present') {
                    // On time: Check-in between 07:00 and 08:00
                    $checkIn = sprintf('%02d:%02d:%02d', 7, rand(0, 59), rand(0, 59));
                    // Check-out between 17:00 and 18:00
                    $checkOut = sprintf('%02d:%02d:%02d', 17, rand(0, 59), rand(0, 59));
                } elseif ($status === 'late') {
                    // Late: Check-in between 08:01 and 09:30
                    $checkIn = sprintf('%02d:%02d:%02d', 8, rand(1, 59), rand(0, 59));
                    $checkOut = sprintf('%02d:%02d:%02d', 17, rand(0, 30), rand(0, 59));
                } elseif ($status === 'permit' || $status === 'leave') {
                    $approvalStatus = $approvalStatuses[array_rand($approvalStatuses)];
                    if ($status === 'permit') {
                        $documentPath = 'documents/permit_' . $user->id . '_' . $currentDate->format('Ymd') . '.pdf';
                    } else {
                        $documentPath = 'documents/leave_' . $user->id . '_' . $currentDate->format('Ymd') . '.pdf';
                    }
                    if ($approvalStatus === 'rejected') {
                        $rejectReason = 'Reason provided for permit/leave request is insufficient or invalid.';
                    }
                }

                // Create the record
                Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $currentDate->toDateString(),
                    ],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                        'latitude' => ($status === 'present' || $status === 'late') ? $loc['lat'] : null,
                        'longitude' => ($status === 'present' || $status === 'late') ? $loc['lng'] : null,
                        'location_name' => ($status === 'present' || $status === 'late') ? $loc['name'] : null,
                        'approval_status' => $approvalStatus,
                        'reject_reason' => $rejectReason,
                        'document_path' => $documentPath,
                        'photo_path' => ($status === 'present' || $status === 'late') ? 'photos/attendance_placeholder.jpg' : null,
                    ]
                );
            }
        }

        echo "Attendance dummy data seeded successfully!\n";
    }
}
