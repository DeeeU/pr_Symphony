<?php

  namespace Tests\AppBundle\Repository;

  use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
  use AppBundle\Entity\Memo;
  use AppBundle\Entity\Category;


  class CategoryRepositoryTest extends KernelTestCase {

    private $entityManager;

    private $categoryRepository;

    protected function setUp(): void {
      $kernel = self::bootKernel();

      $this->entityManager = $kernel->getContainer()
           ->get('doctrine')
           ->getManager();

      $this->categoryRepository = $this->entityManager
                                       ->getRepository(Category::class);

      $this->cleanDatabase();
    }

    protected function tearDown(): void {
      parent::tearDown();

      $this->cleanDatabase();
      $this->entityManager->close();
      $this->entityManager = null; // メモリリーク防止
    }

    private function cleanDatabase(): void {
      $this->entityManager->createQuery('delete from AppBundle:Memo')->execute();
      $this->entityManager->createQuery('delete from AppBundle:Category')->execute();
    }

    public function testCanSaveAndRetrieveCategory() {
      $category = new Category();
      $category->setName('ホラー');
      $category->setDescription('怖い');
      $category->setColor('#007bff');

      $this->entityManager->persist($category);
      $this->entityManager->flush();
      $savedId = $category->getId();

      $retrievedCategory = $this->categoryRepository->find($savedId);

      $this->assertNotNull($retrievedCategory);
      $this->assertEquals('ホラー', $retrievedCategory->getName());
      $this->assertEquals('怖い', $retrievedCategory->getDescription());
      $this->assertEquals('#007bff', $retrievedCategory->getColor());
    }

    public function testFindByNameExcluding() {
      $category = new Category();
      $category->setName('ホラー');

      $this->entityManager->persist($category);
      $this->entityManager->flush();

      $categoryId = $category->getId();
      $this->entityManager->clear();

      $found = $this->categoryRepository->findByNameExcluding('ホラー');
      $this->assertNotNull($found, 'カテゴリが見つかるべき');
      $this->assertEquals('ホラー', $found->getName());

      $found = $this->categoryRepository->findByNameExcluding('ホラー', $categoryId);
      $this->assertNull($found, 'カテゴリは見つからない');

      $found = $this->categoryRepository->findByNameExcluding('アクション');
      $this->assertNull($found, '存在しないカテゴリは見つからない');
    }

    public function testCreateSearchQueryBuilder() {
      $category1 = new Category();
      $category1->setName('ホラー');
      $category1->setDescription('怖い');

      $category2 = new Category();
      $category2->setName('アクション');
      $category2->setDescription('激しめ');

      $this->entityManager->persist($category1);
      $this->entityManager->persist($category2);
      $this->entityManager->flush();
      $this->entityManager->clear();

      $queryBuilder = $this->categoryRepository->createSearchQueryBuilder('ホラ');
      $results = $queryBuilder->getQuery()->getResult();

      $this->assertCount(1, $results);
      $this->assertEquals('ホラー', $results[0]->getName());

      $queryBuilder = $this->categoryRepository->createSearchQueryBuilder();
      $results = $queryBuilder->getQuery()->getResult();

      $this->assertCount(2, $results);
    }
  }
