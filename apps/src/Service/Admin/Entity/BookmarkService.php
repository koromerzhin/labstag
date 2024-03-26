<?php

namespace Labstag\Service\Admin\Entity;

use DateTime;
use DOMDocument;
use Exception;
use Labstag\Entity\Bookmark;
use Labstag\Entity\User;
use Labstag\Form\Admin\Bookmark\ImportType;
use Labstag\Interfaces\AdminEntityServiceInterface;
use Labstag\Interfaces\DomainInterface;
use Labstag\Queue\EnqueueMethod;
use Labstag\Service\Admin\ViewService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BookmarkService extends ViewService implements AdminEntityServiceInterface
{
    public function getType(): string
    {
        return Bookmark::class;
    }

    public function import(Security $security, EnqueueMethod $enqueueMethod): Response
    {
        $domain = $this->getDomain();
        if (!$domain instanceof DomainInterface) {
            throw new Exception('Domain not found');
        }

        $url = $domain->getUrlAdmin();
        $this->btnService->setBtnList($url);
        $form = $this->createForm(ImportType::class, []);
        $this->btnService->addBtnSave($form->getName(), 'Import');
        /** @var Request $request */
        $request = $this->requeststack->getCurrentRequest();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploadFile($form, $security, $enqueueMethod);
        }

        $templates = $this->getDomain()->getTemplates();
        if (!isset($templates['import'])) {
            throw new Exception('Template move not found');
        }

        return $this->render(
            $templates['import'],
            ['form' => $form]
        );
    }

    private function uploadFile(
        FormInterface $form,
        Security $security,
        EnqueueMethod $enqueueMethod
    ): void
    {
        $file = $form->get('file')->getData();
        if (!$file instanceof UploadedFile) {
            return;
        }

        $domDocument = new DOMDocument();
        $domDocument->loadHTMLFile($file->getPathname(), LIBXML_NOWARNING | LIBXML_NOERROR);

        $domNodeList = $domDocument->getElementsByTagName('a');
        $dateTime    = new DateTime();
        /** @var User $user */
        $user   = $security->getUser();
        $userId = $user->getId();
        foreach ($domNodeList as $tag) {
            $enqueueMethod->async(
                BookmarkService::class,
                'process',
                [
                    'userid' => $userId,
                    'url'    => $tag->getAttribute('href'),
                    'name'   => $tag->nodeValue,
                    'icon'   => $tag->getAttribute('icon'),
                    'date'   => $dateTime->setTimestamp((int) $tag->getAttribute('add_date')),
                ]
            );
        }
    }
}
