<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuditNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $type)
    {
        $this->type = $type;

        if ($type == 'results') {
            $this->audit = $data->marks;
            $this->userEmail = $data->agentEmail;
            $this->supervisorEmail = $data->supervisorEmail;
            $this->qualityAnalysts = $data->qualityAnalysts;
        } elseif ($type == 'liveCalls') {
            $this->callId = $data->id;
            $this->userEmail = $data->agentEmail;
            $this->supervisorEmail = $data->supervisorEmail;
            $this->qualityAnalysts = $data->qualityAnalysts;
        } elseif ($type == 'autofails') {
            $this->failId = $data->id;
            $this->userEmail = $data->agentEmail;
            $this->supervisorEmail = $data->supervisorEmail;
            $this->qualityAnalysts = $data->qualityAnalysts;
        } elseif ($type == 'coaching') {
            $this->coachingId = $data->id;
            $this->userEmail = $data->agentEmail;
            $this->supervisorEmail = $data->supervisorEmail;
            $this->qualityAnalysts = $data->qualityAnalysts;
        }elseif ($type == 'shop-walk-in') {
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
    
        if ($this->type == 'results') {
            return $this->markdown('emails.audit_notification')
                ->subject('Monitoring Ticket Raised')
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->with([
                    'audit' => $this->audit,
                    'user' => $userEmail,
                    'supervisor' => $this->supervisorEmail,
                    'qualityAnalysts' => $this->qualityAnalysts,
                    'type' => $this->type, // add this line to pass the $type variable to the view
                ]);
    
    
        } elseif ($this->type == 'liveCalls') {
            return $this->markdown('emails.live_call_notification')
                ->subject('Live Call Monitoring Alert')
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->with([
                    'callId' => $this->callId,
                    'user' => $userEmail,
                    'supervisor' => $this->supervisorEmail,
                    'qualityAnalysts' => $this->qualityAnalysts,
                    'type' => $this->type, // add this line to pass the $type variable to the view
                ]);
        } elseif ($this->type == 'autofails') {
            return $this->markdown('emails.autofail_notification')
                ->subject('Autofail Alert')
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->with([
                    'failId' => $this->failId,
                    'user' => $userEmail,
                    'supervisor' => $this->supervisorEmail,
                    'qualityAnalysts' => $this->qualityAnalysts,
                    'type' => $this->type, // add this line to pass the $type variable to the view
                ]);
        } elseif ($this->type == 'shop-walk-in') {
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
        }elseif ($this->type == 'coaching') {
            return $this->markdown('coaching.coaching_notification')
                ->subject('Coaching Form')
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->with([
                    'coachingId' => $this->coachingId,
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
            subject: 'Monitoring Ticket Raised',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.audit_notification',
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
