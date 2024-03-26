<?php

namespace Labstag\Command;

use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Lib\CommandLib;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsCommand(name: 'labstag:user')]
class LabstagUserCommand extends CommandLib
{
    public function setUserEmail(
        SymfonyStyle $symfonyStyle,
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        User $user
    ): void
    {
        $accept = false;
        while (!$accept) {
            $question = new Question("Entrer l'email de l'utilisateur : ");
            $email    = $questionHelper->ask($input, $output, $question);
            if (!is_string($email)) {
                $symfonyStyle->error('Email incorrect');

                continue;
            }

            $accept = true;
            $user->setEmail($email);
        }
    }

    public function setUserPassword(
        SymfonyStyle $symfonyStyle,
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        User $user
    ): void
    {
        $accept = false;
        while (!$accept) {
            $question = new Question("Entrer le password de l'utilisateur : ");
            $question->setHidden(true);

            $password1 = $questionHelper->ask($input, $output, $question);
            $question  = new Question("Resaisir le password de l'utilisateur : ");
            $question->setHidden(true);

            $password2 = $questionHelper->ask($input, $output, $question);
            if (!is_string($password1) || !is_string($password2) || $password1 !== $password2) {
                $symfonyStyle->error('Mot de passe incorrect');

                continue;
            }

            $accept = true;
            $user->setPlainPassword($password1);
        }
    }

    public function setUserUsername(
        SymfonyStyle $symfonyStyle,
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        User $user
    ): void
    {
        $accept = false;
        while (!$accept) {
            $question = new Question("Entrer le username de l'utilisateur : ");
            $username = $questionHelper->ask($input, $output, $question);
            if (!is_string($username)) {
                $symfonyStyle->error('Username incorrect');

                continue;
            }

            $accept = true;
            $user->setUsername($username);
        }
    }

    protected function actionEnableDisableDelete(
        UserRepository $userRepository,
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $symfonyStyle,
        string $action
    ): void
    {
        /** @var QuestionHelper $helper */
        $helper         = $this->getHelper('question');
        $choiceQuestion = new ChoiceQuestion(
            "Entrer le username de l'utilisateur : ",
            $this->tableQuestionUser($userRepository)
        );
        $choiceQuestion->setMultiselect(true);

        $usernames = $helper->ask($input, $output, $choiceQuestion);
        if (!is_iterable($usernames)) {
            $symfonyStyle->error('Username incorrect');

            return;
        }

        foreach ($usernames as $username) {
            if (!is_string($username) || '' == $username) {
                continue;
            }

            switch ($action) {
                case 'enable':
                    $this->enable($userRepository, $helper, $username, $symfonyStyle, $input, $output);

                    break;
                case 'disable':
                    $this->disable($userRepository, $helper, $username, $symfonyStyle, $input, $output);

                    break;
                case 'delete':
                    $this->delete($userRepository, $helper, $username, $symfonyStyle, $input, $output);

                    break;
            }
        }
    }

    protected function actionState(
        UserRepository $userRepository,
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $symfonyStyle
    ): void
    {
        /** @var QuestionHelper $helper */
        $helper         = $this->getHelper('question');
        $choiceQuestion = new ChoiceQuestion(
            "Entrer le username de l'utilisateur : ",
            $this->tableQuestionUser($userRepository)
        );
        $username = $helper->ask($input, $output, $choiceQuestion);
        if (!is_string($username)) {
            $symfonyStyle->error('Username incorrect');

            return;
        }

        $this->state($userRepository, $helper, $username, $symfonyStyle, $input, $output);
    }

    protected function actionUpdatePassword(
        UserRepository $userRepository,
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $symfonyStyle
    ): void
    {
        /** @var QuestionHelper $helper */
        $helper         = $this->getHelper('question');
        $choiceQuestion = new ChoiceQuestion(
            "Entrer le username de l'utilisateur : ",
            $this->tableQuestionUser($userRepository)
        );
        $username = $helper->ask($input, $output, $choiceQuestion);
        if (!is_string($username)) {
            $symfonyStyle->error('Username incorrect');

            return;
        }

        $this->updatePassword($userRepository, $helper, $username, $symfonyStyle, $input, $output);
    }

