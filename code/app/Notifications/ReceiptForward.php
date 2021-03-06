<?php

namespace App\Notifications;

use Auth;

use App\Notifications\ManyMailNotification;
use App\Notifications\MailFormatter;

class ReceiptForward extends ManyMailNotification
{
    use MailFormatter;

    private $temp_file = null;

    public function __construct($temp_file)
    {
        $this->temp_file = $temp_file;
    }

    public function toMail($notifiable)
    {
        $user = Auth::user();
        $message = $this->initMailMessage($notifiable, $user->gas);
        return $this->formatMail($message, 'receipt')->attach($this->temp_file);
    }
}
