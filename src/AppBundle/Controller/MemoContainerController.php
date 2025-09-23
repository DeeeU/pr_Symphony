<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\MemoContainer;
use AppBundle\Entity\User;
use AppBundle\Entity\Memo;

/**
 * @Route("/memo-container")
 */
class MemoContainerController extends Controller
{
  /**
   * @Route("/user/{userId}/summary", name="memo_container_user_summary", requirements={"userId"="\d+"})
   */
  public function userSummaryAction($userId)
  {
    $em = $this->getDoctrine()->getManager();

    $user = $em->getRepository(User::class)->find($userId);
    if (!$user) {
      throw $this->createNotFoundException('ユーザーが見つかりません');
    }

    $memoRepository = $em->getRepository(Memo::class);
    $memos = $memoRepository->findByAuthor($user);

    $container = new MemoContainer();
    $container->setName($user->getName() . 'さんのメモまとめ');
    $container->setDescription('総メモ数:' . count($memos) . '件 | 作成日:' . date('Y年m月d日'));
    $container->setUser($user);

    foreach ($memos as $memo) {
      $container->addMemo($memo);
    }

    return $this->render('memo_container/user_summary.html.twig', [
      'container' => $container,
      'user' => $user
    ]);
  }

  /**
   * コンテナ一覧ページ
   * @Route("/", name="memo_container_index")
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();
    $userRepository = $em->getRepository(User::class);

    // メモを持っているユーザーのみ取得
    $usersWithMemos = $userRepository->findUsersWithMemos();

    return $this->render('memo_container/index.html.twig', [
      'users' => $usersWithMemos
    ]);
  }
}
