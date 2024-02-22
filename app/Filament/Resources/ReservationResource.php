<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\Pages\CreateReservation;
use App\Filament\Resources\ReservationResource\Pages\EditReservation;
use App\Filament\Resources\ReservationResource\Pages\ListReservations;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $modelLabel = 'Reservas';

    protected static ?string $navigationIcon = 'heroicon-o-bookmark';

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $navigationGroup = 'Booking';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $dateFormat = 'Y-m-d';

        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Paciente')
                    ->searchable()
                    ->required()
                    ->options(User::all()->pluck('name', 'id')),
                DatePicker::make('date')
                    ->label('Fecha')
                    ->native(false)
                    ->minDate(now()->format($dateFormat))
                    ->maxDate(now()->addMonths(4)->format($dateFormat))
                    ->format($dateFormat)
                    ->disabledDates(ReservationService::disableDatesTimeZone())
                    ->required()
                    ->live(),
                Tabs::make('Tabs')
                    ->tabs(fn (Get $get) => (new ReservationService())->getTracksAvailableForDate($get('date') ?? date('Y-m-d')))                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Cliente'),
                TextColumn::make('track.name')->label('Lugar'),
                TextColumn::make('start_time')->dateTime('Y-m-d H:i'),
                TextColumn::make('end_time')->dateTime('Y-m-d H:i'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('start_time', 'desc');
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
            'index'  => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit'   => EditReservation::route('/{record}/edit'),
        ];
    }
}
