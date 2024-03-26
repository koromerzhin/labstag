<?php

namespace Labstag\Service;

use Labstag\Queue\EnqueueMethod;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    public function __construct(
        protected Environment $twigEnvironment,
        protected EnqueueMethod $enqueueMethod,
        protected MailerInterface $mailer,
        protected DataService $dataService
    )
    {
    }

    public function createEmail(array $data = []): Email
    {
        $config = $this->dataService->getConfig();
        $email  = new Email();
        if (isset($data['html'])) {
            $html = $this->twigEnvironment->render(
                'mails/base.html.twig',
                [
                    'config'  => $config,
                    'content' => $data['html'],
                ]
            );
            $email->html($html);
        }

        if (isset($data['txt'])) {
            $text = $this->twigEnvironment->render(
                'mails/base.text.twig',
                [
                    'config'  => $config,
                    'content' => $data['txt'],
                ]
            );
            $email->text($text);
        }

        if (isset($config['site_no-reply'])) {
            $email->from($config['site_no-reply']);
        }

        return $email;
    }

    public function send(Email $email): void
    {
        $this->enqueueMethod->async(
            MailerInterface::class,
            'send',
            [$email]
        );
    }

    public function sendNow(Email $email): void
    {
        $this->mailer->send($email);
    }
}
