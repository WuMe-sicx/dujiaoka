<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Coupon;
use App\Models\Goods;
use App\Models\Order;
use App\Models\Pay;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $recordTitleAttribute = 'order_sn';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-shopping-cart';
    }

    public static function getNavigationLabel(): string
    {
        return '订单管理';
    }

    public static function getModelLabel(): string
    {
        return '订单';
    }

    public static function getPluralModelLabel(): string
    {
        return '订单';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('订单信息')
                    ->schema([
                        Forms\Components\TextInput::make('order_sn')
                            ->label('订单号')
                            ->disabled(),

                        Forms\Components\TextInput::make('title')
                            ->label('标题'),

                        Forms\Components\Placeholder::make('goods_name')
                            ->label('商品')
                            ->content(fn ($record) => $record?->goods?->gd_name ?? '-'),

                        Forms\Components\Placeholder::make('pay_name')
                            ->label('支付方式')
                            ->content(fn ($record) => $record?->pay?->pay_name ?? '-'),

                        Forms\Components\TextInput::make('email')
                            ->label('邮箱')
                            ->disabled(),

                        Forms\Components\TextInput::make('buy_ip')
                            ->label('IP 地址')
                            ->disabled(),
                    ])->columns(2),

                Section::make('定价')
                    ->schema([
                        Forms\Components\TextInput::make('goods_price')
                            ->label('单价')
                            ->disabled(),

                        Forms\Components\TextInput::make('buy_amount')
                            ->label('数量')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_price')
                            ->label('总计')
                            ->disabled(),

                        Forms\Components\Placeholder::make('coupon_name')
                            ->label('优惠券')
                            ->content(fn ($record) => $record?->coupon?->coupon ?? '-'),

                        Forms\Components\TextInput::make('coupon_discount_price')
                            ->label('优惠券折扣')
                            ->disabled(),

                        Forms\Components\TextInput::make('wholesale_discount_price')
                            ->label('批发折扣')
                            ->disabled(),

                        Forms\Components\TextInput::make('actual_price')
                            ->label('实际价格')
                            ->disabled(),
                    ])->columns(3),

                Section::make('状态与配送')
                    ->schema([
                        Forms\Components\Radio::make('status')
                            ->label('订单状态')
                            ->options([
                                Order::STATUS_WAIT_PAY => '待支付',
                                Order::STATUS_PENDING => '待处理',
                                Order::STATUS_PROCESSING => '处理中',
                                Order::STATUS_COMPLETED => '已完成',
                                Order::STATUS_FAILURE => '失败',
                                Order::STATUS_ABNORMAL => '异常',
                                Order::STATUS_EXPIRED => '已过期',
                            ]),

                        Forms\Components\Radio::make('type')
                            ->label('配送类型')
                            ->options([
                                Order::AUTOMATIC_DELIVERY => '自动',
                                Order::MANUAL_PROCESSING => '手动',
                            ]),

                        Forms\Components\Textarea::make('info')
                            ->label('配送信息')
                            ->rows(6)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('search_pwd')
                            ->label('查询密码'),

                        Forms\Components\TextInput::make('trade_no')
                            ->label('交易号')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_sn')
                    ->label('订单号')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('标题')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('type')
                    ->label('类型')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Order::AUTOMATIC_DELIVERY ? '自动' : '手动')
                    ->color(fn ($state) => $state == Order::AUTOMATIC_DELIVERY ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('goods.gd_name')
                    ->label('商品')
                    ->limit(15),

                Tables\Columns\TextColumn::make('actual_price')
                    ->label('价格')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('buy_amount')
                    ->label('数量'),

                Tables\Columns\TextColumn::make('pay.pay_name')
                    ->label('支付方式')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('trade_no')
                    ->label('交易号')
                    ->copyable()
                    ->limit(15)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        Order::STATUS_WAIT_PAY => '待支付',
                        Order::STATUS_PENDING => '待处理',
                        Order::STATUS_PROCESSING => '处理中',
                        Order::STATUS_COMPLETED => '已完成',
                        Order::STATUS_FAILURE => '失败',
                        Order::STATUS_ABNORMAL => '异常',
                        Order::STATUS_EXPIRED => '已过期',
                        default => '未知',
                    })
                    ->color(fn ($state) => match($state) {
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_WAIT_PAY => 'warning',
                        Order::STATUS_PENDING, Order::STATUS_PROCESSING => 'info',
                        Order::STATUS_FAILURE, Order::STATUS_ABNORMAL => 'danger',
                        Order::STATUS_EXPIRED => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('状态')
                    ->options([
                        Order::STATUS_WAIT_PAY => '待支付',
                        Order::STATUS_PENDING => '待处理',
                        Order::STATUS_PROCESSING => '处理中',
                        Order::STATUS_COMPLETED => '已完成',
                        Order::STATUS_FAILURE => '失败',
                        Order::STATUS_ABNORMAL => '异常',
                        Order::STATUS_EXPIRED => '已过期',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('类型')
                    ->options([
                        Order::AUTOMATIC_DELIVERY => '自动',
                        Order::MANUAL_PROCESSING => '手动',
                    ]),
                Tables\Filters\SelectFilter::make('goods_id')
                    ->label('商品')
                    ->options(Goods::query()->pluck('gd_name', 'id'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('pay_id')
                    ->label('支付方式')
                    ->options(Pay::query()->pluck('pay_name', 'id')),
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
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
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
