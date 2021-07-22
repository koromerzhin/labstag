<?php

namespace Labstag\Lib;

use bheller\ImagesGenerator\ImagesGeneratorProvider;
use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Labstag\Entity\AdresseUser;
use Labstag\Entity\Attachment;
use Labstag\Entity\EmailUser;
use Labstag\Entity\Groupe;
use Labstag\Entity\LienUser;
use Labstag\Entity\NoteInterne;
use Labstag\Entity\PhoneUser;
use Labstag\Entity\User;
use Labstag\Reader\UploadAnnotationReader;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\AdresseUserRequestHandler;
use Labstag\RequestHandler\AttachmentRequestHandler;
use Labstag\RequestHandler\EditoRequestHandler;
use Labstag\RequestHandler\EmailUserRequestHandler;
use Labstag\RequestHandler\GroupeRequestHandler;
use Labstag\RequestHandler\LibelleRequestHandler;
use Labstag\RequestHandler\LienUserRequestHandler;
use Labstag\RequestHandler\NoteInterneRequestHandler;
use Labstag\RequestHandler\PhoneUserRequestHandler;
use Labstag\RequestHandler\TemplateRequestHandler;
use Labstag\RequestHandler\UserRequestHandler;
use Labstag\Service\GuardService;
use Labstag\Service\InstallService;
use Labstag\Service\OauthService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

abstract class FixtureLib extends Fixture
{
    protected const NUMBER_ADRESSE = 25;

    protected const NUMBER_EDITO = 25;

    protected const NUMBER_EMAIL = 25;

    protected const NUMBER_LIBELLE = 10;

    protected const NUMBER_LIEN = 25;

    protected const NUMBER_NOTEINTERNE = 25;

    protected const NUMBER_PHONE = 25;

    protected const NUMBER_POST = 100;

    protected const NUMBER_TEMPLATES = 10;

    protected AdresseUserRequestHandler $adresseUserRH;

    protected AttachmentRequestHandler $attachmentRH;

    protected CacheInterface $cache;

    protected ContainerBagInterface $containerBag;

    protected EditoRequestHandler $editoRH;

    protected EmailUserRequestHandler $emailUserRH;

    protected GroupeRepository $groupeRepository;

    protected GroupeRequestHandler $groupeRH;

    protected GuardService $guardService;

    protected InstallService $installService;

    protected LibelleRequestHandler $libelleRH;

    protected LienUserRequestHandler $lienUserRH;

    protected LoggerInterface $logger;

    protected NoteInterneRequestHandler $noteInterneRH;

    protected OauthService $oauthService;

    protected PhoneUserRequestHandler $phoneUserRH;

    protected TemplateRequestHandler $templateRH;

    protected Environment $twig;

    protected UploadAnnotationReader $uploadAnnotReader;

    protected UserRepository $userRepository;

    protected UserRequestHandler $userRH;

    public function __construct(
        LoggerInterface $logger,
        ContainerBagInterface $containerBag,
        UploadAnnotationReader $uploadAnnotReader,
        InstallService $installService,
        OauthService $oauthService,
        UserRepository $userRepository,
        GroupeRepository $groupeRepository,
        GuardService $guardService,
        Environment $twig,
        EmailUserRequestHandler $emailUserRH,
        LienUserRequestHandler $lienUserRH,
        NoteInterneRequestHandler $noteInterneRH,
        GroupeRequestHandler $groupeRH,
        EditoRequestHandler $editoRH,
        UserRequestHandler $userRH,
        PhoneUserRequestHandler $phoneUserRH,
        AttachmentRequestHandler $attachmentRH,
        AdresseUserRequestHandler $adresseUserRH,
        TemplateRequestHandler $templateRH,
        LibelleRequestHandler $libelleRH,
        CacheInterface $cache
    )
    {
        $this->attachmentRH      = $attachmentRH;
        $this->logger            = $logger;
        $this->containerBag      = $containerBag;
        $this->libelleRH         = $libelleRH;
        $this->uploadAnnotReader = $uploadAnnotReader;
        $this->installService    = $installService;
        $this->cache             = $cache;
        $this->guardService      = $guardService;
        $this->twig              = $twig;
        $this->userRepository    = $userRepository;
        $this->oauthService      = $oauthService;
        $this->groupeRepository  = $groupeRepository;
        $this->templateRH        = $templateRH;
        $this->adresseUserRH     = $adresseUserRH;
        $this->phoneUserRH       = $phoneUserRH;
        $this->userRH            = $userRH;
        $this->editoRH           = $editoRH;
        $this->groupeRH          = $groupeRH;
        $this->noteInterneRH     = $noteInterneRH;
        $this->lienUserRH        = $lienUserRH;
        $this->emailUserRH       = $emailUserRH;
    }

    protected function addAdresse(
        Generator $faker,
        User $user
    ): void
    {
        $adresse = new AdresseUser();
        $old     = clone $adresse;
        $adresse->setRefuser($user);
        $adresse->setRue($faker->streetAddress);
        $adresse->setVille($faker->city);
        $adresse->setCountry($faker->countryCode);
        $adresse->setZipcode($faker->postcode);
        $adresse->setType($faker->unique()->colorName);
        $latitude  = $faker->latitude;
        $longitude = $faker->longitude;
        $gps       = $latitude.','.$longitude;
        $adresse->setGps($gps);
        $adresse->setPmr((bool) rand(0, 1));
        $this->adresseUserRH->handle($old, $adresse);
    }

    protected function addEmail(
        Generator $faker,
        User $user
    ): void
    {
        $email = new EmailUser();
        $old   = clone $email;
        $email->setRefuser($user);
        $email->setAdresse($faker->safeEmail);
        $this->emailUserRH->handle($old, $email);
    }

