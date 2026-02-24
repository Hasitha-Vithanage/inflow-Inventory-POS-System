<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $attachments_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $attachments_data = [])
    {
        $this->data = $data;
        $this->attachments_data = $attachments_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->data['subject'])
            ->markdown('emails.custom')
            ->view('emails.custom');

        if (!empty($this->attachments_data)) {
            foreach ($this->attachments_data as $attachment) {
                $mail->attachData($attachment['data'], $attachment['name'], [
                    'mime' => $attachment['mime'] ?? 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
