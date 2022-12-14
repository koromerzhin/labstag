<?php

namespace Labstag\Domain\User;

use Labstag\Entity\User;
use Labstag\Form\Admin\Search\UserType as SearchUserType;
use Labstag\Form\Admin\User\UserType;
use Labstag\Lib\DomainLib;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\UserRequestHandler;
use Labstag\Search\UserSearch;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDomain extends DomainLib
{
    public function __construct(
        protected UserRequestHandler $userRequestHandler,
        protected UserRepository $userRepository,
        TranslatorInterface $translator
    )
    {
        parent::__construct($translator);
    }

    public function getEntity()
    {
        return User::class;
    }

    public function getRepository()
    {
        return $this->userRepository;
    }

    public function getRequestHandler()
    {
        return $this->userRequestHandler;
    }

    public function getSearchData()
    {
        return new UserSearch();
    }

    public function getSearchForm()
    {
        return SearchUserType::class;
    }

    /**
     * @return mixed[]
     */
    public function getTitles(): array
    {
        return [
            'admin_user_index'   => $this->translator->trans('user.title', [], 'admin.breadcrumb'),
            'admin_user_edit'    => $this->translator->trans('user.edit', [], 'admin.breadcrumb'),
            'admin_user_guard'   => $this->translator->trans('user.guard', [], 'admin.breadcrumb'),
            'admin_user_new'     => $this->translator->trans('user.new', [], 'admin.breadcrumb'),
            'admin_user_trash'   => $this->translator->trans('user.trash', [], 'admin.breadcrumb'),
            'admin_user_preview' => $this->translator->trans('user.preview', [], 'admin.breadcrumb'),
            'admin_user_show'    => $this->translator->trans('user.show', [], 'admin.breadcrumb'),
        ];
    }

    public function getType()
    {
        return UserType::class;
    }

    public function getUrlAdmin(): array
    {
        return [
            'delete'   => 'api_action_delete',
            'destroy'  => 'api_action_destroy',
            'edit'     => 'admin_user_edit',
            'empty'    => 'api_action_empty',
            'guard'    => 'admin_user_guard',
            'list'     => 'admin_user_index',
            'new'      => 'admin_user_new',
            'preview'  => 'admin_user_preview',
            'restore'  => 'api_action_restore',
            'show'     => 'admin_user_show',
            'trash'    => 'admin_user_trash',
            'workflow' => 'api_action_workflow',
        ];
    }
}
