<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\Facades\Mail;

class EmailTest extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.email-test';

    public ?array $data = [];

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-paper-airplane';
    }

    public static function getNavigationLabel(): string
    {
        return '邮件测试';
    }

    public function getTitle(): string
    {
        return '邮件测试';
    }

    public static function getNavigationGroup(): ?string
    {
        return '系统配置';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public function mount(): void
    {
        $this->form->fill([
            'title' => '这是一条测试邮件',
            'body' => '这是一条测试邮件的正文内容<br/><br/>测试测试测试',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('邮件测试')
                    ->description('使用系统设置中的 SMTP 配置发送测试邮件')
                    ->schema([
                        Forms\Components\TextInput::make('to')
                            ->label('收件人邮箱')
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('主题')
                            ->required(),

                        Forms\Components\RichEditor::make('body')
                            ->label('邮件正文')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $sysConfig = cache('system-setting');

        $mailConfig = [
            'driver' => $sysConfig['driver'] ?? 'smtp',
            'host' => $sysConfig['host'] ?? '',
            'port' => $sysConfig['port'] ?? '465',
            'username' => $sysConfig['username'] ?? '',
            'from' => [
                'address' => $sysConfig['from_address'] ?? '',
                'name' => $sysConfig['from_name'] ?? '独角发卡',
            ],
            'password' => $sysConfig['password'] ?? '',
            'encryption' => $sysConfig['encryption'] ?? 'ssl',
        ];

        config(['mail' => array_merge(config('mail'), $mailConfig)]);
        (new MailServiceProvider(app()))->register();

        try {
            Mail::send(['html' => 'email.mail'], ['body' => $data['body']], function ($message) use ($data) {
                $message->to($data['to'])->subject($data['title']);
            });

            Notification::make()
                ->title('邮件发送成功！')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('邮件发送失败')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
