<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;

/**
 * Category controller
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="category_index")
     */
    public function indexAction(Request $request)
    {
      $repository = $this->getDoctrine() -> getRepository(Category::class);

      $keyword = $request->query->get('keyword');

      if($keyword) {
        $queryBuilder = $repository->createSearchQueryBuilder($keyword);
      } else {
        $queryBuilder = $repository->createQueryBuilder('c')
                                   ->orderBy('c.createdAt','DESC');
      }

      $paginator = $this->get('knp_paginator');
      $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        10
      );

      return $this->render('category/index.html.twig', [
        'pagination' => $pagination,
        'keyword' => $keyword,
      ]);
    }

    /**
     * @Route("/{id}/edit", name="category_edit")
     */
    public function editAction(Request $request, $id)
    {
      $repository = $this->getDoctrine()->getRepository(Category::class);
      $category = $repository->find($id);

      if(!$category) {
        throw $this->createNotFoundException('カテゴリは見つかりませんでした');
      }

      $form = $this->editActioncreateForm(CategoryType::class, $category);
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->addFlash('success', 'カテゴリ「' . $category->getName() . '」を更新しました！');

        return $this->redirectToRoute('category_show', ['id' => $id]);
      }

      return $this->render('category/edit.html.twig', [
        'category' => $category,
        'form' => $form->createView()
      ]);
    }

    /**
     * @Route("/{id}", name="category_show")
     */
    public function showAction($id)
    {
      $repository = $this->getDoctrine()->getRepository(Category::class);
      $category = $repository->find($id);

      if(!$category) {
        throw $this->createNotFoundException('カテゴリは見つかりませんでした');
      }

      return $this->render('category/show.html.twig',[
        'category' => $category
      ]);
    }

    /**
     * @Route("/new", name="category_new")
     */
    public function newAction(Request $request)
    {
      $category = new Category();
      $form = $this->createForm(CategoryType::class, $category);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine() -> getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        $this->addFlash('success', 'カテゴリ「' . $category->getName() . '」を作成しました。');

        return $this->redirectToRoute('category_index');
      }

      return $this->render('category/new.html.twig', [
        'form' => $form->createView()
      ]);
    }

    /**
     * @Route("/{id}/delete", name="category_delete", methods={"POST"})
     */
    public function deleteAction(Request $request, $id)
    {
      $repository = $this->getDoctrine()->getRepository(Category::class);
      $category = $repository->find($id);

      if(!$category) {
        throw $this->createNotFoundException('カテゴリは見つかりませんでした');
      }

      $token = $request->request->get('_token');
      if(!$this->isCsrfTokenValid('delete' . $id, $token)) {
        $this->addFlash('error', '不正なリクエストです');
        return $this->redirectToRoute('category_show', ['id' => $id]);
      }

      $name = $category->getName();

      if($category->getMemoCount() > 0) {
        $this->addFlash('error', 'カテゴリ「' . $name . '」にはメモが関連付けられているため削除できません');
        return $this->redirectToRoute('category_show', ['id' => $id]);
      }

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->remove($category);
      $entityManager->flush();

      $this->addFlash('success', 'カテゴリ「' . $name . '」を削除しました');

      return $this->redirectToRoute('category_index');
    }

}
