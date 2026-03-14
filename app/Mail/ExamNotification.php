<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExamNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $type)
    {
        $this->type = $type;

        if ($type == 'exam_results') {
            $this->examId = $data->id;
            $this->userEmail = $$data->agentEmail;
            $this->supervisorEmail = $data->supervisorEmail;
            $this->qualityAnalysts = $data->qualityAnalysts;
        }
    }
    public function build()
    {
    
        $userEmail = $this->userEmail; // define the variable before passing it to the view
    
        //dd($userEmail);
    
        if ($this->type == 'exam_results') {
            return $this->markdown('emails.exam_result_notification')
                ->subject('Exam Result Notification')
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->with([
                    'result' => $this->examResult,
                    'user' => $userEmail,
                    'supervisor' => $this->supervisorEmail,
                    'qualityAnalysts' => $this->qualityAnalysts,
                    'type' => $this->type, // add this line to pass the $type variable to the view
    
                ]);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Exam Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.exam_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
