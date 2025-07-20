<?php
namespace App\Actions\Notification;
use Illuminate\Support\Facades\Mail;

class Testmail
{
  public function execute(): void
  {
    Mail::raw('Frau Müller Testmail.', function ($message) {
      $message->to('m@marceli.to')->subject('Frau Müller Testmail');
    });
  }
}