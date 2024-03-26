<?php

namespace Labstag\Service;

use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Interfaces\FrontInterface;
use Labstag\Repository\AttachmentRepository;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class FrontService
{
    /**
     * @var string
     */
    final public const ADMIN_CONTROLLER = '/(Controller\\\Admin)/';

    /**
     * @var string[]
     */
    final public const ERROR_CONTROLLER = [
        'error_controller',
        'error_controller::preview',
    ];

    protected $rewindableGenerator;

    public function __construct(
        #[TaggedIterator('frontclass')]
        iterable $rewindableGenerator,
        protected Environment $twigEnvironment,
        protected RequestStack $requestStack,
        protected UrlGeneratorInterface $urlGenerator,
        protected AttachmentRepository $attachmentRepository
    )
    {
        $this->rewindableGenerator = $rewindableGenerator;
    }

    public function configMeta(
        array $config,
        array $meta
    ): array
    {
        $functions = [
            'configMetaImage',
            'configMetaRobots',
            'configMetaTitle',
            'configMetaLocale',
            'configMetaDescription',
        ];

        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            $meta = call_user_func_array($callable, [$config, $meta]);
            if (!is_array($meta)) {
                $meta = [];
            }
        }

        return $meta;
    }

    public function setBreadcrumb(?EntityFrontInterface $entityFront): array
    {
        $breadcrumb = [];
        foreach ($this->rewindableGenerator as $row) {
            /** @var FrontInterface $row */
            $breadcrumb = $row->setBreadcrumb($entityFront, $breadcrumb);
        }

        return array_reverse($breadcrumb);
    }

    public function setMeta(?EntityFrontInterface $entityFront): array
    {
        $meta = [];
        foreach ($this->rewindableGenerator as $row) {
            /** @var FrontInterface $row */
            $meta = $row->setMeta($entityFront, $meta);
        }

        foreach ($meta as $key => $value) {
            if (is_null($value)) {
                unset($meta[$key]);
            }
        }

        return $meta;
    }

    public function setMetatags(array $meta): void
    {
        $metatags             = [];
        $meta['twitter:card'] = 'summary_large_image';
        $meta['og:type']      = 'website';
        ksort($meta);
        foreach ($meta as $key => $value) {
            if ('' == $value || is_null($value) || 'title' == $key) {
                continue;
            }

            if (0 != substr_count((string) $key, 'og:')) {
                $metatags[] = [
                    'property' => $key,
                    'content'  => $value,
                ];

                continue;
            }

            $metatags[] = [
                'name'    => $key,
                'content' => $value,
            ];
        }

        $this->twigEnvironment->AddGlobal('sitemetatags', $metatags);
    }

    protected function configMetaDescription(array $config, array $meta): array
    {
        unset($config);
        $tests = [
            'og:description',
            'twitter:description',
        ];
        if (!array_key_exists('description', $meta) || $this->arrayKeyExists($tests, $meta)) {
            return $meta;
        }

        $meta['og:description']      = $meta['description'];
        $meta['twitter:description'] = $meta['description'];

        return $meta;
    }

    protected function configMetaImage(array $config, array $meta): array
    {
        unset($config);
        $attachment = $this->attachmentRepository->getImageDefault();
        $this->twigEnvironment->AddGlobal('imageglobal', $attachment);
        if (!isset($meta['image']) || is_null($attachment) || is_null($attachment->getName())) {
            return $meta;
        }

        $image = $meta['image'] ?? $attachment->getName();

        $pathPackage           = new PathPackage('/', new EmptyVersionStrategy());
        $url                   = $pathPackage->getUrl($image);
        $meta['og:image']      = $url;
        $meta['twitter:image'] = $url;
        if (isset($meta['image'])) {
            unset($meta['image']);
        }

        return $meta;
    }

    protected function configMetaLocale(
        array $config,
        array $meta
    ): array
    {
        if (!$this->isStateMeta()) {
            return $meta;
        }

        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $url     = $request->getSchemeAndHttpHost();
        $all     = $request->attributes->all();
        if (isset($all['_route']) && '' != $all['_route']) {
            $url = $this->urlGenerator->generate(
                $all['_route'],
                $all['_route_params'],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $meta['og:locale']   = $config['languagedefault'];
        $meta['og:url']      = $url;
        $meta['twitter:url'] = $url;

        return $meta;
    }

    protected function configMetaRobots(array $config, array $meta): array
    {
        unset($config);
        if (!$this->isStateMeta()) {
            $meta['robots'] = 'noindex';
        }

        return $meta;
    }

    protected function configMetaTitle(
        array $config,
        array $meta
    ): array
    {
        if (!array_key_exists('site_title', $config)) {
            return $meta;
        }

        $sitetitle = $config['site_title'];

        $title = isset($meta['title']) ? $meta['title'].' - '.$sitetitle : $sitetitle;

        if (!isset($meta['title'])) {
            $meta['title'] = $title;
        }

        $meta['og:title']      = $title;
        $meta['twitter:title'] = $title;

        return $meta;
    }

    private function arrayKeyExists(
        array $var,
        array $data
    ): bool
    {
        $find = 0;
        foreach ($var as $name) {
            $find = (int) array_key_exists($name, $data);
        }

        return 0 != $find;
    }

    private function isStateMeta(): bool
    {
        /** @var Request $request */
        $request    = $this->requestStack->getCurrentRequest();
        $controller = $request->attributes->get('_controller');
        if (!is_string($controller)) {
            return false;
        }

        preg_match(self::ADMIN_CONTROLLER, $controller, $matches);

        return 0 == count($matches) || !in_array($controller, self::ERROR_CONTROLLER);
    }
}
