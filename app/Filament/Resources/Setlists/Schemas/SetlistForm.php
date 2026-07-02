<?php

namespace App\Filament\Resources\Setlists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SetlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('number_of_sets')
                    ->label('Number of Sets')
                    ->options([1 => '1 Set', 2 => '2 Sets', 3 => '3 Sets'])
                    ->required()
                    ->default(1),
                TextInput::make('show_length_minutes')
                    ->label('Show Length (minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(480)
                    ->placeholder('e.g. 90')
                    ->hint('Total show time including breaks'),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
