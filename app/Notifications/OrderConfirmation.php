<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmation extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct($data = null, $template = null)
  {
    $this->data = $data;
    $this->template = $template;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable)
  {
    // Get subject from template or use default
    $subject = $this->template['subject'] ?? config('order_confirmation.default_subject', 'Bestellbestätigung fraumueller.ch');
    
    return (new MailMessage)
      ->from(env('MAIL_FROM_ADDRESS'))
      ->bcc(env('MAIL_BCC'))
      ->replyTo(env('MAIL_TO'))
      ->subject($subject)
      ->markdown('mail.order.confirmation', [
        'data' => $this->data,
        'template' => $this->template
      ]);
  }
  
  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
      //
    ];
  }
}
