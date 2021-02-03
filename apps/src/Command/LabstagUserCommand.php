<?php

namespace Labstag\Command;

use Doctrine\ORM\EntityManagerInterface;
use Labstag\Entity\Groupe;
use Labstag\Entity\User;
use Labstag\Repository\GroupeRepository;
use Labstag\Repository\UserRepository;
use Labstag\RequestHandler\UserRequestHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Registry;

class LabstagUserCommand extends Command
{

    protected static $defaultName = 'labstag:user';

    protected UserRepository $userRepository;

    protected UserRequestHandler $userRequestHandler;

    protected EntityManagerInterface $entityManager;

    protected Registry $workflows;

    protected GroupeRepository $groupeRepository;

    public function __construct(
        UserRepository $userRepository,
        GroupeRepository $groupeRepository,
        Registry $workflows,
        EntityManagerInterface $entityManager,
        UserRequestHandler $userRequestHandler,
        string $name = null
    )
    {
        $this->groupeRepository   = $groupeRepository;
        $this->entityManager      = $entityManager;
        $this->workflows          = $workflows;
        $this->userRequestHandler = $userRequestHandler;
        $this->userRepository     = $userRepository;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('command for admin user');
    }

    protected function actionState($input, $output, $inputOutput)
    {
        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            "Entrer le username de l'utilisateur : ",
            $this->tableQuestionUser()
        );
        $username = $helper->ask($input, $output, $question);
        $this->state($helper, $username, $inputOutput, $input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputOutput = new SymfonyStyle($input, $output);
        $helper      = $this->getHelper('question');
        $question    = new ChoiceQuestion(
            'Action à effectué',
            [
                'list'    => 'list',
                'create'  => 'create',
                'enable'  => 'enable',
                'disable' => 'disable',
                'delete'  => 'delete',
                'state'   => 'state',
            ]
        );

        $action = $helper->ask($input, $output, $question);
        switch ($action) {
            case 'list':
                $this->list($inputOutput, $output);
                break;
            case 'create':
                $this->create($helper, $inputOutput, $input, $output);
                break;
            case 'state':
                $this->actionState($input, $output, $inputOutput);
                break;
            case 'enable':
            case 'disable':
            case 'delete':
                $this->actionEnableDisableDelete($input, $output, $inputOutput, $action);
                break;
        }

        return Command::SUCCESS;
    }

    protected function actionEnableDisableDelete($input, $output, $inputOutput, $action)
    {
        $helper   = $this->getHelper('question');
        $question = new ChoiceQuestion(
            "Entrer le username de l'utilisateur : ",
            $this->tableQuestionUser()
        );
        $question->setMultiselect(true);
        $usernames = $helper->ask($input, $output, $question);
        foreach ($usernames as $username) {
            if ($username == '') {
                continue;
            }

            if ('enable' === $action) {
                $this->enable($helper, $username, $inputOutput, $input, $output);
            } elseif ('disable' === $action) {
                $this->disable($helper, $username, $inputOutput, $input, $output);
            } elseif ('delete' === $action) {
                $this->delete($helper, $username, $inputOutput, $input, $output);
            }
        }
    }

