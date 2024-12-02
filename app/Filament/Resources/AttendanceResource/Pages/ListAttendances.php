<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(route('presensi'))
                ->visible(function () {
                    //* ambil user yang sedang login
                    if (auth()->user()->hasRole('super_admin')) {
                        return true;
                    }

                    $user = auth()->user();
                    if (!$user) {
                        return false; // Tidak ada user yang login
                    }

                    //* Ambil jadwal untuk hari ini
                    $today = Carbon::now()->locale('id')->dayName;
                    $currentTime = Carbon::now();

                    $schedule = Schedule::where('user_id', $user->id)
                        ->where('day', $today)
                        ->with('shift')
                        ->first();

                    if (!$schedule || !$schedule->shift) {
                        Notification::make()
                            ->title('Tidak dapat melakukan presensi')
                            ->body('Anda tidak dapat melakukan presensi diluar jadwal yang telah ditentukan.')
                            ->warning()
                            ->send();
                        return false; // Tidak ada jadwal untuk hari ini
                    }

                    // Cek apakah saat ini sudah 20 menit sebelum waktu `start_time`
                    $startTime = Carbon::createFromTimeString($schedule->shift->start_time);
                    $diffInMinutes = $currentTime->diffInMinutes($startTime, false);
                    if ($diffInMinutes > 20) {
                        Notification::make()
                            ->title('Tidak dapat melakukan presensi')
                            ->body('Anda dapat melakukan presensi paling cepat 20 menit sebelum jadwal kerja.')
                            ->warning()
                            ->send();
                        return false; // Tombol tidak muncul
                    }

                    return true;
                }),
        ];
    }
}
