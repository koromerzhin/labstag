<?php

namespace Labstag\Service;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Configuration;
use Labstag\Entity\Groupe;
use Labstag\Entity\Menu;
use Labstag\Entity\Template;
use Labstag\Repository\ConfigurationRepository;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\MenuRepository;
use Labstag\Repository\TemplateRepository;
use Labstag\RequestHandler\ConfigurationRequestHandler;
use Labstag\RequestHandler\GroupeRequestHandler;
use Labstag\RequestHandler\MenuRequestHandler;
use Labstag\RequestHandler\TemplateRequestHandler;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

class InstallService
{

    protected MenuRequestHandler $menuRH;

    protected GroupeRequestHandler $groupeRH;

    protected ConfigurationRequestHandler $configurationRH;

    protected TemplateRequestHandler $templateRH;

    protected GroupeRepository $groupeRepo;

    protected ConfigurationRepository $configurationRepo;

    protected TemplateRepository $templateRepo;

    protected Environment $twig;

    protected MenuRepository $menuRepo;

    protected OauthService $oauthService;

    protected EntityManagerInterface $entityManager;

    protected CacheInterface $cache;

    public function __construct(
        MenuRequestHandler $menuRH,
        GroupeRequestHandler $groupeRH,
        GroupeRepository $groupeRepo,
        OauthService $oauthService,
        ConfigurationRequestHandler $configurationRH,
        ConfigurationRepository $configurationRepo,
        MenuRepository $menuRepo,
        TemplateRequestHandler $templateRH,
        TemplateRepository $templateRepo,
        EntityManagerInterface $entityManager,
        Environment $twig,
        CacheInterface $cache
    )
    {
        $this->cache             = $cache;
        $this->oauthService      = $oauthService;
        $this->menuRepo          = $menuRepo;
        $this->twig              = $twig;
        $this->templateRepo      = $templateRepo;
        $this->configurationRepo = $configurationRepo;
        $this->groupeRepo        = $groupeRepo;
        $this->menuRH            = $menuRH;
        $this->groupeRH          = $groupeRH;
        $this->entityManager     = $entityManager;
        $this->configurationRH   = $configurationRH;
        $this->templateRH        = $templateRH;
    }

    public function getData($file)
    {
        $file = __DIR__.'/../../json/'.$file.'.json';
        $data = [];
        if (is_file($file)) {
            $data = json_decode(file_get_contents($file), true);
        }

        return $data;
    }

    public function getEnv()
    {
        $file   = __DIR__.'/../../.env';
        $data   = [];
        $dotenv = new Dotenv();
        if (is_file($file)) {
            $data = $dotenv->parse(file_get_contents($file));
        }

        ksort($data);

        return $data;
    }

    public function menuadmin()
    {
        $childs = $this->getData('menuadmin');
        $this->saveMenu('admin', $childs);
    }

    public function menuadminprofil()
    {
        $childs = $this->getData('menuadminprofil');
        $this->saveMenu('admin-profil', $childs);
    }

    protected function saveMenu(string $key, array $childs): void
    {
        // $this->entityManager->getFilters()->disable('softdeleteable');
        $search = ['clef' => $key];
        $menu   = $this->menuRepo->findOneBy($search);
        if ($menu instanceof Menu) {
            $this->entityManager->remove($menu);
            $this->entityManager->flush();
        }

        $menu = new Menu();
        $menu->setPosition(0);
        $menu->setClef($key);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();
        $indexChild = 0;
        foreach ($childs as $attr) {
            $this->addChild($indexChild, $menu, $attr);
            ++$indexChild;
        }
    }

    protected function addChild(int $index, Menu $menu, array $attr): void
    {
        $child = new Menu();
        $child->setPosition($index);
        $child->setParent($menu);
        if (isset($attr['separator'])) {
            $child->setSeparateur(true);
            $this->entityManager->persist($child);
            $this->entityManager->flush();

            return;
        }

        $child->setLibelle($attr['libelle']);
        if (isset($attr['data'])) {
            $child->setData($attr['data']);
        }

        $this->entityManager->persist($child);
        $this->entityManager->flush();
        if (isset($attr['childs'])) {
            $indexChild = 0;
            foreach ($attr['childs'] as $attrChild) {
                $this->addChild($indexChild, $child, $attrChild);
                ++$indexChild;
            }
        }
    }

    public function group()
    {
        $groupes = $this->getData('group');
        foreach ($groupes as $row) {
            $this->addGroupe($row);
        }
    }

    protected function addGroupe(
        string $row
    ): void
    {
        $search = ['code' => $row];
        $groupe = $this->groupeRepo->findOneBy($search);
        if ($groupe instanceof Groupe) {
            return;
        }

        $groupe = new Groupe();
        $old    = clone $groupe;
        $groupe->setCode($row);
        $groupe->setName($row);
        $this->groupeRH->handle($old, $groupe);
    }

    public function config()
    {
        $config = $this->getData('config');
        $env    = $this->getEnv();
        $this->setOauth($env, $config);
        foreach ($config as $key => $row) {
            $this->addConfig($key, $row);
        }

        $this->cache->delete('configuration');
    }

    protected function setOauth(array $env, array &$data): void
    {
        $oauth = [];
        foreach ($env as $key => $val) {
            if (0 != substr_count($key, 'OAUTH_')) {
                $code    = str_replace('OAUTH_', '', $key);
                $code    = strtolower($code);
                $explode = explode('_', $code);
                $type    = $explode[0];
                $key     = $explode[1];
                if (!isset($oauth[$type])) {
                    $activate = $this->oauthService->getActivedProvider($type);

                    $oauth[$type] = [
                        'activate' => $activate,
                        'type'     => $type,
                    ];
                }

                $oauth[$type][$key] = $val;
            }
        }

        /** @var mixed $row */
        foreach ($oauth as $row) {
            $data['oauth'][] = $row;
        }
    }

    protected function addConfig(
        string $key,
        $value
    ): void
    {
        $search        = ['name' => $key];
        $configuration = $this->configurationRepo->findOneBy($search);
        if (!$configuration instanceof Configuration) {
            $configuration = new Configuration();
        }

        $old = clone $configuration;
        $configuration->setName($key);
        $configuration->setValue($value);
        $this->configurationRH->handle($old, $configuration);
    }

    public function templates()
    {
        $templates = $this->getData('template');
        foreach ($templates as $key => $row) {
            $this->addTemplate($key, $row);
        }
    }

    protected function addTemplate(
        string $key,
        string $value
    ): void
    {
        $search   = ['code' => $key];
        $template = $this->templateRepo->findOneBy($search);
        if ($template instanceof Template) {
            return;
        }

        $template = new Template();
        $old      = clone $template;
        $template->setName($value);
        $template->setCode($key);
        $htmlfile = 'tpl/mail-'.$key.'.html.twig';
        if (is_file('templates/'.$htmlfile)) {
            $template->setHtml($this->twig->render($htmlfile));
        }

        $txtfile = 'tpl/mail-'.$key.'.txt.twig';
        if (is_file('templates/'.$txtfile)) {
            $template->setText($this->twig->render($txtfile));
        }

        $this->templateRH->handle($old, $template);
    }
}
