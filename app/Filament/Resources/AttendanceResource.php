<?php

namespace App\Filament\Resources;

use App\Filament\Exports\AttendanceExporter;
use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Auth;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Actions;
use Filament\Tables\Filters\SelectFilter;
// use Filament\Tables\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Actions\Action;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TimePicker::make('schedule_start_time')
                                    ->label('Jadwal Masuk')
                                    ->default('07:00:00')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin'))
                                    ->required(),
                                Forms\Components\TimePicker::make('schedule_end_time')
                                    ->label('Jadwal Pulang')
                                    ->default('20:30:00')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin'))
                                    ->required(),
                                Forms\Components\TextInput::make('schedule_latitude')
                                    ->label('Latitude Kantor')
                                    ->default('3.596821772442')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                                Forms\Components\TextInput::make('schedule_longitude')
                                    ->label('Longitude Kantor')
                                    ->default('98.665208816528')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                                Forms\Components\TextInput::make('start_latitude')
                                    ->label('Latitude')
                                    ->default('3.596821772442')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                                Forms\Components\TextInput::make('start_longitude')
                                    ->label('Longitude')
                                    ->default('98.665208816528')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                                Forms\Components\TextInput::make('end_latitude')
                                    ->label('Latitude')
                                    ->default('3.596821772442')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                                Forms\Components\TextInput::make('end_longitude')
                                    ->label('Longitude')
                                    ->default('98.665208816528')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin')),
                            ]),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('Name')
                                    ->hidden(fn() => !Auth::user()->hasRole('super_admin'))
                                    ->required(),
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Waktu Masuk')
                                    ->required(),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Waktu Pulang'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $is_super_admin = Auth::user()->hasRole('super_admin');

                if (!$is_super_admin) {
                    $query->where('user_id', Auth::user()->id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->label('Nama')
                    ->sortable(),

                // Tables\Columns\TextColumn::make('is_late')
                //     ->label('Status')
                //     ->badge()
                //     ->getStateUsing(function ($record) {
                //         return $record->isLate() ? 'Terlambat' : 'Tepat Waktu';
                //     })
                //     ->color(fn(string $state): string => match ($state) {
                //         'Tepat Waktu' => 'success',
                //         'Terlambat' => 'danger',
                //     }),
                Tables\Columns\TextColumn::make('user.position.name')
                    ->searchable()
                    ->label('Position')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Masuk'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Pulang'),
                Tables\Columns\TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
                    ->getStateUsing(function ($record) {
                        return $record->workDuration();
                    })
                    ->visible(fn() => Auth::user()->hasRole('super_admin')),
            ])
            ->defaultSort('start_time', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->default(Carbon::now()->startOfMonth()),
                        DatePicker::make('created_until')
                            ->default(Carbon::now()->endOfMonth()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Dari tanggal ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Sampai tanggal ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('position')
                    ->relationship('user.position', 'name')
                    ->preload()
                    ->searchable()
                    ->label('Position'),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                // ExportAction::make()->exporter(AttendanceExporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()
                // ExportBulkAction::make()->exporter(AttendanceExporter::class)
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