    protected function configure(): void
    {
        $this->setDescription('command for admin user');
    }

    protected function create(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $user         = new User();
        $functions    = [
            'setUserUsername',
            'setUserPassword',
            'setUserEmail',
        ];
        foreach ($functions as $function) {
            /** @var callable $callable */
            $callable = [
                $this,
                $function,
            ];
            call_user_func_array($callable, [$symfonyStyle, $questionHelper, $input, $output, $user]);
        }

        $this->setUserGroup($questionHelper, $input, $output, $user);
        $userRepository->save($user);
        $symfonyStyle->success('Utilisateur ajouté');
    }

    protected function delete(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        string $username,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $entity = $userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User) {
            $symfonyStyle->warning(
                ['Utilisateur introuvable']
            );

            return;
        }

        $choiceQuestion = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir supprimer l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $questionHelper->ask($input, $output, $choiceQuestion);
        if ('oui' !== $action) {
            return;
        }

        $userRepository->remove($entity);
        $symfonyStyle->success('Utilisateur supprimé');
    }

    protected function disable(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        string $username,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $entity = $userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User) {
            $symfonyStyle->warning(
                ['Utilisateur introuvable']
            );

            return;
        }

        $choiceQuestion = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir désactiver l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $questionHelper->ask($input, $output, $choiceQuestion);
        if ('oui' !== $action || !$this->workflowService->has($entity)) {
            $symfonyStyle->warning(
                ['Action impossible']
            );

            return;
        }

        /** @var WorkflowInterface $workflow */
        $workflow = $this->workflowService->get($entity);
        if (!$workflow->can($entity, 'desactiver')) {
            $symfonyStyle->warning(
                ['Action impossible']
            );

            return;
        }

        $workflow->apply($entity, 'desactiver');
        $userRepository->save($entity);
        $symfonyStyle->success('Utilisateur désactivé');
    }

    protected function enable(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        string $username,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $entity = $userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User) {
            $symfonyStyle->warning(
                ['Utilisateur introuvable']
            );

            return;
        }

        $choiceQuestion = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir activer l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $questionHelper->ask($input, $output, $choiceQuestion);
        if ('oui' !== $action || !$this->workflowService->has($entity)) {
            $symfonyStyle->warning(
                ['Action impossible']
            );

            return;
        }

        /** @var WorkflowInterface $workflow */
        $workflow = $this->workflowService->get($entity);
        if (!$workflow->can($entity, 'activer')) {
            $symfonyStyle->warning(
                ['Action impossible']
            );

            return;
        }

        $workflow->apply($entity, 'activer');
        $userRepository->save($entity);
        $symfonyStyle->success('Utilisateur activé');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper         = $this->getHelper('question');
        $choiceQuestion = new ChoiceQuestion(
            'Action à effectué',
            [
                'list'           => 'list',
                'create'         => 'create',
                'enable'         => 'enable',
                'disable'        => 'disable',
                'delete'         => 'delete',
                'state'          => 'state',
                'updatepassword' => 'updatepassword',
                'cancel'         => 'cancel',
            ]
        );

        $action = $helper->ask($input, $output, $choiceQuestion);
        if (!is_string($action)) {
            $output->writeln('Action inconnue');

            return Command::FAILURE;
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->repositoryService->get(User::class);

        match ($action) {
            'list'           => $this->list($userRepository, $symfonyStyle, $output),
            'create'         => $this->create($userRepository, $helper, $symfonyStyle, $input, $output),
            'updatepassword' => $this->actionUpdatePassword($userRepository, $input, $output, $symfonyStyle),
            'state'          => $this->actionState($userRepository, $input, $output, $symfonyStyle),
            'enable', 'disable', 'delete' => $this->actionEnableDisableDelete(
                $userRepository,
                $input,
                $output,
                $symfonyStyle,
                $action
            ),
            'cancel' => $output->writeln('cancel'),
            default  => $output->writeln('Action inconnue'),
        };

        return Command::SUCCESS;
    }

    protected function list(
        UserRepository $userRepository,
        SymfonyStyle $symfonyStyle,
        OutputInterface $output
    ): void
    {
        $users = $userRepository->findBy([], ['username' => 'ASC']);
        $table = [];
        /** @var User $user */
        foreach ($users as $user) {
            /** @var Groupe $groupe */
            $groupe  = $user->getRefgroupe();
            $table[] = [
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'groupe'   => $groupe->getName(),
                'state'    => $user->getState(),
            ];
        }

        $symfonyStyle->table(
            [
                'username',
                'email',
                'groupe',
                'state',
            ],
            $table
        );
        $output->writeln('list');
    }

    protected function state(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        string $username,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $entity = $userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User) {
            $symfonyStyle->warning(
                ['Utilisateur introuvable']
            );

            return;
        }

        $states = [];
        /** @var WorkflowInterface $workflow */
        $workflow    = $this->workflowService->get($entity);
        $transitions = $workflow->getEnabledTransitions($entity);
        foreach ($transitions as $transition) {
            $name          = $transition->getName();
            $states[$name] = $name;
        }

        $choiceQuestion = new ChoiceQuestion(
            "Passer l'utilisateur à l'épage : ",
            $states
        );
        $state = $questionHelper->ask($input, $output, $choiceQuestion);
        if (!is_string($state) || !$workflow->can($entity, $state)) {
            $symfonyStyle->warning(
                ['Action impossible']
            );

            return;
        }

        $workflow->apply($entity, $state);
        $userRepository->save($entity);
        $symfonyStyle->success('Utilisateur passé au stade "'.$state.'"');
    }

    protected function tableQuestionUser(
        UserRepository $userRepository
    ): array
    {
        $users = $userRepository->findBy([], ['username' => 'ASC']);
        $table = [];
        /** @var User $user */
        foreach ($users as $user) {
            /** @var Groupe $groupe */
            $groupe                      = $user->getRefgroupe();
            $table[$user->getUsername()] = json_encode(
                [
                    'username' => $user->getUsername(),
                    'email'    => $user->getEmail(),
                    'groupe'   => $groupe->getName(),
                    'state'    => $user->getState(),
                ],
                JSON_THROW_ON_ERROR
            );
        }

        return $table;
    }

    protected function updatePassword(
        UserRepository $userRepository,
        QuestionHelper $questionHelper,
        string $username,
        SymfonyStyle $symfonyStyle,
        InputInterface $input,
        OutputInterface $output
    ): void
    {
        $entity = $userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User) {
            $symfonyStyle->warning(
                ['Utilisateur introuvable']
            );

            return;
        }

        $question = new Question("Entrer le password de l'utilisateur : ");
        $question->setHidden(true);

        $password1 = $questionHelper->ask($input, $output, $question);
        $question  = new Question("Resaisir le password de l'utilisateur : ");
        $question->setHidden(true);

        $password2 = $questionHelper->ask($input, $output, $question);
        if (!is_string($password1) || !is_string($password2) || $password1 !== $password2) {
            $symfonyStyle->error('Mot de passe incorrect');

            return;
        }

        $entity->setPlainPassword($password1);
        $userRepository->save($entity);
        $symfonyStyle->success('Mot de passe changé');
    }

    private function setUserGroup(
        QuestionHelper $questionHelper,
        InputInterface $input,
        OutputInterface $output,
        User $user
    ): void
    {
        /** @var GroupeRepository $repositoryLib */
        $repositoryLib = $this->repositoryService->get(Groupe::class);
        $groupes       = $repositoryLib->findBy([], ['name' => 'DESC']);
        $data          = [];
        foreach ($groupes as $groupe) {
            /** @var Groupe $groupe */
            if ('visiteur' == $groupe->getCode()) {
                continue;
            }

            $data[$groupe->getCode()] = $groupe->getName();
        }

        $choiceQuestion = new ChoiceQuestion(
            "Groupe à attribuer à l'utilisateur",
            $data
        );
        $selection = $questionHelper->ask($input, $output, $choiceQuestion);
        foreach ($groupes as $groupe) {
            /** @var Groupe $groupe */
            if ($selection != $groupe->getCode()) {
                continue;
            }

            $user->setRefgroupe($groupe);
        }
    }
}
