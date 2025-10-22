<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use AppBundle\Entity\User;

/**
 * ユーザーの権限を管理するコマンド
 *
 * admin権限はセキュリティ上の理由から、
 * このコマンドでのみ付与・削除できるようにする
 */
class UserRoleCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:user:role')
            ->setDescription('ユーザーの権限を管理（admin権限の付与・削除）')
            ->setHelp('このコマンドを使用してユーザーにadmin権限を付与または削除できます。');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $userRepository = $em->getRepository(User::class);

        $helper = $this->getHelper('question');

        // 操作を選択
        $actionQuestion = new ChoiceQuestion(
            'どの操作を実行しますか？',
            ['admin権限を付与', 'admin権限を削除', 'admin一覧を表示'],
            0
        );
        $actionQuestion->setErrorMessage('選択 %s は無効です。');

        $action = $helper->ask($input, $output, $actionQuestion);
        $output->writeln('選択: ' . $action);

        if ($action === 'admin一覧を表示') {
            $this->listAdmins($output, $userRepository);
            return 0;
        }

        // ユーザー一覧を表示
        $users = $userRepository->findAll();

        if (empty($users)) {
            $output->writeln('<error>ユーザーが見つかりません。</error>');
            return 1;
        }

        $output->writeln("\n<info>登録ユーザー一覧:</info>");
        $userChoices = [];
        foreach ($users as $user) {
            $isAdmin = $user->isAdmin() ? '[ADMIN]' : '';
            $label = sprintf('%d: %s (%s) %s',
                $user->getId(),
                $user->getName(),
                $user->getEmail(),
                $isAdmin
            );
            $userChoices[$user->getId()] = $label;
        }

        $userQuestion = new ChoiceQuestion(
            "\n対象のユーザーを選択してください",
            $userChoices
        );
        $userQuestion->setErrorMessage('選択 %s は無効です。');

        $selectedUserLabel = $helper->ask($input, $output, $userQuestion);

        // ユーザーIDを抽出
        preg_match('/^(\d+):/', $selectedUserLabel, $matches);
        $userId = (int)$matches[1];

        $user = $userRepository->find($userId);

        if (!$user) {
            $output->writeln('<error>ユーザーが見つかりません。</error>');
            return 1;
        }

        // 操作を実行
        if ($action === 'admin権限を付与') {
            if ($user->isAdmin()) {
                $output->writeln('<comment>このユーザーは既にadmin権限を持っています。</comment>');
                return 0;
            }

            $user->addRole('ROLE_ADMIN');
            $em->persist($user);
            $em->flush();

            $output->writeln(sprintf(
                '<info>✓ %s (%s) にadmin権限を付与しました。</info>',
                $user->getName(),
                $user->getEmail()
            ));

        } elseif ($action === 'admin権限を削除') {
            if (!$user->isAdmin()) {
                $output->writeln('<comment>このユーザーはadmin権限を持っていません。</comment>');
                return 0;
            }

            $user->removeRole('ROLE_ADMIN');
            $em->persist($user);
            $em->flush();

            $output->writeln(sprintf(
                '<info>✓ %s (%s) からadmin権限を削除しました。</info>',
                $user->getName(),
                $user->getEmail()
            ));
        }

        return 0;
    }

    /**
     * admin権限を持つユーザーの一覧を表示
     */
    private function listAdmins(OutputInterface $output, $userRepository)
    {
        $users = $userRepository->findAll();
        $admins = array_filter($users, function($user) {
            return $user->isAdmin();
        });

        if (empty($admins)) {
            $output->writeln('<comment>admin権限を持つユーザーは存在しません。</comment>');
            return;
        }

        $output->writeln("\n<info>admin権限を持つユーザー一覧:</info>");
        foreach ($admins as $admin) {
            $output->writeln(sprintf(
                '  - %s (%s) [ID: %d]',
                $admin->getName(),
                $admin->getEmail(),
                $admin->getId()
            ));
        }
        $output->writeln('');
    }
}
