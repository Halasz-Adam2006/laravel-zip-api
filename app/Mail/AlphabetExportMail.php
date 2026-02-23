<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlphabetExportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $county,
        public string $letter,
        public array $cities,
        public string $csv
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Alphabet export: {$this->county} ({$this->letter})"
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.alphabet-export'
        );
    }

    public function attachments(): array
    {
        $filename = sprintf(
            'alphabet-%s-%s.csv',
            str_replace(' ', '-', strtolower($this->county)),
            strtolower($this->letter)
        );

        return [
            Attachment::fromData(fn() => $this->csv, $filename)
                ->withMime('text/csv'),
        ];
    }
}
