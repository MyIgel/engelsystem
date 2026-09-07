<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Events\Message as MessageEvent;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;

class Messages
{
    public function __construct(
        protected EngelsystemMailer $mailer
    ) {
    }

    public function created(MessageEvent $event): void
    {
        if (!$event->message->receiver->settings->email_messages) {
            return;
        }

        $this->sendMail(
            $event->message,
            $event->message->receiver,
            'notification.messages.new',
            'emails/messages-new',
        );
    }

    private function sendMail(Message $message, User $user, string $subject, string $template): void
    {
        $this->mailer->sendViewTranslated(
            $user,
            $subject,
            $template,
            [
                'sender'       => $message->sender->displayName,
                'send_message' => $message,
                'username'     => $user->displayName,
            ]
        );
    }
}