    protected function addGroupe(
        int $key,
        string $row
    ): void
    {
        $groupe = new Groupe();
        $old    = clone $groupe;
        $groupe->setCode($row);
        $groupe->setName($row);
        $this->addReference('groupe_'.$key, $groupe);
        $this->groupeRH->handle($old, $groupe);
    }

    protected function addLink(
        Generator $faker,
        User $user
    ): void
    {
        $lien = new LienUser();
        $old  = clone $lien;
        $lien->setRefUser($user);
        $lien->setName($faker->word());
        $lien->setAdresse($faker->url);
        $this->lienUserRH->handle($old, $lien);
    }

    protected function addNoteInterne(
        array $users,
        Generator $faker,
        int $index,
        DateTime $maxDate,
        array $states
    ): void
    {
        $noteinterne = new NoteInterne();
        $old         = clone $noteinterne;
        $random      = $faker->numberBetween(5, 50);
        $noteinterne->setTitle($faker->unique()->text($random));
        $dateDebut = $faker->dateTime($maxDate);
        $noteinterne->setDateDebut($dateDebut);
        $dateFin = clone $dateDebut;
        $dateFin->modify('+'.$faker->numberBetween(10, 50).' days');
        $dateFin->modify('+'.$faker->numberBetween(2, 24).' hours');
        $noteinterne->setDateFin($dateFin);
        /** @var string $content */
        $content = $faker->paragraphs(4, true);
        $noteinterne->setContent(str_replace("\n\n", '<br />', $content));
        $this->addReference('noteinterne_'.$index, $noteinterne);
        $tabIndex = array_rand($users);
        /** @var User $user */
        $user = $users[$tabIndex];
        $noteinterne->setRefuser($user);
        $this->upload($noteinterne, $faker);
        $this->noteInterneRH->handle($old, $noteinterne);
        $this->noteInterneRH->changeWorkflowState($noteinterne, $states);
    }

    protected function addPhone(
        Generator $faker,
        User $user,
        array $states
    ): void
    {
        $number = $faker->e164PhoneNumber;
        $phone  = new PhoneUser();
        $old    = clone $phone;
        $phone->setRefuser($user);
        $phone->setNumero($number);
        $phone->setType($faker->word());
        $phone->setCountry($faker->countryCode);
        $this->phoneUserRH->handle($old, $phone);
        $this->phoneUserRH->changeWorkflowState($phone, $states);
    }

    protected function addUser(
        array $groupes,
        int $index,
        array $dataUser,
        Generator $faker
    ): void
    {
        $user = new User();
        $old  = clone $user;

        $user->setRefgroupe($this->getRefgroupe($groupes, $dataUser['groupe']));
        $user->setUsername($dataUser['username']);
        $user->setPlainPassword($dataUser['password']);
        $user->setEmail($dataUser['email']);
        $this->upload($user, $faker);
        $this->addReference('user_'.$index, $user);
        $this->userRH->handle($old, $user);
        $this->userRH->changeWorkflowState($user, $dataUser['state']);
    }

    protected function getParameter(string $name)
    {
        return $this->containerBag->get($name);
    }

    protected function getRefgroupe(array $groupes, string $code): ?Groupe
    {
        foreach ($groupes as $groupe) {
            if ($groupe->getCode() == $code) {
                return $groupe;
            }
        }

        return null;
    }

    protected function setFaker()
    {
        $faker = Factory::create('fr_FR');
        $faker->addProvider(new ImagesGeneratorProvider($faker));

        return $faker;
    }

    protected function upload($entity, Generator $faker)
    {
        if (!$this->uploadAnnotReader->isUploadable($entity)) {
            return;
        }

        /** @var resource $finfo */
        $finfo       = finfo_open(FILEINFO_MIME_TYPE);
        $annotations = $this->uploadAnnotReader->getUploadableFields($entity);
        $slugify     = new Slugify();
        foreach ($annotations as $annotation) {
            $path       = $this->getParameter('file_directory').'/'.$annotation->getPath();
            $accessor   = PropertyAccess::createPropertyAccessor();
            $title      = $accessor->getValue($entity, $annotation->getSlug());
            $slug       = $slugify->slugify($title);
            $attachment = new Attachment();
            $old        = clone $attachment;
            try {
                $image   = $faker->imageGenerator(
                    null,
                    1920,
                    1920,
                    'jpg',
                    true,
                    $faker->word,
                    $faker->hexColor,
                    $faker->hexColor
                );
                $content = file_get_contents($image);
                /** @var resource $tmpfile */
                $tmpfile = tmpfile();
                $data    = stream_get_meta_data($tmpfile);
                file_put_contents($data['uri'], $content);
                $file     = new UploadedFile(
                    $data['uri'],
                    $slug.'.jpg',
                    (string) finfo_file($finfo, $data['uri']),
                    null,
                    true
                );
                $filename = $file->getClientOriginalName();
                $file->move(
                    $path,
                    $filename
                );
                $file = $path.'/'.$filename;
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());
                echo $exception->getMessage();
            }

            $file = $path.'/'.$filename;
            $attachment->setMimeType(mime_content_type($file));
            $attachment->setSize(filesize($file));
            $size = getimagesize($file);
            $attachment->setDimensions(is_array($size) ? $size : []);
            $attachment->setName(
                str_replace(
                    $this->getParameter('kernel.project_dir').'/public/',
                    '',
                    $file
                )
            );
            $this->attachmentRH->handle($old, $attachment);
            $accessor->setValue($entity, $annotation->getFilename(), $attachment);
        }
    }
}
