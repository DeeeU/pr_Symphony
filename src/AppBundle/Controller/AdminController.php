<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Entity\Memo;
use AppBundle\Entity\Category;

/**
 * Admin controller
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_dashboard")
     */
    public function dashboardAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // 統計情報を取得
        $userCount = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $memoCount = $em->getRepository(Memo::class)->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $categoryCount = $em->getRepository(Category::class)->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // 最近のユーザーを取得
        $recentUsers = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // 最近のメモを取得
        $recentMemos = $em->getRepository(Memo::class)
            ->createQueryBuilder('m')
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userCount,
            'memoCount' => $memoCount,
            'categoryCount' => $categoryCount,
            'recentUsers' => $recentUsers,
            'recentMemos' => $recentMemos,
        ]);
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function usersAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);

        $queryBuilder = $repository->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/users.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/users/{id}/toggle-admin", name="admin_user_toggle_admin", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function toggleAdminAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('ユーザーが見つかりませんでした');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('toggle-admin' . $id, $token)) {
            $this->addFlash('error', '不正なリクエストです');
            return $this->redirectToRoute('admin_users');
        }

        $em = $this->getDoctrine()->getManager();

        if ($user->isAdmin()) {
            // 管理者権限を削除
            $roles = array_filter($user->getRoles(), function($role) {
                return $role !== 'ROLE_ADMIN';
            });
            $user->setRoles(array_values($roles));
            $this->addFlash('success', 'ユーザー「' . $user->getName() . '」の管理者権限を削除しました');
        } else {
            // 管理者権限を付与
            $roles = $user->getRoles();
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles(array_unique($roles));
            $this->addFlash('success', 'ユーザー「' . $user->getName() . '」に管理者権限を付与しました');
        }

        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/{id}/delete", name="admin_user_delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function deleteUserAction(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('ユーザーが見つかりませんでした');
        }

        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-user' . $id, $token)) {
            $this->addFlash('error', '不正なリクエストです');
            return $this->redirectToRoute('admin_users');
        }

        $name = $user->getName();

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'ユーザー「' . $name . '」を削除しました');

        return $this->redirectToRoute('admin_users');
    }
}
