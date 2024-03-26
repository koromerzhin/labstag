<?php

namespace Labstag\Block;

use Exception;
use Labstag\Entity\Block\Custom;
use Labstag\Entity\Layout;
use Labstag\Form\Admin\Block\CustomType;
use Labstag\Interfaces\BlockInterface;
use Labstag\Interfaces\EntityBlockInterface;
use Labstag\Interfaces\EntityFrontInterface;
use Labstag\Lib\BlockLib;
use Labstag\Repository\LayoutRepository;
use Symfony\Component\HttpFoundation\Request;

class CustomBlock extends BlockLib implements BlockInterface
{
    public function context(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): mixed
    {
        unset($entityFront);
        if (!$entityBlock instanceof Custom) {
            return null;
        }

        $paragraphs = $this->setParagraphs($entityBlock);
        $redirect   = $this->setRedirect($paragraphs);

        if (!is_null($redirect)) {
            return $redirect;
        }

        $paragraphs = $this->launchParagraphs($paragraphs);

        return [
            'paragraphs' => $paragraphs,
            'block'      => $entityBlock,
        ];
    }

    public function getCode(EntityBlockInterface $entityBlock, ?EntityFrontInterface $entityFront): string
    {
        unset($entityBlock, $entityFront);

        return 'custom';
    }

    public function getEntity(): string
    {
        return Custom::class;
    }

    public function getForm(): string
    {
        return CustomType::class;
    }

    public function getName(): string
    {
        return $this->translator->trans('custom.name', [], 'block');
    }

    public function getType(): string
    {
        return 'custom';
    }

    public function isShowForm(): bool
    {
        return false;
    }

    private function setParagraphs(Custom $custom): array
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $all     = $request->attributes->all();
        $route   = $all['_route'];
        /** @var LayoutRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Layout::class);
        $dataLayouts   = $repositoryLib->findByCustom($custom);
        $layouts       = [];
        if (!is_iterable($dataLayouts)) {
            throw new Exception('Layouts invalide');
        }

        foreach ($dataLayouts as $layout) {
            /** @var Layout $layout */
            $urls = $layout->getUrl();
            if (!is_iterable($urls) || !in_array($route, $urls)) {
                continue;
            }

            $layouts[] = $layout;
        }

        $paragraphs = [];
        foreach ($layouts as $layout) {
            $paragraphs = $this->getParagraphsArray(
                $this->paragraphService,
                $layout,
                $paragraphs
            );
        }

        return $paragraphs;
    }
}
