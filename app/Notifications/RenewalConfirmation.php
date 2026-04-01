<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalConfirmation extends Notification
{
  use Queueable;

  public function __construct($order = null)
  {
    $this->order = $order;
  }

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    $subject = 'Abo-Verlängerung - fraumueller.ch';

    return (new MailMessage)
      ->from(env('MAIL_FROM_ADDRESS'))
      ->replyTo(env('MAIL_FROM_ADDRESS'))
      ->subject($subject)
      ->markdown('mail.order.renewal-confirmation', [
        'order' => $this->order,
      ]);
  }

  public function toArray($notifiable)
  {
    return [];
  }
}
