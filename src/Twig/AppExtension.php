<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private Security $security,
        private MessageRepository $messageRepository
    ) {
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return [
                'globalUnreadCount' => 0,
            ];
        }

        return [
            'globalUnreadCount' => $this->messageRepository->countUnreadForUser($user),
        ];
    }
}