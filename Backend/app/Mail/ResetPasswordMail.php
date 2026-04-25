<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $email,
        public string $nombre,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer contraseña — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim(env('APP_FRONTEND_URL', config('app.url', 'http://localhost:9000')), '/');
        $url = $frontendUrl
            . '/#/recuperar-password'
            . '?token=' . urlencode($this->token)
            . '&email='  . urlencode($this->email);

        return new Content(
            view: 'emails.reset-password',
            with: [
                'nombre'  => $this->nombre,
                'resetUrl'=> $url,
                'token'   => $this->token,
                'expira'  => 60,
            ],
        );
    }
}
