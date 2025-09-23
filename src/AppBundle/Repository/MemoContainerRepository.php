<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\Memo;
use AppBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;

class MemoContainerRepository extends EntityRepository
{
  /**
   * @param User @user
   * @return array
   */
  public function findByUser(User $user): array
  {
    return $this->createQueryBuilder('mc')
      ->where('mc.user = :user')
      ->setParameter('user', $user)
      ->orderBy('mc.createdAt', 'DESC')
      ->getQuery()
      ->getResult();
  }

  /**
   * @param string|null $keyword
   * @return Doctrine\ORM\QueryBuilder
   */
  public function createSearchQueryBuilder($keyword = null)
  {
    $qb = $this->createQueryBuilder('mc')
      ->orderBy('mc.createdAt', 'DESC');

    if ($keyword) {
      $qb->andWhere('mc.name like :keyword or mc.description like :keyword')
        ->setParameter('keyword', '%' . $keyword . '%');
    }
    return $qb;
  }

  /**
   * @param int $limit
   * @return array
   */
  public function findRecent($limit = 10): array
  {
    return $this->createQueryBuilder('mc')
      ->orderBy('mc.createdAt', 'DESC')
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult();
  }

  /**
   * @return array
   */
  public function findNonEmptyContainers(): array
  {
    return $this->createQueryBuilder('mc')
      ->leftJoin('mc.memos', 'm')
      ->groupBy('mc.id')
      ->having('COUNT(m.id) > 0')
      ->orderBy('mc.createdAt', 'DESC')
      ->getQuery()
      ->getResult();
  }

  /**
   * 特定ユーザーの最新コンテナを取得
   *
   * @param User $user
   * @param int $limit
   * @return array
   */
  public function findRecentByUser(User $user, $limit = 5): array
  {
    return $this->createQueryBuilder('mc')
      ->where('mc.user = :user')
      ->setParameter('user', $user)
      ->orderBy('mc.createdAt', 'DESC')
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult();
  }

  /**
   * 特定のメモを含むコンテナを検索
   *
   * @param Memo $memo
   * @return array
   */
  public function findContainersWithMemo(Memo $memo): array
  {
    return $this->createQueryBuilder('mc')
      ->leftJoin('mc.memos', 'm')
      ->where('m = :memo')
      ->setParameter('memo', $memo)
      ->getQuery()
      ->getResult();
  }

  /**
   * コンテナの統計情報を取得
   *
   * @return array
   */
  public function getContainerStatistics(): array
  {
    $qb = $this->createQueryBuilder('mc')
      ->select('COUNT(mc.id) as total_containers')
      ->addSelect('AVG(memo_count.memo_count) as avg_memos_per_container')
      ->leftJoin('(
                       SELECT mc2.id as container_id, COUNT(m2.id) as memo_count
                       FROM memo_container mc2
                       LEFT JOIN memo_container_memos mcm ON mc2.id = mcm.memocontainer_id
                       LEFT JOIN memo m2 ON mcm.memo_id = m2.id
                       GROUP BY mc2.id
                   )', 'memo_count', 'WITH', 'mc.id = memo_count.container_id');

    return $qb->getQuery()->getSingleResult();
  }
}
