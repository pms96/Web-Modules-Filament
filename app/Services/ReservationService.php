<?php

namespace App\Services;

use App\Models\Track;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Get;
use PhpParser\Node\Expr\FuncCall;

class ReservationService
{
    public function getAvailableTimesForDate(int $trackId, string $date): array
    {
        $date                  = Carbon::parse($date);
        $startPeriod           = $date->copy()->hour(14);
        $endPeriod             = $date->copy()->hour(18);
        $times                 = CarbonPeriod::create($startPeriod, '1 hour', $endPeriod);
        $availableReservations = [];

        $tracks = Track::with([
            'reservations' => function ($q) use ($trackId, $startPeriod, $endPeriod) {
                $q->where('track_id', $trackId);
                $q->whereBetween('start_time', [$startPeriod, $endPeriod]);
            },
        ])
        ->get();

        foreach ($tracks as $track) {
            if ( $track->id === $trackId ) {            
                $reservations = $track->reservations->pluck('start_time')->toArray();

                $availableTimes = $times->copy()->filter(function ($time) use ($reservations) {
                    return ! in_array($time, $reservations);
                })->toArray();

                foreach ($availableTimes as $time) {
                    $key                         = $track->id . '-' . $time->format('H');
                    $availableReservations[$key] = $track->title . ' ' . $time->format('H:i') . ' - ' . $track->name;
                }
            }
        }

        return $availableReservations;
    }

    public function getTracksAvailableForDate(string $date): array
    {
        $date                  = Carbon::parse($date);
        $startPeriod           = $date->copy()->hour(14);
        $endPeriod             = $date->copy()->hour(18);
        
        $tabsOfTracks = [];

        $tracks = Track::with([
            'reservations' => function ($q) use ($startPeriod, $endPeriod) {                
                $q->whereBetween('start_time', [$startPeriod, $endPeriod]);
            },
        ])
        ->get();

        foreach ( $tracks as $tab ) {
            $name = $tab->name;
            $slug = str($name)->slug()->toString();
 
            $tabsOfTracks[$slug] = Tab::make($name)
                ->schema([
                    Radio::make('track')
                    ->label('Citas Disponibles')
                    ->options(fn (Get $get) => (new ReservationService())->getAvailableTimesForDate($tab->id,$get('date')))
                    ->hidden(fn (Get $get) => ! $get('date'))
                    ->required()
                    ->columnSpan(2)
                    ->columns(3),
                ]); 
        }

        return $tabsOfTracks;
    }

    public static function disableDatesTimeZone(): array 
    {
        $start = Carbon::now();
        $end = $start->copy()->addMonths(4);
        $period = CarbonPeriod::create($start, $end);

        $weekends = [];
        foreach ($period as $date) {
            if ($date->isWeekend()) {
                $weekends[] = $date->format('Y-m-d');
            }
        }

        return $weekends;
    }
}
