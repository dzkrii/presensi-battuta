<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMonthlyWorkDuration($month, $year)
    {
        // Mendapatkan karyawan (user) terkait dari WorkHour
        $user = $this->user;

        // Mendapatkan awal dan akhir bulan
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Ambil data presensi karyawan dalam rentang waktu tersebut
        $attendances = $user->attendances()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotNull('end_time')
            ->get();

        // Inisialisasi total jam dan menit
        $totalHours = 0;
        $totalMinutes = 0;

        foreach ($attendances as $attendance) {
            $checkIn = Carbon::parse($attendance->start_time);
            $checkOut = Carbon::parse($attendance->end_time);

            // Hitung durasi setiap presensi
            $duration = $checkIn->diff($checkOut);

            $totalHours += $duration->h;
            $totalMinutes += $duration->i;
        }

        // Konversi menit menjadi jam jika lebih dari 60 menit
        $totalHours += intdiv($totalMinutes, 60);
        $totalMinutes = $totalMinutes % 60;

        return $totalHours . ' jam ' . $totalMinutes . ' menit';
    }

    public function getMissingWorkHours($month = null, $year = null)
    {
        // Ambil total jam kerja yang harus dipenuhi
        $totalHours = $this->total_hours; // Pastikan field 'total_hours' ada di tabel 'work_hours'

        // Ambil durasi kerja yang sudah dipenuhi menggunakan fungsi getMonthlyWorkDuration
        $workedDuration = $this->getMonthlyWorkDuration($month, $year);

        // Pisahkan jam dan menit dari $workedDuration, misalnya '5 jam 30 menit'
        preg_match('/(\d+) jam (\d+) menit/', $workedDuration, $matches);

        $workedHours = isset($matches[1]) ? (int)$matches[1] : 0;
        $workedMinutes = isset($matches[2]) ? (int)$matches[2] : 0;

        // Total jam kerja yang sudah dipenuhi dalam menit
        $workedMinutesTotal = ($workedHours * 60) + $workedMinutes;

        // Total jam kerja yang harus dipenuhi dalam menit (asumsikan $totalHours adalah dalam jam)
        $totalMinutes = $totalHours * 60;

        // Hitung kekurangan jam dalam menit
        $remainingMinutes = $totalMinutes - $workedMinutesTotal;

        // Jika kekurangan lebih dari 0, kembalikan dalam format jam dan menit
        if ($remainingMinutes > 0) {
            $remainingHours = floor($remainingMinutes / 60);
            $remainingMinutes = $remainingMinutes % 60;

            return "{$remainingHours} jam {$remainingMinutes} menit";
        }

        // Jika sudah memenuhi target jam kerja, kembalikan 0
        return "0 jam 0 menit";
    }
}
