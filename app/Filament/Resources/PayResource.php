<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayResource\Pages;
use App\Models\Pay;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayResource extends Resource
{
    protected static ?string $model = Pay::class;

    protected static ?string $recordTitleAttribute = 'pay_name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getNavigationLabel(): string
    {
        return '支付方式';
    }

    public static function getModelLabel(): string
    {
        return '支付方式';
    }

    public static function getPluralModelLabel(): string
    {
        return '支付方式';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('pay_name')
                    ->label('支付名称')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('pay_check')
                    ->label('支付代码')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('merchant_id')
                    ->label('商户ID')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('merchant_key')
                    ->label('商户密钥')
                    ->rows(3),

                Forms\Components\Textarea::make('merchant_pem')
                    ->label('商户证书')
                    ->required()
                    ->rows(3),

                Forms\Components\Radio::make('pay_client')
                    ->label('客户端类型')
                    ->options([
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => '移动端',
                        Pay::PAY_CLIENT_ALL => '全部',
                    ])
                    ->default(Pay::PAY_CLIENT_PC)
                    ->required(),

                Forms\Components\Radio::make('pay_method')
                    ->label('支付方式')
                    ->options([
                        Pay::METHOD_JUMP => '跳转支付',
                        Pay::METHOD_SCAN => '扫码支付',
                    ])
                    ->default(Pay::METHOD_JUMP)
                    ->required(),

                Forms\Components\TextInput::make('pay_handleroute')
                    ->label('处理路由')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('/pay/alipay'),

                Forms\Components\Toggle::make('is_open')
                    ->label('状态')
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

                Tables\Columns\TextColumn::make('pay_name')
                    ->label('支付名称')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pay_check')
                    ->label('代码')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pay_method')
                    ->label('方式')
                    ->formatStateUsing(fn ($state) => $state == Pay::METHOD_JUMP ? '跳转' : '扫码'),

                Tables\Columns\TextColumn::make('merchant_id')
                    ->label('商户ID')
                    ->limit(20),

                Tables\Columns\TextColumn::make('pay_client')
                    ->label('客户端')
                    ->formatStateUsing(fn ($state) => match($state) {
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => '移动端',
                        Pay::PAY_CLIENT_ALL => '全部',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('pay_handleroute')
                    ->label('路由')
                    ->limit(20),

                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('状态'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('pay_client')
                    ->label('客户端')
                    ->options([
                        Pay::PAY_CLIENT_PC => 'PC',
                        Pay::PAY_CLIENT_MOBILE => '移动端',
                        Pay::PAY_CLIENT_ALL => '全部',
                    ]),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPays::route('/'),
            'create' => Pages\CreatePay::route('/create'),
            'edit' => Pages\EditPay::route('/{record}/edit'),
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
