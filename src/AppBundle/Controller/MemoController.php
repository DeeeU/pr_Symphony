<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Memo;

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
    public function newAction()
    {
        return $this->render('memo/new.html.twig');
    }

    /**
     * @Route("/create", name="memo_create", methods={"POST"})
     */
    public function createAction(Request $request)
    {
        $title = $request->request->get('title');
        $content = $request->request->get('content');

        if (empty($title) || empty($content)) {
            $this->addFlash('error', 'タイトルと内容は必須です');
            return $this->redirectToRoute('memo_new');
        }

        $memo = new Memo();
        $memo->setTitle($title);
        $memo->setContent($content);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($memo);
        $entityManager->flush();

        $this->addFlash('success', 'メモ「' . $title . '」を作成しました！');

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