    protected function list($inputOutput, OutputInterface $output)
    {
        $users = $this->userRepository->findBy([], ['username' => 'ASC']);
        $table = [];
        foreach ($users as $user) {
            /** @var User $user */
            $table[] = [
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'groupe'   => $user->getGroupe()->getName(),
                'state'    => $user->getState(),
            ];
        }

        $inputOutput->table(
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

    protected function create($helper, $inputOutput, InputInterface $input, OutputInterface $output)
    {
        $user     = new User();
        $old      = clone $user;
        $question = new Question("Entrer le username de l'utilisateur : ");
        $username = $helper->ask($input, $output, $question);
        $user->setUsername($username);
        $question = new Question("Entrer le password de l'utilisateur : ");
        $question->setHidden(true);
        $password = $helper->ask($input, $output, $question);
        $user->setPlainPassword($password);
        $question = new Question("Entrer l'email de l'utilisateur : ");
        $email    = $helper->ask($input, $output, $question);
        $user->setEmail($email);
        $groupes = $this->groupeRepository->findBy([], ['name' => 'DESC']);
        $data    = [];
        foreach ($groupes as $groupe) {
            /** @var Groupe $groupe */
            if ('visiteur' == $groupe->getCode()) {
                continue;
            }

            $data[$groupe->getCode()] = $groupe->getName();
        }

        $question  = new ChoiceQuestion(
            "Groupe à attribuer à l'utilisateur",
            $data
        );
        $selection = $helper->ask($input, $output, $question);
        foreach ($groupes as $groupe) {
            /** @var Groupe $groupe */
            if ($selection == $groupe->getCode()) {
                $user->setGroupe($groupe);
            }
        }

        $this->userRequestHandler->handle($old, $user);
        $inputOutput->success('Utilisateur ajouté');
    }

    protected function state($helper, $username, $inputOutput, InputInterface $input, OutputInterface $output)
    {
        $entity = $this->userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User || is_null($entity)) {
            $inputOutput->warning(
                ['Utilisateur introuvable']
            );
            return;
        }

        $states      = [];
        $workflow    = $this->workflows->get($entity);
        $transitions = $workflow->getEnabledTransitions($entity);
        foreach ($transitions as $transition) {
            $name          = $transition->getName();
            $states[$name] = $name;
        }

        $question = new ChoiceQuestion(
            "Passer l'utilisateur à l'épage : ",
            $states
        );
        $state    = $helper->ask($input, $output, $question);
        if (!$workflow->can($entity, $state)) {
            $inputOutput->warning(
                ['Action impossible']
            );
            return;
        }

        $workflow->apply($entity, $state);
        $this->entityManager->flush();
        $inputOutput->success('Utilisateur passé au stade "'.$state.'"');
    }

    protected function enable($helper, $username, $inputOutput, InputInterface $input, OutputInterface $output)
    {
        $entity = $this->userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User || is_null($entity)) {
            $inputOutput->warning(
                ['Utilisateur introuvable']
            );
            return;
        }

        $question = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir activer l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $helper->ask($input, $output, $question);
        if ('oui' !== $action || !$this->workflows->has($entity)) {
            $inputOutput->warning(
                ['Action impossible']
            );
            return;
        }

        $workflow = $this->workflows->get($entity);
        if (!$workflow->can($entity, 'activer')) {
            $inputOutput->warning(
                ['Action impossible']
            );
            return;
        }

        $workflow->apply($entity, 'activer');
        $this->entityManager->flush();
        $inputOutput->success('Utilisateur activé');
    }

    protected function disable($helper, $username, $inputOutput, InputInterface $input, OutputInterface $output)
    {
        $entity = $this->userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User || is_null($entity)) {
            $inputOutput->warning(
                ['Utilisateur introuvable']
            );
            return;
        }

        $question = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir désactiver l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $helper->ask($input, $output, $question);
        if ('oui' !== $action || !$this->workflows->has($entity)) {
            $inputOutput->warning(
                ['Action impossible']
            );
            return;
        }

        $workflow = $this->workflows->get($entity);
        if (!$workflow->can($entity, 'desactiver')) {
            $inputOutput->warning(
                ['Action impossible']
            );
            return;
        }

        $workflow->apply($entity, 'desactiver');
        $this->entityManager->flush();
        $inputOutput->success('Utilisateur désactivé');
    }

    protected function delete($helper, string $username, $inputOutput, InputInterface $input, OutputInterface $output)
    {
        $entity = $this->userRepository->findOneBy(['username' => $username]);
        if (!$entity instanceof User || is_null($entity)) {
            $inputOutput->warning(
                ['Utilisateur introuvable']
            );
            return;
        }

        $question = new ChoiceQuestion(
            "Êtes-vous sûr de bien vouloir supprimer l'utilisateur ".$username.' ?',
            [
                'non' => 'non',
                'oui' => 'oui',
            ]
        );

        $action = $helper->ask($input, $output, $question);
        if ('oui' !== $action) {
            return;
        }

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        $inputOutput->success('Utilisateur supprimé');
    }

    protected function tableQuestionUser()
    {
        $users = $this->userRepository->findBy([], ['username' => 'ASC']);
        $table = [];
        foreach ($users as $user) {
            /** @var User $user */
            $table[$user->getUsername()] = json_encode(
                [
                    'username' => $user->getUsername(),
                    'email'    => $user->getEmail(),
                    'groupe'   => $user->getGroupe()->getName(),
                    'state'    => $user->getState(),
                ]
            );
        }

        return $table;
    }
}