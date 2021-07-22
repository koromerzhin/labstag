<?php

namespace Labstag\Service;

use Labstag\Queue\EnqueueMethod;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{

    protected DataService $dataService;

    protected EnqueueMethod $enqueue;

    protected MailerInterface $mailer;

    protected Environment $twig;

    public function __construct(
        Environment $twig,
        EnqueueMethod $enqueue,
        MailerInterface $mailer,
        DataService $dataService
    )
    {
        $this->dataService = $dataService;
        $this->twig        = $twig;
        $this->enqueue     = $enqueue;
        $this->mailer      = $mailer;
    }

    public function createEmail(array $data = []): Email
    {
        $config = $this->dataService->getConfig();
        $email  = new Email();
        if (isset($data['html'])) {
            $html = $this->twig->render(
                'mails/base.html.twig',
                [
                    'config'  => $config,
                    'content' => $data['html'],
                ]
            );
            $email->html($html);
        }

        if (isset($data['txt'])) {
            $text = $this->twig->render(
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
        $this->enqueue->enqueue(
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
