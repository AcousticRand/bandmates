<?php

namespace App\Filament\Resources\Venues\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('capacity')
                    ->numeric()
                    ->minValue(0),
                Textarea::make('address')
                    ->columnSpanFull()
                    ->rows(2),
                Textarea::make('notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }
}
