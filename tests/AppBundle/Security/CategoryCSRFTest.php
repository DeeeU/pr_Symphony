<?php

namespace AppBundle\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * CategoryCSRFTest
 *
 * CSRF保護の実際の動作をテストする統合テストクラス
 * WebTestCaseを使用してブラウザレベルでCSRFをテスト
 */
class CategoryCSRFTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * フォームにCSRFトークンが含まれているかテスト
     * 注意: CategoryControllerがまだ実装されていないため、一旦スキップ
     */
    public function testFormContainsCSRFToken()
    {
        $this->markTestSkipped('CategoryControllerの実装後に有効化します');

        // CategoryController実装後に以下のテストを有効化
        /*
        $crawler = $this->client->request('GET', '/category/new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $csrfTokenField = $crawler->filter('input[name*="_token"]');
        $this->assertEquals(1, $csrfTokenField->count(), 'CSRFトークンフィールドが見つかりません');

        $csrfToken = $csrfTokenField->attr('value');
        $this->assertNotEmpty($csrfToken, 'CSRFトークンが空です');
        $this->assertGreaterThan(10, strlen($csrfToken), 'CSRFトークンが短すぎます');
        */
    }

    /**
     * 有効なCSRFトークンでフォーム送信が成功するかテスト
     */
    public function testValidCSRFTokenSubmission()
    {
        $this->markTestSkipped('CategoryControllerの実装後に有効化します');
    }

    /**
     * 無効なCSRFトークンでフォーム送信が拒否されるかテスト
     */
    public function testInvalidCSRFTokenSubmission()
    {
        $this->markTestSkipped('CategoryControllerの実装後に有効化します');
    }

    /**
     * CSRFトークンなしでフォーム送信が拒否されるかテスト
     */
    public function testMissingCSRFTokenSubmission()
    {
        $this->markTestSkipped('CategoryControllerの実装後に有効化します');
    }

    /**
     * フォーム設定でCSRFが有効になっているかテスト
     */
    public function testCSRFConfigurationInForm()
    {
        $container = $this->client->getContainer();
        $formFactory = $container->get('form.factory');

        // CategoryTypeフォーム作成
        $form = $formFactory->create('AppBundle\Form\CategoryType');

        // CSRF設定確認
        $this->assertTrue($form->getConfig()->getOption('csrf_protection'));
        $this->assertEquals('_token', $form->getConfig()->getOption('csrf_field_name'));
        $this->assertEquals('category_item', $form->getConfig()->getOption('csrf_token_id'));
    }
}
