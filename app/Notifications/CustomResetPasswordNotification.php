<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = config('envDefault.frontendUrl') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
        return (new MailMessage)
            ->subject('Baseball Record重設密碼通知') // 自定義標題
            ->greeting('您好！')
            ->line('您收到這封信是因為我們收到了您的帳號密碼重設請求。')
            ->line('請在10分鐘內點擊下方按鈕以重設您的密碼，10分鐘後此連結將無法更新密碼。')
            ->action('按此重設密碼', $url) // 自定義按鈕文字
            ->line('如果您沒有要求重設密碼，請忽略此信，無需進行任何操作。')
            ->salutation('祝您有愉快的一天！');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
