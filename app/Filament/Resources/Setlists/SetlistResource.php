<?php

namespace App\Filament\Resources\Setlists;

use App\Filament\Resources\Setlists\Pages\SetlistBoardPage;
use App\Filament\Resources\Setlists\Pages\CreateSetlist;
use App\Filament\Resources\Setlists\Pages\EditSetlist;
use App\Filament\Resources\Setlists\Pages\ListSetlists;
use App\Filament\Resources\Setlists\Schemas\SetlistForm;
use App\Filament\Resources\Setlists\Tables\SetlistsTable;
use App\Models\Setlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SetlistResource extends Resource
{
    protected static ?string $model = Setlist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static \UnitEnum|string|null $navigationGroup = 'Repertoire';

    public static function form(Schema $schema): Schema
    {
        return SetlistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SetlistsTable::configure($table);
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
            'index'  => ListSetlists::route('/'),
            'create' => CreateSetlist::route('/create'),
            'edit'   => EditSetlist::route('/{record}/edit'),
            'build'  => SetlistBoardPage::route('/{record}/build'),
        ];
    }
}
