<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationLabel(): string
    {
        return '用户管理';
    }

    public static function getModelLabel(): string
    {
        return '用户';
    }

    public static function getPluralModelLabel(): string
    {
        return '用户';
    }

    public static function getNavigationGroup(): ?string
    {
        return '用户中心';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('用户名')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('编辑时留空则不修改密码'),

                Forms\Components\TextInput::make('balance')
                    ->label('余额')
                    ->numeric()
                    ->prefix('¥')
                    ->default(0)
                    ->disabled(),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('邮箱验证时间'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('用户名')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('balance')
                    ->label('余额')
                    ->money('CNY')
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('已验证')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->label('已验证邮箱')
                    ->query(fn ($query) => $query->whereNotNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('adjust_balance')
                    ->label('调整余额')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->form([
                        Forms\Components\Radio::make('type')
                            ->label('操作类型')
                            ->options([
                                'add' => '增加余额',
                                'deduct' => '扣减余额',
                            ])
                            ->default('add')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('金额')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\TextInput::make('remark')
                            ->label('备注')
                            ->maxLength(255),
                    ])
                    ->action(function (User $record, array $data): void {
                        try {
                            $balanceService = app(\App\Service\BalanceService::class);

                            if ($data['type'] === 'add') {
                                $balanceService->addBalance(
                                    $record,
                                    $data['amount'],
                                    \App\Models\TransactionLog::TYPE_ADJUSTMENT,
                                    null,
                                    $data['remark'] ?? '管理员调整'
                                );
                            } else {
                                $balanceService->deductBalance(
                                    $record,
                                    $data['amount'],
                                    \App\Models\TransactionLog::TYPE_ADJUSTMENT,
                                    null,
                                    $data['remark'] ?? '管理员调整'
                                );
                            }

                            Notification::make()
                                ->title('余额调整成功')
                                ->success()
                                ->send();
                        } catch (\App\Exceptions\InsufficientBalanceException $e) {
                            Notification::make()
                                ->title('操作失败')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('操作失败')
                                ->body('系统错误，请稍后重试')
                                ->danger()
                                ->send();
                            \Illuminate\Support\Facades\Log::error('余额调整失败', [
                                'user_id' => $record->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
