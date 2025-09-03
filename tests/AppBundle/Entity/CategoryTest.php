<?php

namespace Tests\AppBundle\Entity;

use PHPUnit\Framework\TestCase;
use AppBundle\Entity\Category;

class CategoryTest extends TestCase
{
    public function testCategoryCanBeInstantiated()
    {
        $category = new Category();

        // まずは基本的なテスト
        $this->assertInstanceOf(Category::class, $category);

        // createdAt のテスト - null でないことを確認
        $this->assertNotNull($category->getCreatedAt());

        // createdAt が DateTime インスタンスであることを確認
        $this->assertInstanceOf(\DateTime::class, $category->getCreatedAt());

        // デフォルトカラーのテスト
        $this->assertEquals('#007bff', $category->getColor());

        // 初期状態ではメモが空であることを確認
        $this->assertCount(0, $category->getMemos());
    }

    public function testCategoryNameCanBeSetAndRetrieved()
    {
        $category = new Category();
        $name = 'プログラミング';

        $result = $category->setName($name);

        // Fluent interface のテスト（メソッドチェーン）
        $this->assertSame($category, $result);
        $this->assertEquals($name, $category->getName());
    }

    public function testCategoryDescriptionCanBeSetAndRetrieved()
    {
        $category = new Category();
        $description = 'プログラミング関連のメモ';

        $category->setDescription($description);

        $this->assertEquals($description, $category->getDescription());
    }

    public function testCategoryColorCanBeSetAndRetrieved()
    {
        $category = new Category();
        $color = '#ff5733';

        $category->setColor($color);

        $this->assertEquals($color, $category->getColor());
    }

    public function testGetColorPresets() {
      $presets = Category::getColorPresets();

      $this->assertIsArray($presets);
      $this->assertArrayHasKey('#007bff', $presets);
      $this->assertEquals('青', $presets['#007bff']);
    }

    public function testGetColorName() {
      $category = new Category();

      $category->setColor('#007bff');
      $this->assertEquals('青', $category->getColorName());

      $customized_color_category = new Category();

      $customized_color_category->setColor('#002bee');
      $this->assertEquals('カスタム', $customized_color_category->getColorName());
    }
}
