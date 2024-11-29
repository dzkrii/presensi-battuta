<?php

namespace App\Filament\Resources\WorkHourResource\Pages;

use App\Filament\Resources\WorkHourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkHour extends EditRecord
{
    protected static string $resource = WorkHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
