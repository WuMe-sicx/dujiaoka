<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoodsResource\Pages;
use App\Models\Carmis;
use App\Models\Goods;
use App\Models\GoodsGroup;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GoodsResource extends Resource
{
    protected static ?string $model = Goods::class;

    protected static ?string $recordTitleAttribute = 'gd_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function getNavigationLabel(): string
    {
        return '商品管理';
    }

    public static function getModelLabel(): string
    {
        return '商品';
    }

    public static function getPluralModelLabel(): string
    {
        return '商品';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('gd_name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('gd_description')
                            ->label('Short Description')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('gd_keywords')
                            ->label('SEO Keywords')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('group_id')
                            ->label('Category')
                            ->options(GoodsGroup::query()->pluck('gp_name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\FileUpload::make('picture')
                            ->label('Product Image')
                            ->image()
                            ->directory('goods')
                            ->disk('admin'),

                        Forms\Components\Radio::make('type')
                            ->label('Delivery Type')
                            ->options([
                                Goods::AUTOMATIC_DELIVERY => 'Automatic Delivery',
                                Goods::MANUAL_PROCESSING => 'Manual Processing',
                            ])
                            ->default(Goods::AUTOMATIC_DELIVERY)
                            ->required(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('retail_price')
                            ->label('Original Price')
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->helperText('Show as strikethrough price'),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('Actual Price')
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('in_stock')
                            ->label('Stock')
                            ->numeric()
                            ->default(0)
                            ->helperText('For auto-delivery products, this is calculated from unsold keys'),

                        Forms\Components\TextInput::make('sales_volume')
                            ->label('Sales Volume')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('buy_limit_num')
                            ->label('Purchase Limit')
                            ->numeric()
                            ->default(0)
                            ->helperText('0 = no limit'),

                        Forms\Components\TextInput::make('ord')
                            ->label('Display Order')
                            ->numeric()
                            ->default(1),
                    ])->columns(3)
                    ->columnSpanFull(),

                Section::make('Content')
                    ->schema([
                        Forms\Components\RichEditor::make('buy_prompt')
                            ->label('Purchase Prompt')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Product Description')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Advanced Configuration')
                    ->schema([
                        Forms\Components\Textarea::make('wholesale_price_cnf')
                            ->label('Wholesale Pricing')
                            ->rows(4)
                            ->helperText('Format: quantity=price, one per line. e.g., 10=8.5'),

                        Forms\Components\Textarea::make('other_ipu_cnf')
                            ->label('Custom Input Fields')
                            ->rows(4)
                            ->helperText('Additional input fields for order, one per line'),

                        Forms\Components\Textarea::make('api_hook')
                            ->label('API Webhook')
                            ->rows(4)
                            ->helperText('Webhook URL to call when order completes'),

                        Forms\Components\Toggle::make('is_open')
                            ->label('Enabled')
                            ->default(true),
                    ])->columns(1)
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

                Tables\Columns\ImageColumn::make('picture')
                    ->label('Image')
                    ->disk('admin')
                    ->size(50),

                Tables\Columns\TextColumn::make('gd_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('group.gp_name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Goods::AUTOMATIC_DELIVERY ? 'Auto' : 'Manual')
                    ->color(fn ($state) => $state == Goods::AUTOMATIC_DELIVERY ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('Price')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('in_stock')
                    ->label('Stock')
                    ->state(function (Goods $record): int {
                        if ($record->type == Goods::AUTOMATIC_DELIVERY) {
                            return Carmis::query()
                                ->where('goods_id', $record->id)
                                ->where('status', Carmis::STATUS_UNSOLD)
                                ->count();
                        }
                        return $record->in_stock ?? 0;
                    }),

                Tables\Columns\TextColumn::make('sales_volume')
                    ->label('Sales')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('ord')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('Status'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        Goods::AUTOMATIC_DELIVERY => 'Automatic Delivery',
                        Goods::MANUAL_PROCESSING => 'Manual Processing',
                    ]),
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Category')
                    ->options(GoodsGroup::query()->pluck('gp_name', 'id'))
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
            'index' => Pages\ListGoods::route('/'),
            'create' => Pages\CreateGoods::route('/create'),
            'edit' => Pages\EditGoods::route('/{record}/edit'),
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
