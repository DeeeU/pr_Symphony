<?php

  namespace Tests\AppBundle\Entity;

  use PHPUnit\Framework\TestCase;
  use AppBundle\Entity\Memo;
  use AppBundle\Entity\Category;

  class MemoAssociationTest extends TestCase {
    public function testMemoCanBeAssociatedWithCategory() {
      $category = new Category();
      $category->setName('開発');

      $memo = new Memo();
      $memo->setTitle('タイトル');
      $memo->setContent('内容');
      $memo->setCategory($category);

      $this->assertEquals($category, $memo->getCategory());
      $this->assertEquals('開発', $memo->getCategory()->getName());
    }

    public function testCategoryCanHaveMultipleMemos() {
      $category = new Category();
      $category->setName('ホゲータ');

      $memo1 = new Memo();
      $memo1->setTitle('タイトル');
      $memo1->setContent('内容');

      $memo2 = new Memo();
      $memo2->setTitle('タイトル2');
      $memo2->setContent('内容2');

      $category->addMemo($memo1);
      $category->addMemo($memo2);

      $this->assertEquals(2, $category->getMemoCount());
      $this->assertTrue($category->getMemos()->contains($memo1));
      $this->assertTrue($category->getMemos()->contains($memo2));
    }

    public function testRemoveMemoFromCategory() {
      $category = new Category();
      $category->setName('開発');

      $memo = new Memo();
      $memo->setTitle('タイトル');
      $memo->setContent('内容');
      $category->addMemo($memo);
      $this->assertEquals(1, $category->getMemoCount());

      $category->removeMemo($memo);
      $this->assertEquals(0, $category->getMemoCount());
    }
  }
