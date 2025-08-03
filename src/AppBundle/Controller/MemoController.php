<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Memo;
use AppBundle\Form\MemoType;

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
        $repository = $this->getDoctrine()->getRepository(Memo::class);
        $memos = $repository->findAll();

        return $this->render('memo/index.html.twig', [
            'memos' => $memos
        ]);
    }

    /**
     * @Route("/new", name="memo_new")
     */
    public function newAction(Request $request)
    {
        $memo = new Memo();
        $form = $this->createForm(MemoType::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($memo);
          $entityManager->flush();

          $this->addFlash('success', 'メモ「' . $memo->getTitle() . '」を作成しました！');

          return $this->redirectToRoute('memo_index');
        }
        return $this->render('memo/new.html.twig', [
          'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name="memo_create", methods={"POST"})
     */
    // public function createAction(Request $request)
    // {
    //     $title = $request->request->get('title');
    //     $content = $request->request->get('content');

    //     if (empty($title) || empty($content)) {
    //         $this->addFlash('error', 'タイトルと内容は必須です');
    //         return $this->redirectToRoute('memo_new');
    //     }

    //     $memo = new Memo();
    //     $memo->setTitle($title);
    //     $memo->setContent($content);

    //     $entityManager = $this->getDoctrine()->getManager();
    //     $entityManager->persist($memo);
    //     $entityManager->flush();

    //     $this->addFlash('success', 'メモ「' . $title . '」を作成しました！');

    //     return $this->redirectToRoute('memo_index');
    // }

    /**
     * @Route("/{id}/edit", name="memo_edit", requirements={"id"="\d+"})
     */
    public function editAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Memo::class);
        $memo = $repository->find($id);

        if (!$memo) {
            throw $this->createNotFoundException('メモが見つかりません');
        }

        $form = $this->createForm(MemoType::class, $memo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();

          $this->addFlash('success', 'メモ「' . $memo->getTitle() . '」を更新しました！');

          return $this->redirectToRoute('memo_show', ['id' => $id]);
        }

        return $this->render('memo/edit.html.twig', [
            'memo' => $memo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/update", name="memo_update", methods={"POST"}, requirements={"id"="\d+"})
     */
    // public function updateAction(Request $request, $id)
    // {
    //     $repository = $this->getDoctrine()->getRepository(Memo::class);
    //     $memo = $repository->find($id);

    //     if (!$memo) {
    //         throw $this->createNotFoundException('メモが見つかりません');
    //     }

    //     $title = $request->request->get('title');
    //     $content = $request->request->get('content');

    //     if (empty($title) || empty($content)) {
    //         $this->addFlash('error', 'タイトルと内容は必須です');
    //         return $this->redirectToRoute('memo_edit', ['id' => $id]);
    //     }

    //     $memo->setTitle($title);
    //     $memo->setContent($content);

    //     $entityManager = $this->getDoctrine()->getManager();
    //     $entityManager->flush();

    //     $this->addFlash('success', 'メモ「' . $title . '」を更新しました！');

    //     return $this->redirectToRoute('memo_show', ['id' => $id]);
    // }

    /**
     * @Route("/{id}/delete", name="memo_delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function deleteAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Memo::class);
        $memo = $repository->find($id);

        if (!$memo) {
            throw $this->createNotFoundException('メモが見つかりません');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete'.$id, $token)) {
            $this->addFlash('error', '不正なリクエストです');
            return $this->redirectToRoute('memo_show', ['id' => $id]);
        }

        $title = $memo->getTitle();

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($memo);
        $entityManager->flush();

        $this->addFlash('success', 'メモ「' . $title . '」を削除しました');

        return $this->redirectToRoute('memo_index');
    }

    /**
     * @Route("/{id}", name="memo_show", requirements={"id"="\d+"})
     */
    public function showAction($id)
    {
        $repository = $this->getDoctrine()->getRepository(Memo::class);
        $memo = $repository->find($id);

        if (!$memo) {
            throw $this->createNotFoundException('メモが見つかりません');
        }

        return $this->render('memo/show.html.twig', [
            'memo' => $memo
        ]);
    }
}
