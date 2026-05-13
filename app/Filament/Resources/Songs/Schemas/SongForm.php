<?php

namespace App\Filament\Resources\Songs\Schemas;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SongForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Info')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('artist'),
                        TextInput::make('album'),
                        TextInput::make('genre'),
                        TextInput::make('release_year')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue(2100),
                    ]),

                Section::make('Performance')
                    ->columns(3)
                    ->schema([
                        TextInput::make('tempo')
                            ->placeholder('e.g. 120 BPM'),
                        TextInput::make('key')
                            ->placeholder('e.g. G, Bb, F#m'),
                        TextInput::make('runtime')
                            ->placeholder('e.g. 3:45')
                            ->hint('mm:ss')
                            ->rules(['nullable', 'regex:/^\d{1,2}:[0-5]\d$/']),
                        Toggle::make('has_track')
                            ->label('Has Track?'),
                        Toggle::make('is_acoustic')
                            ->label('Is Acoustic?')
                            ->columnStart(3),
                        Textarea::make('arrangement')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Lyrics & Notes')
                    ->schema([
                        Textarea::make('lyrics')
                            ->rows(12)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
