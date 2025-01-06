<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkHourResource\Pages;
use App\Filament\Resources\WorkHourResource\RelationManagers;
use App\Models\WorkHour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkHourResource extends Resource
{
    protected static ?string $model = WorkHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Work Hours';

    protected static ?string $navigationGroup = 'Attendance Management';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('total_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_hours')
                    ->label('Jam Kerja yg Harus Dipenuhi')
                    ->formatStateUsing(fn($state) => $state . ' jam')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_duration')
                    ->label('Total Jam Kerja Bulan Ini')
                    ->getStateUsing(function ($record) {
                        // Ambil nilai bulan dan tahun dari request
                        $month = request()->input('filters.month', date('n')); // default bulan sekarang
                        $year = request()->input('filters.year', date('Y'));   // default tahun sekarang

                        return $record->getMonthlyWorkDuration($month, $year);
                    }),
                Tables\Columns\TextColumn::make('missing_work_hours')
                    ->label('Kekurangan Jam Kerja')
                    ->getStateUsing(function ($record) {
                        // Ambil filter bulan dan tahun dari query
                        $month = request('tableFilters')['month'] ?? date('n'); // Default bulan sekarang
                        $year = request('tableFilters')['year'] ?? date('Y');  // Default tahun sekarang

                        // Panggil fungsi untuk menghitung kekurangan jam kerja
                        return $record->getMissingWorkHours($month, $year);
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
