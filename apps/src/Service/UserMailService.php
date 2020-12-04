<?php

namespace Labstag\Service;

use DateTime;
use Labstag\Entity\AdresseUser;
use Labstag\Entity\EmailUser;
use Labstag\Entity\LienUser;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\PhoneUser;
use Labstag\Entity\Template;
use Labstag\Entity\User;
use Labstag\Repository\TemplateRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Routing\RouterInterface;

class UserMailService
{

    private MailerService $mailerService;

    private TemplateRepository $repository;

    private RouterInterface $router;

    private array $config;

    private DataService $dataService;

    public function __construct(
        RouterInterface $router,
        MailerService $mailerService,
        TemplateRepository $repository,
        DataService $dataService
    )
    {
        $this->dataService   = $dataService;
        $this->repository    = $repository;
        $this->mailerService = $mailerService;
        $this->router        = $router;

        $config       = $dataService->getConfig();
        $this->config = $config;
        Locale::setDefault($config['languagedefault']);
    }

    public function changeValue(
        User $user,
        string $content,
        array $otherchange = []
    ): string
    {
        $time   = new DateTime();
        $change = [
            'username' => $user->getUsername(),
            'datetime' => $time->format('d/m/Y H:i'),
        ];

        $change = array_merge($change, $otherchange);

        foreach ($change as $key => $after) {
            $before  = '%' . $key . '%';
            $content = str_replace($before, $after, $content);
        }

        return $content;
    }

    public function newUser(User $user): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-user']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $url     = $this->config['site_url'];
        $change  = [
            'url_confirm_user' => $url . $this->router->generate(
                'app_confirm_user',
                [
                    'id' => $user->getId(),
                ]
            ),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function changePassword(User $user): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'change-password']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html),
                'txt'  => $this->changeValue($user, $txt),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function lostPassword(User $user): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'lost-password']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $url     = $this->config['site_url'];
        $change  = [
            'url_change_password' => $url . $this->router->generate(
                'app_changepassword',
                [
                    'id' => $user->getId(),
                ]
            ),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function changeEmailPrincipal(User $user): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'change-email-principal']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html),
                'txt'  => $this->changeValue($user, $txt),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function checkNewMail(User $user, EmailUser $emailUser): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-new-mail']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $url     = $this->config['site_url'];
        $change  = [
            'url_confirm_email' => $url . $this->router->generate(
                'app_confirm_mail',
                [
                    'id' => $emailUser->getId(),
                ]
            ),
            'courriel'          => $emailUser->getAdresse(),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function checkNewOauthConnectUser(
        User $user,
        OauthConnectUser $oauthConnectUser
    ): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-new-oauthconnectuser']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $change  = [
            'oauth_name' => $oauthConnectUser->getName(),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function checkNewLink(User $user, LienUser $lienUser): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-new-link']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $change  = [
            'link' => $lienUser->getAdresse(),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function checkNewPhone(User $user, PhoneUser $phoneUser): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-new-phone']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $change  = [
            'tel_number' => $phoneUser->getNumero(),
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }

    public function checkNewAdresse(User $user, AdresseUser $adresseUser): void
    {
        /** @var Template $template */
        $template = $this->repository->findOneBy(
            ['code' => 'check-new-adresse']
        );
        if (!($template instanceof Template)) {
            return;
        }

        $change  = [
            'adresse_rue'     => $adresseUser->getRue(),
            'adresse_zipcode' => $adresseUser->getZipcode(),
            'adresse_ville'   => $adresseUser->getVille(),
            'adresse_country' => Countries::getName($adresseUser->getCountry()),
            'adresse_gps'     => $adresseUser->getGps(),
            'adresse_pmr'     => $adresseUser->isPmr() ? 'Oui' : 'Non',
        ];
        $html    = $template->getHtml();
        $txt     = $template->getText();
        $subject = $template->getName();
        $email   = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $txt, $change),
            ]
        );
        $email->subject($subject);
        $email->to($user->getEmail());
        $this->mailerService->send($email);
    }
}
