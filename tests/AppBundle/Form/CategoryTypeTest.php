<?php

namespace AppBundle\Tests\Form;

use Symfony\Component\Form\Test\TypeTestCase;
use AppBundle\Form\CategoryType;
use AppBundle\Entity\Category;

class CategoryTypeTest extends TypeTestCase
{
    public function testSubmitValidData() {
        $formData = [
            'name' => 'テストカテゴリ',
            'description' => 'テスト用の説明文です'
        ];

        $expectedCategory = new Category();
        $expectedCategory->setName('テストカテゴリ');
        $expectedCategory->setDescription('テスト用の説明文です');

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        $result = $form->getData();
        $this->assertInstanceOf(Category::class, $result);
        $this->assertEquals($expectedCategory->getName(), $result->getName());
        $this->assertEquals($expectedCategory->getDescription(), $result->getDescription());
    }

    public function testSubmitBlankName() {
        $formData = [
            'name' => '',
            'description' => 'テスト用の説明文です'
        ];

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $category = $form->getData();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('', $category->getName());
        $this->assertEquals('テスト用の説明文です', $category->getDescription());
    }

    public function testSubmitTooLongName() {
        $formData = [
            'name' => str_repeat('あ', 101),
            'description' => 'テスト用の説明文です'
        ];

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $category = $form->getData();

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals(str_repeat('あ', 101), $category->getName());
    }

    public function testSubmitTooLongDescription() {
        $formData = [
            'name' => '正常なカテゴリ名',
            'description' => str_repeat('あ', 256)
        ];

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $category = $form->getData();
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('正常なカテゴリ名', $category->getName());
        $this->assertEquals(str_repeat('あ', 256), $category->getDescription());
    }

    public function testSubmitBlankDescription() {
        $formData = [
            'name' => 'テストカテゴリ',
            'description' => ''
        ];

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isValid());

        $result = $form->getData();
        $this->assertEquals('テストカテゴリ', $result->getName());
        $this->assertEquals('', $result->getDescription());
    }

    public function testFormHasExpectedFields() {
        $form = $this->factory->create(CategoryType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('description'));
        $this->assertTrue($form->has('save'));

        $this->assertFalse($form->has('id'));
        $this->assertFalse($form->has('createdAt'));
        $this->assertFalse($form->has('updatedAt'));
    }

    public function testFormAttributes()
    {
        $form = $this->factory->create(CategoryType::class);
        $view = $form->createView();

        $nameAttrs = $view['name']->vars['attr'];
        $this->assertEquals('form-control', $nameAttrs['class']);
        $this->assertEquals('カテゴリ名を入力してください', $nameAttrs['placeholder']);
        $this->assertEquals(100, $nameAttrs['maxlength']);

        $descAttrs = $view['description']->vars['attr'];
        $this->assertEquals('form-control', $descAttrs['class']);
        $this->assertEquals(4, $descAttrs['rows']);
    }

    public function testFormWithExistingCategory() {
        $existingCategory = new Category();
        $existingCategory->setName('既存カテゴリ');
        $existingCategory->setDescription('既存の説明文');

        $form = $this->factory->create(CategoryType::class, $existingCategory);
        $view = $form->createView();

        $this->assertEquals('既存カテゴリ', $view['name']->vars['value']);
        $this->assertEquals('既存の説明文', $view['description']->vars['value']);
    }

    public function testCSRFProtection() {
        $form = $this->factory->create(CategoryType::class);
        $view = $form->createView();

        // CSRFトークンが設定されているか確認
        $this->assertTrue($form->getConfig()->getOption('csrf_protection'));
        $this->assertEquals('_token', $form->getConfig()->getOption('csrf_field_name'));
        $this->assertEquals('category_item', $form->getConfig()->getOption('csrf_token_id'));

      }

    public function testBlockPrefix() {
        $categoryType = new CategoryType();
        $this->assertEquals('appbundle_category', $categoryType->getBlockPrefix());
    }

    public function testDataTransformation() {
        $formData = [
            'name' => '  トリムテスト  ',
            'description' => '  説明文  '
        ];

        $form = $this->factory->create(CategoryType::class);
        $form->submit($formData);

        $this->assertTrue($form->isValid());

        $result = $form->getData();
        $this->assertEquals('トリムテスト', $result->getName());
    }
}
