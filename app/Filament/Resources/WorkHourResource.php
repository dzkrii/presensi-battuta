<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkHourResource\Pages;
use App\Filament\Resources\WorkHourResource\RelationManagers;
use App\Models\User;
use App\Models\WorkHour;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class WorkHourResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Work Hours';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('user_id')
                //     ->relationship('user', 'name')
                //     ->searchable()
                //     ->preload()
                //     ->required(),
                // Forms\Components\TextInput::make('total_hours')
                //     ->required()
                //     ->numeric()
                //     ->default(0),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('user.name')
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('total_hours')
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('work_duration')
                //     ->label('Durasi Kerja')
                //     ->getStateUsing(function ($record) {
                //         // Ambil nilai bulan dan tahun dari request
                //         $month = request()->input('filters.month', date('n')); // default bulan sekarang
                //         $year = request()->input('filters.year', date('Y'));   // default tahun sekarang

                //         return $record->getMonthlyWorkDuration($month, $year);
                //     }),
                // Tables\Columns\TextColumn::make('missing_work_hours')
                //     ->label('Kekurangan Jam Kerja')
                //     ->getStateUsing(function ($record) {
                //         // Ambil filter bulan dan tahun dari query
                //         $month = request('tableFilters')['month'] ?? date('n'); // Default bulan sekarang
                //         $year = request('tableFilters')['year'] ?? date('Y');  // Default tahun sekarang

                //         // Panggil fungsi untuk menghitung kekurangan jam kerja
                //         return $record->getMissingWorkHours($month, $year);
                //     }),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('deleted_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                // ! BARU
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Karyawan')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_work_hours')
                    ->label('Total Jam Kerja')
                    ->getStateUsing(function ($record) {
                        // Tentukan rentang tanggal
                        $startDate = Carbon::create(2024, 12, 1)->startOfDay();
                        $endDate = Carbon::create(2024, 12, 28)->endOfDay();

                        // Ambil data presensi dalam rentang tanggal tersebut
                        $attendances = $record->attendances()
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->whereNotNull('end_time')
                            ->get();

                        $totalHours = 0;
                        $totalMinutes = 0;

                        foreach ($attendances as $attendance) {
                            $checkIn = Carbon::parse($attendance->start_time);
                            $checkOut = Carbon::parse($attendance->end_time);
                            $duration = $checkIn->diff($checkOut);

                            $totalHours += $duration->h;
                            $totalMinutes += $duration->i;
                        }

                        // Konversi menit menjadi jam jika lebih dari 60 menit
                        $totalHours += intdiv($totalMinutes, 60);
                        $totalMinutes = $totalMinutes % 60;

                        return $totalHours . ' jam ' . $totalMinutes . ' menit';
                    }),
                Tables\Columns\TextColumn::make('missing_work_hours')
                    ->label('Kekurangan Jam Kerja')
                    ->getStateUsing(function ($record) {
                        // Tentukan rentang tanggal
                        $startDate = Carbon::create(2024, 12, 1)->startOfDay();
                        $endDate = Carbon::create(2024, 12, 28)->endOfDay();

                        // Ambil total jam kerja yang harus dipenuhi
                        // $totalHours = $record->total_hours; // Pastikan field 'total_hours' ada di tabel 'work_hours'
                        $totalHours = 32; // Pastikan field 'total_hours' ada di tabel 'work_hours'

                        // Hitung total jam kerja yang telah dicatat
                        $attendances = $record->attendances()
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->whereNotNull('end_time')
                            ->get();

                        $workedHours = 0;
                        $workedMinutes = 0;

                        foreach ($attendances as $attendance) {
                            $checkIn = Carbon::parse($attendance->start_time);
                            $checkOut = Carbon::parse($attendance->end_time);
                            $duration = $checkIn->diff($checkOut);

                            $workedHours += $duration->h;
                            $workedMinutes += $duration->i;
                        }

                        // Konversi menit menjadi jam jika lebih dari 60 menit
                        $workedHours += intdiv($workedMinutes, 60);
                        $workedMinutes = $workedMinutes % 60;

                        // Hitung jam kerja yang tidak terpenuhi
                        $totalMinutes = $totalHours * 60; // Total jam kerja yang harus dipenuhi dalam menit
                        $workedMinutesTotal = ($workedHours * 60) + $workedMinutes; // Total jam kerja yang telah dicatat dalam menit

                        $remainingMinutes = $totalMinutes - $workedMinutesTotal;

                        if ($remainingMinutes > 0) {
                            $remainingHours = floor($remainingMinutes / 60);
                            $remainingMinutes = $remainingMinutes % 60;

                            return "{$remainingHours} jam {$remainingMinutes} menit";
                        }

                        return "0 jam 0 menit";
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('month')
                //     ->label('Bulan')
                //     ->options([
                //         '1' => 'Januari',
                //         '2' => 'Februari',
                //         '3' => 'Maret',
                //         '4' => 'April',
                //         '5' => 'Mei',
                //         '6' => 'Juni',
                //         '7' => 'Juli',
                //         '8' => 'Agustus',
                //         '9' => 'September',
                //         '10' => 'Oktober',
                //         '11' => 'November',
                //         '12' => 'Desember',
                //     ])
                //     ->default(date('n')),
                // Tables\Filters\SelectFilter::make('year')
                //     ->label('Tahun')
                //     ->options([
                //         date('Y') => date('Y'),
                //         date('Y') - 1 => date('Y') - 1,
                //     ])
                //     ->default(date('Y')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkHours::route('/'),
            'create' => Pages\CreateWorkHour::route('/create'),
            'edit' => Pages\EditWorkHour::route('/{record}/edit'),
        ];
    }
}
