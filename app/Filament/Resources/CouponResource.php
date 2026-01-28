<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use App\Models\Goods;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $recordTitleAttribute = 'coupon';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    public static function getNavigationLabel(): string
    {
        return '优惠券';
    }

    public static function getModelLabel(): string
    {
        return '优惠券';
    }

    public static function getPluralModelLabel(): string
    {
        return '优惠券';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('goods')
                    ->label('适用商品')
                    ->multiple()
                    ->relationship('goods', 'gd_name')
                    ->preload()
                    ->searchable(),

                Forms\Components\TextInput::make('discount')
                    ->label('折扣金额')
                    ->numeric()
                    ->prefix('¥')
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('coupon')
                    ->label('优惠券代码')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('ret')
                    ->label('剩余使用次数')
                    ->numeric()
                    ->default(1),

                Forms\Components\Radio::make('is_use')
                    ->label('状态')
                    ->options([
                        Coupon::STATUS_UNUSED => '未使用',
                        Coupon::STATUS_USE => '已使用',
                    ])
                    ->default(Coupon::STATUS_UNUSED),

                Forms\Components\Toggle::make('is_open')
                    ->label('启用')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('coupon')
                    ->label('优惠券代码')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('discount')
                    ->label('折扣')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_use')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == Coupon::STATUS_UNUSED ? '未使用' : '已使用')
                    ->color(fn ($state) => $state == Coupon::STATUS_UNUSED ? 'success' : 'danger'),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('启用'),

                Tables\Columns\TextColumn::make('ret')
                    ->label('剩余次数'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('创建时间')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('is_use')
                    ->label('状态')
                    ->options([
                        Coupon::STATUS_UNUSED => '未使用',
                        Coupon::STATUS_USE => '已使用',
                    ]),
                Tables\Filters\SelectFilter::make('goods')
                    ->label('商品')
                    ->relationship('goods', 'gd_name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
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
