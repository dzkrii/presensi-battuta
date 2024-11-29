<?php

namespace App\Filament\Resources\WorkHourResource\Pages;

use App\Filament\Resources\WorkHourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkHours extends ListRecords
{
    protected static string $resource = WorkHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
