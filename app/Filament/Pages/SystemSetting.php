<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.system-setting';

    public ?array $data = [];

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationLabel(): string
    {
        return '系统设置';
    }

    public function getTitle(): string
    {
        return '系统设置';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public function mount(): void
    {
        $settings = Cache::get('system-setting', []);
        $this->form->fill($settings ?: []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('设置')
                    ->tabs([
                        Tab::make('基本设置')
                            ->schema([
                                Section::make('站点信息')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('站点标题')
                                            ->required(),
                                        Forms\Components\FileUpload::make('img_logo')
                                            ->label('Logo 图片')
                                            ->image()
                                            ->disk('admin'),
                                        Forms\Components\TextInput::make('text_logo')
                                            ->label('文字Logo'),
                                        Forms\Components\TextInput::make('keywords')
                                            ->label('SEO 关键词'),
                                        Forms\Components\Textarea::make('description')
                                            ->label('描述')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make('显示与安全')
                                    ->schema([
                                        Forms\Components\Select::make('template')
                                            ->label('模板')
                                            ->options(config('dujiaoka.templates', []))
                                            ->required(),
                                        Forms\Components\Select::make('language')
                                            ->label('语言')
                                            ->options(config('dujiaoka.language', []))
                                            ->required(),
                                        Forms\Components\TextInput::make('manage_email')
                                            ->label('管理员邮箱'),
                                        Forms\Components\TextInput::make('order_expire_time')
                                            ->label('订单过期时间（分钟）')
                                            ->numeric()
                                            ->default(5)
                                            ->required(),
                                        Forms\Components\Toggle::make('is_open_anti_red')
                                            ->label('防红模式'),
                                        Forms\Components\Toggle::make('is_open_img_code')
                                            ->label('图片验证码'),
                                        Forms\Components\Toggle::make('is_open_search_pwd')
                                            ->label('查询密码'),
                                        Forms\Components\Toggle::make('is_open_google_translate')
                                            ->label('谷歌翻译'),
                                    ])->columns(2),

                                Section::make('公告与页脚')
                                    ->schema([
                                        Forms\Components\RichEditor::make('notice')
                                            ->label('公告')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('footer')
                                            ->label('页脚')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('交易风控')
                                    ->schema([
                                        Forms\Components\TextInput::make('max_pending_orders')
                                            ->label('未支付订单限制')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('同一IP最多允许的未支付订单数，0为不限制'),
                                        Forms\Components\TextInput::make('default_stock_threshold')
                                            ->label('默认库存预警阈值')
                                            ->numeric()
                                            ->default(10)
                                            ->helperText('库存低于此值时触发预警通知'),
                                    ])->columns(2),
                            ]),

                        Tab::make('订单推送设置')
                            ->schema([
                                Section::make('Server酱')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_server_jiang')
                                            ->label('Server酱'),
                                        Forms\Components\TextInput::make('server_jiang_token')
                                            ->label('Server酱 Token'),
                                    ])->columns(2),

                                Section::make('Telegram')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_telegram_push')
                                            ->label('Telegram 推送'),
                                        Forms\Components\TextInput::make('telegram_bot_token')
                                            ->label('Telegram 机器人Token'),
                                        Forms\Components\TextInput::make('telegram_userid')
                                            ->label('Telegram 用户ID'),
                                    ])->columns(2),

                                Section::make('Bark')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_bark_push')
                                            ->label('Bark 推送'),
                                        Forms\Components\Toggle::make('is_open_bark_push_url')
                                            ->label('Bark 自定义URL'),
                                        Forms\Components\TextInput::make('bark_server')
                                            ->label('Bark 服务器'),
                                        Forms\Components\TextInput::make('bark_token')
                                            ->label('Bark 令牌'),
                                    ])->columns(2),

                                Section::make('企业微信')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_open_qywxbot_push')
                                            ->label('企业微信机器人推送'),
                                        Forms\Components\TextInput::make('qywxbot_key')
                                            ->label('企业微信机器人密钥'),
                                    ])->columns(2),
                            ]),

                        Tab::make('邮件设置')
                            ->schema([
                                Section::make('SMTP配置')
                                    ->schema([
                                        Forms\Components\TextInput::make('driver')
                                            ->label('邮件驱动')
                                            ->default('smtp')
                                            ->required(),
                                        Forms\Components\TextInput::make('host')
                                            ->label('SMTP 主机'),
                                        Forms\Components\TextInput::make('port')
                                            ->label('SMTP 端口')
                                            ->default('587'),
                                        Forms\Components\TextInput::make('username')
                                            ->label('用户名'),
                                        Forms\Components\TextInput::make('password')
                                            ->label('密码')
                                            ->password(),
                                        Forms\Components\TextInput::make('encryption')
                                            ->label('加密方式')
                                            ->placeholder('tls'),
                                        Forms\Components\TextInput::make('from_address')
                                            ->label('发件人地址'),
                                        Forms\Components\TextInput::make('from_name')
                                            ->label('发件人名称'),
                                    ])->columns(2),
                            ]),

                        Tab::make('GeeTest')
                            ->schema([
                                Section::make('GeeTest 验证')
                                    ->schema([
                                        Forms\Components\TextInput::make('geetest_id')
                                            ->label('GeeTest ID'),
                                        Forms\Components\TextInput::make('geetest_key')
                                            ->label('GeeTest 密钥'),
                                        Forms\Components\Toggle::make('is_open_geetest')
                                            ->label('启用 GeeTest'),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Cache::put('system-setting', $data);

        Notification::make()
            ->title('设置保存成功')
            ->success()
            ->send();
    }
}
