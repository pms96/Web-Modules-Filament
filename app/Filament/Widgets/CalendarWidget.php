<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Actions\DeleteAction;
use Saade\FilamentFullCalendar\Actions\EditAction;
use Saade\FilamentFullCalendar\Actions\ViewAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
 
class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Reservation::class;
 
    public function fetchEvents(array $fetchInfo): array
    {
        return Reservation::where('start_time', '>=', $fetchInfo['start'])
            ->where('end_time', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Reservation $task) {
                return [
                    'id'            => $task->id,                    
                    'title'         => $task->title,                                                            
                    'start'         => $task->start_time,
                    'end'           => $task->end_time,
                ];
            })
            ->toArray();
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
            ->mountUsing(
                function (Form $form, array $arguments) {
                    $form->fill([
                        'start_time' => $arguments['start'] ?? null,
                        'end_time' => $arguments['end'] ?? null
                    ]);
                }
            ),
        ];
    }
 
    protected function modalActions(): array
    {
        return [
            EditAction::make()
            ->mountUsing(
                function (Reservation $record, Form $form, array $arguments) {
                    $form->fill([
                        'title' => $record->title,
                        'description' => $record->description,
                        'start_time' => $arguments['event']['start'] ?? $record->start_time,
                        'end_time' => $arguments['event']['end'] ?? $record->end_time
                    ]);
                }
            ),
            DeleteAction::make(),
        ];
    }

    protected function viewAction(): Action
    {
        return ViewAction::make();
    }
 
    public function getFormSchema(): array
    {
        return [
            TextInput::make('title'),
            TextInput::make('description'),
 
            Grid::make()
                ->schema([
                    DateTimePicker::make('start_time'),
 
                    DateTimePicker::make('end_time'),
                ]),
        ];
    }
 
    public static function canView(): bool
    {
        return false;
    }
}
