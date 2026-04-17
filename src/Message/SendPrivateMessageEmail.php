<?php

namespace App\Message;

class SendPrivateMessageEmail
{
    public function __construct(
        private string $recipientEmail,
        private string $recipientUsername,
        private string $senderUsername,
        private string $messageContent
    ) {
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function getRecipientUsername(): string
    {
        return $this->recipientUsername;
    }

    public function getSenderUsername(): string
    {
        return $this->senderUsername;
    }

    public function getMessageContent(): string
    {
        return $this->messageContent;
    }
}