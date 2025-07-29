<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/memo")
 */
class MemoController extends Controller
{
    /**
     * @Route("/", name="memo_index")
     */
    public function indexAction()
    {
        // ダミーデータ
        $memos = [
            ['id' => 1, 'title' => '最初のメモ', 'content' => 'Symfony学習開始！'],
            ['id' => 2, 'title' => 'PHP復習', 'content' => 'オブジェクト指向をマスターした'],
            ['id' => 3, 'title' => '今日の予定', 'content' => 'Symfonyのmvcを理解する'],
        ];

        return $this->render('memo/index.html.twig', [
            'memos' => $memos
        ]);
    }

    /**
     * @Route("/new", name="memo_new")
     */
    public function newAction()
    {
        return $this->render('memo/new.html.twig');
    }

    /**
     * @Route("/create", name="memo_create", methods={"POST"})
     */
    public function createAction(Request $request) {
      $title = $request->request->get('title');
      $content = $request->request->get('content');

      if(empty($title) || empty($content)) {
        $this->addFlash('error', 'タイトルを入力して');
        return $this->redirectToRoute('memo_new');
      }

      $this->addFlash('success', 'メモ「' . $title . '」を作成しました。');

      return $this->redirectToRoute('memo_index');
    }

    /**
     * @Route("/{id}", name="memo_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        // ダミーデータ
        $memo = [
            'id' => $id,
            'title' => 'サンプルメモ' . $id,
            'content' => 'これはID' . $id . 'のメモの詳細です。長い文章をここに書くことができます。'
        ];

        return $this->render('memo/show.html.twig', [
            'memo' => $memo
        ]);
    }
}
