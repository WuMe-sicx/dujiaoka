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
                Section::make('基本信息')
                    ->schema([
                        Forms\Components\TextInput::make('gd_name')
                            ->label('商品名称')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('gd_description')
                            ->label('简短描述')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('gd_keywords')
                            ->label('SEO 关键词')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('group_id')
                            ->label('分类')
                            ->options(GoodsGroup::query()->pluck('gp_name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\FileUpload::make('picture')
                            ->label('商品图片')
                            ->image()
                            ->directory('goods')
                            ->disk('admin'),

                        Forms\Components\Radio::make('type')
                            ->label('配送类型')
                            ->options([
                                Goods::AUTOMATIC_DELIVERY => '自动发货',
                                Goods::MANUAL_PROCESSING => '手动处理',
                            ])
                            ->default(Goods::AUTOMATIC_DELIVERY)
                            ->required(),
                    ])->columns(2)
                    ->columnSpanFull(),

                Section::make('定价')
                    ->schema([
                        Forms\Components\TextInput::make('retail_price')
                            ->label('原价')
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->helperText('显示为划线价'),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('实际价格')
                            ->numeric()
                            ->prefix('¥')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('in_stock')
                            ->label('库存')
                            ->numeric()
                            ->default(0)
                            ->helperText('自动发货商品根据未售卡密自动计算'),

                        Forms\Components\TextInput::make('sales_volume')
                            ->label('销量')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('buy_limit_num')
                            ->label('限购数量')
                            ->numeric()
                            ->default(0)
                            ->helperText('0 = 不限制'),

                        Forms\Components\TextInput::make('ord')
                            ->label('显示顺序')
                            ->numeric()
                            ->default(1),
                    ])->columns(3)
                    ->columnSpanFull(),

                Section::make('内容')
                    ->schema([
                        Forms\Components\RichEditor::make('buy_prompt')
                            ->label('购买提示')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('商品描述')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('高级配置')
                    ->schema([
                        Forms\Components\Textarea::make('wholesale_price_cnf')
                            ->label('批发价格')
                            ->rows(4)
                            ->helperText('格式：数量=价格，每行一个，如：10=8.5'),

                        Forms\Components\Textarea::make('other_ipu_cnf')
                            ->label('自定义输入字段')
                            ->rows(4)
                            ->helperText('订单附加输入字段，每行一个'),

                        Forms\Components\Textarea::make('api_hook')
                            ->label('API 回调')
                            ->rows(4)
                            ->helperText('订单完成时调用的回调地址'),

                        Forms\Components\Toggle::make('is_open')
                            ->label('启用')
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
                    ->label('图片')
                    ->disk('admin')
                    ->size(50),

                Tables\Columns\TextColumn::make('gd_name')
                    ->label('名称')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('group.gp_name')
                    ->label('分类')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Goods::AUTOMATIC_DELIVERY ? '自动' : '手动')
                    ->color(fn ($state) => $state == Goods::AUTOMATIC_DELIVERY ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('价格')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('in_stock')
                    ->label('库存')
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
                    ->label('销量')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('ord')
                    ->label('排序')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('状态'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options([
                        Goods::AUTOMATIC_DELIVERY => '自动发货',
                        Goods::MANUAL_PROCESSING => '手动处理',
                    ]),
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('分类')
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
