<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarmisResource\Pages;
use App\Models\Carmis;
use App\Models\Goods;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarmisResource extends Resource
{
    protected static ?string $model = Carmis::class;

    protected static ?string $recordTitleAttribute = 'carmi';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-key';
    }

    public static function getNavigationLabel(): string
    {
        return '卡密管理';
    }

    public static function getModelLabel(): string
    {
        return '卡密';
    }

    public static function getPluralModelLabel(): string
    {
        return '卡密';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('goods_id')
                    ->label('Product')
                    ->options(
                        Goods::query()
                            ->where('type', Goods::AUTOMATIC_DELIVERY)
                            ->pluck('gd_name', 'id')
                    )
                    ->required()
                    ->searchable(),

                Forms\Components\Radio::make('status')
                    ->label('Status')
                    ->options([
                        Carmis::STATUS_UNSOLD => 'Unsold',
                        Carmis::STATUS_SOLD => 'Sold',
                    ])
                    ->default(Carmis::STATUS_UNSOLD),

                Forms\Components\Toggle::make('is_loop')
                    ->label('Reusable')
                    ->helperText('If enabled, this key can be sold multiple times')
                    ->default(false),

                Forms\Components\Textarea::make('carmi')
                    ->label('Digital Key')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('goods.gd_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Carmis::STATUS_UNSOLD ? 'Unsold' : 'Sold')
                    ->color(fn ($state) => $state == Carmis::STATUS_UNSOLD ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('is_loop')
                    ->label('Reusable')
                    ->boolean(),

                Tables\Columns\TextColumn::make('carmi')
                    ->label('Key')
                    ->limit(30)
                    ->copyable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Carmis::STATUS_UNSOLD => 'Unsold',
                        Carmis::STATUS_SOLD => 'Sold',
                    ]),
                Tables\Filters\SelectFilter::make('goods_id')
                    ->label('Product')
                    ->options(
                        Goods::query()
                            ->where('type', Goods::AUTOMATIC_DELIVERY)
                            ->pluck('gd_name', 'id')
                    )
                    ->searchable(),
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
            'index' => Pages\ListCarmis::route('/'),
            'create' => Pages\CreateCarmis::route('/create'),
            'edit' => Pages\EditCarmis::route('/{record}/edit'),
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
