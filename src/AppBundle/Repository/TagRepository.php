<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
  /**
   * タグ検索
   */
  public function createSearchQueryBuilder($keyword)
  {
    $qb = $this->createQueryBuilder('t');

    if ($keyword) {
      $qb->where('t.name like :keyword')
        ->setParameter('keyword', '%' . $keyword . '%');
    }
    return $qb->orderBy('t.createdAt', 'DESC');
  }

  public function findAllOrderByMemoCount()
  {
    return $this->createQueryBuilder('t')
      ->leftJoin('t.memos', 'm')
      ->groupBy('t.id')
      ->orderBy('count(m.id)', 'DESC')
      ->addOrderBy('t.name', 'ASC')
      ->getQuery()
      ->getResult();
  }
}
