<?php

namespace App\MessageHandler;

use App\Message\SendPrivateMessageEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendPrivateMessageEmailHandler
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(SendPrivateMessageEmail $message)
    {
        $email = (new Email())
            ->from('no-reply@symfoconnect.com')
            ->to($message->getRecipientEmail())
            ->subject('Nouveau message privé')
            ->text(
                sprintf(
                    "Bonjour %s,\n\n%s vous a envoyé un message :\n\n\"%s\"",
                    $message->getRecipientUsername(),
                    $message->getSenderUsername(),
                    $message->getMessageContent()
                )
            );

        $this->mailer->send($email);
    }
}