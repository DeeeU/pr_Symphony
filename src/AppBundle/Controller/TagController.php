<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Tag;
use AppBundle\Form\TagType;

/**
 * Tag Controller
 * @Route("/tag")
 */
class TagController extends Controller
{
  /**
   * @Route("/", name="tag_index")
   */

  public function indexAction(Request $request)
  {
    $repository = $this->getDoctrine()->getRepository(Tag::class);
    $keyword = $request->query->get('keyword');

    if ($keyword) {
      $queryBuilder = $repository->createSearchQueryBuilder($keyword);
    } else {
      $queryBuilder = $repository->createQueryBuilder('t')
        ->orderBy('t.createdAt', 'DESC');
    }
    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
      $queryBuilder,
      $request->query->getInt('page', 1),
      10
    );

    return $this->render('tag/index.html.twig', [
      'pagination' => $pagination,
      'keyword' => $keyword,
    ]);
  }

  /**
   * @Route("/new", name="tag_new")
   */
  public function newAction(Request $request)
  {
    $tag = new Tag();
    $form = $this->createForm(TagType::class, $tag);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($tag);
      $entityManager->flush();

      $this->addFlash('success', 'タグ「' . $tag->getName() . '」を作成しました！');

      return $this->redirectToRoute('tag_index');
    }

    return $this->render('tag/new.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * タグ編集
   * @Route("/{id}/edit", name="tag_edit", requirements={"id"="\d+"})
   */
  public function editAction(Request $request, $id)
  {
    $repository = $this->getDoctrine()->getRepository(Tag::class);
    $tag = $repository->find($id);

    if (!$tag) {
      throw $this->createNotFoundException('タグが見つかりません');
    }

    $form = $this->createForm(TagType::class, $tag);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->flush();

      $this->addFlash('success', 'タグ「' . $tag->getName() .
        '」を更新しました！');

      return $this->redirectToRoute('tag_index');
    }

    return $this->render('tag/edit.html.twig', [
      'tag' => $tag,
      'form' => $form->createView()
    ]);
  }

  /**
   * タグ削除
   * @Route("/{id}/delete", name="tag_delete", methods={"POST"}, requirements={"id"="\d+"})
   */
  public function deleteAction(Request $request, $id)
  {
    $repository = $this->getDoctrine()->getRepository(Tag::class);
    $tag = $repository->find($id);

    if (!$tag) {
      throw $this->createNotFoundException('タグが見つかりません');
    }

    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete' . $id, $token)) {
      $this->addFlash('error', '不正なリクエストです');
      return $this->redirectToRoute('tag_index');
    }

    $name = $tag->getName();

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($tag);
    $entityManager->flush();

    $this->addFlash('success', 'タグ「' . $name . '」を削除しました');

    return $this->redirectToRoute('tag_index');
  }
}
