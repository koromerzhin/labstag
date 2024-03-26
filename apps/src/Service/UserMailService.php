<?php

namespace Labstag\Service;

use DateTime;
use Exception;
use Labstag\Entity\AddressUser;
use Labstag\Entity\EmailUser;
use Labstag\Entity\LinkUser;
use Labstag\Entity\OauthConnectUser;
use Labstag\Entity\PhoneUser;
use Labstag\Entity\Template;
use Labstag\Entity\User;
use Labstag\Repository\TemplateRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Locale;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserMailService
{

    protected array $config;

    public function __construct(
        protected RepositoryService $repositoryService,
        protected RouterInterface $router,
        protected MailerService $mailerService,
        protected DataService $dataService,
        protected TemplateRepository $templateRepository
    )
    {
        $config       = $dataService->getConfig();
        $this->config = $config;

        $code            = 'languagedefault';
        $languagedefault = $config[$code] ?? 'fr';
        if (!is_string($languagedefault)) {
            throw new Exception('Config '.$languagedefault.' is not string');
        }

        Locale::setDefault($languagedefault);
    }

    public function changeEmailPrincipal(User $user): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'change-email-principal']
        );
        if (!$template instanceof Template) {
            return;
        }

        $this->setEmail(
            $template,
            $user
        );
    }

    public function changePassword(User $user): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'change-password']
        );
        if (!$template instanceof Template) {
            return;
        }

        $this->setEmail(
            $template,
            $user
        );
    }

    public function changeValue(
        User $user,
        string $content,
        array $otherchange = []
    ): string
    {
        $dateTime = new DateTime();
        $change   = [
            'username' => $user->getUsername(),
            'datetime' => $dateTime->format('d/m/Y H:i'),
        ];

        $change = [
            ...$change,
            ...$otherchange,
        ];

        foreach ($change as $key => $after) {
            $before  = '%'.$key.'%';
            $content = str_replace($before, $after, $content);
        }

        return $content;
    }

    public function checkNewAddress(UserInterface $user, AddressUser $addressUser): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-new-address']
        );
        if (!$template instanceof Template) {
            return;
        }

        $change = [
            'address_street'  => $addressUser->getStreet(),
            'address_zipcode' => $addressUser->getZipcode(),
            'address_city'    => $addressUser->getCity(),
            'address_country' => Countries::getName((string) $addressUser->getCountry()),
            'address_gps'     => $addressUser->getGps(),
            'address_pmr'     => $addressUser->isPmr() ? 'Oui' : 'Non',
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function checkNewLink(UserInterface $user, LinkUser $linkUser): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-new-link']
        );
        if (!$template instanceof Template) {
            return;
        }

        $change = [
            'link' => $linkUser->getAddress(),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function checkNewMail(User $user, EmailUser $emailUser): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-new-mail']
        );
        if (!$template instanceof Template) {
            return;
        }

        $url    = $this->config['site_url'] ?? '';
        $change = [
            'url_confirm_email' => $url.$this->router->generate(
                'app_confirm_mail',
                [
                    'id' => $emailUser->getId(),
                ]
            ),
            'courriel'          => $emailUser->getAddress(),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function checkNewOauthConnectUser(
        UserInterface $user,
        OauthConnectUser $oauthConnectUser
    ): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-new-oauthconnectuser']
        );
        if (!$template instanceof Template) {
            return;
        }

        $change = [
            'oauth_name' => $oauthConnectUser->getName(),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function checkNewPhone(User $user, PhoneUser $phoneUser): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-new-phone']
        );
        if (!$template instanceof Template) {
            return;
        }

        $url    = $this->config['site_url'] ?? '';
        $change = [
            'url_confirm_phone' => $url.$this->router->generate(
                'app_confirm_phone',
                [
                    'id' => $phoneUser->getId(),
                ]
            ),
            'tel_number'        => $phoneUser->getNumero(),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function lostPassword(User $user): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'lost-password']
        );
        if (!$template instanceof Template) {
            return;
        }

        $url    = $this->config['site_url'] ?? $url = '';
        $change = [
            'url_change_password' => $url.$this->router->generate(
                'app_changepassword',
                [
                    'id' => $user->getId(),
                ]
            ),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    public function newUser(User $user): void
    {
        /** @var Template $template */
        $template = $this->templateRepository->findOneBy(
            ['code' => 'check-user']
        );
        if (!$template instanceof Template) {
            return;
        }

        $url    = $this->config['site_url'] ?? '';
        $change = [
            'url_confirm_user' => $url.$this->router->generate(
                'app_confirm_user',
                [
                    'id' => $user->getId(),
                ]
            ),
        ];
        $this->setEmail(
            $template,
            $user,
            $change
        );
    }

    private function setEmail(
        Template $template,
        UserInterface $user,
        array $change = []
    ): void
    {
        /** @var User $user */
        $html  = (string) $template->getHtml();
        $text  = (string) $template->getText();
        $name  = (string) $template->getName();
        $email = $this->mailerService->createEmail(
            [
                'html' => $this->changeValue($user, $html, $change),
                'txt'  => $this->changeValue($user, $text, $change),
            ]
        );
        $email->subject($name);
        $email->to($user->getEmail());

        $this->mailerService->send($email);
    }
}
