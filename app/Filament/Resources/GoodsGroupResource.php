<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsGroupResource\Pages;
use App\Models\GoodsGroup;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GoodsGroupResource extends Resource
{
    protected static ?string $model = GoodsGroup::class;

    protected static ?string $recordTitleAttribute = 'gp_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-folder';
    }

    public static function getNavigationLabel(): string
    {
        return '商品分类';
    }

    public static function getModelLabel(): string
    {
        return '分类';
    }

    public static function getPluralModelLabel(): string
    {
        return '分类';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('gp_name')
                    ->label('Category Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_open')
                    ->label('Status')
                    ->default(true),

                Forms\Components\TextInput::make('ord')
                    ->label('Display Order')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gp_name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('ord')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoodsGroups::route('/'),
            'create' => Pages\CreateGoodsGroup::route('/create'),
            'edit' => Pages\EditGoodsGroup::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
