<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Memo;

/**
 * Timeline controller
 * @Route("/timeline")
 */
class TimelineController extends Controller
{
    /**
     * @Route("/", name="timeline_index")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Memo::class);

        // メモを作成日時の降順で取得
        $queryBuilder = $repository->createQueryBuilder('m')
                                   ->orderBy('m.createdAt', 'DESC');

        // ページネーション（1ページ20件）
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('timeline/index.html.twig', [
            'pagination' => $pagination
        ]);
    }
}
