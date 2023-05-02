<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TravianAuctionSellingNotification extends Mailable
{
    use Queueable, SerializesModels;

    private string $tableHtml;

    public function __construct(string $tableHtml)
    {
        $this->tableHtml = $tableHtml;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Travian Auction Selling Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(null,
            html: $this->tableHtml
        );
    }
}
