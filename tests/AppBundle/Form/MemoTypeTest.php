<?php

namespace Tests\AppBundle\Form;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Form\MemoType;
use AppBundle\Entity\Memo;
use AppBundle\Entity\Category;

class MemoTypeTest extends KernelTestCase
{
    private $container;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        $this->entityManager = $this->container->get('doctrine')->getManager();

        // テスト用データベースをクリーンに
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        $this->entityManager->close();
        $this->entityManager = null;
        parent::tearDown();
    }

    private function cleanDatabase(): void
    {
        $this->entityManager->createQuery('DELETE FROM AppBundle:Memo')->execute();
        $this->entityManager->createQuery('DELETE FROM AppBundle:Category')->execute();
    }

    public function testSubmitValidDataWithCategory()
    {
        // まずDBにカテゴリを保存
        $category = new Category();
        $category->setName('プログラミング');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // CSRFトークンを無効化してテスト
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create(MemoType::class, null, [
            'csrf_protection' => false  // ← CSRFを無効化
        ]);

        $formData = [
            'title' => 'Symfonyの勉強',
            'content' => 'MVC構造について学習した',
            'category' => $category->getId()
        ];

        $form->submit($formData);

        // デバッグ用
        if (!$form->isValid()) {
            echo "\n=== Form Errors ===\n";
            foreach ($form->getErrors(true) as $error) {
                echo $error->getMessage() . "\n";
            }
        }

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        /** @var Memo $result */
        $result = $form->getData();
        $this->assertInstanceOf(Memo::class, $result);
        $this->assertEquals('Symfonyの勉強', $result->getTitle());
        $this->assertEquals('MVC構造について学習した', $result->getContent());
        $this->assertEquals($category->getId(), $result->getCategory()->getId());
    }

    public function testSubmitValidDataWithoutCategory()
    {
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create(MemoType::class, null, [
            'csrf_protection' => false  // ← CSRFを無効化
        ]);

        $formData = [
            'title' => 'カテゴリなしメモ',
            'content' => '特定のカテゴリに属さないメモ',
            'category' => null
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $result = $form->getData();
        $this->assertNull($result->getCategory());
    }

    public function testFormHasCategoryField()
    {
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create(MemoType::class);

        $this->assertTrue($form->has('category'));
        $this->assertTrue($form->has('title'));
        $this->assertTrue($form->has('content'));
    }

    public function testFormValidationWithBlankTitle()
    {
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create(MemoType::class, null, [
            'csrf_protection' => false  // ← CSRFを無効化
        ]);

        $formData = [
            'title' => '', // 空のタイトル（バリデーションエラーを期待）
            'content' => 'テスト内容',
            'category' => null
        ];

        $form->submit($formData);

        // デバッグ用
        if (!$form->isValid()) {
            echo "\n=== Form Errors (Expected) ===\n";
            foreach ($form->getErrors(true) as $error) {
                echo $error->getMessage() . "\n";
            }
        }

        $this->assertFalse($form->isValid());
        // titleフィールドにエラーがあることを確認
        $this->assertTrue($form->get('title')->getErrors()->count() > 0);
    }

    public function testFormValidationWithBlankContent()
    {
        $formFactory = $this->container->get('form.factory');
        $form = $formFactory->create(MemoType::class, null, [
          'csrf_protection' => false
        ]);

        $formData = [
          'title' => '有効なタイトル',
          'content' => '',
          'category' => null
        ];

        $form->submit($formData);

        $this->assertFalse($form->isValid());
        $this->assertTrue($form->get('content')->getErrors()->count() > 0);
    }
}
