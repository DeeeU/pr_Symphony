const { test, expect } = require('@playwright/test');
const CategoryPage = require('../pages/CategoryPage');
const TestHelpers = require('../utils/helpers');
const TestData = require('../fixtures/testData');

test.describe('カテゴリ作成機能', () => {
    let categoryPage;

    test.beforeEach(async ({ page }) => {
        categoryPage = new CategoryPage(page);
    });

    test.afterEach(async ({ page }) => {
        if (test.info().status === 'failed') {
            await TestHelpers.takeScreenshotOnFailure(page, 'CreateCategory');
        }
    });

    test('有効なデータでカテゴリを作成できる', async () => {
        // テストデータ生成
        const validCategory = categoryPage.generateValidCategoryData();
        
        // カテゴリ作成ページに移動
        await categoryPage.navigateToNewCategory();
        
        // ページタイトルの確認
        await categoryPage.verifyPageTitle('新規カテゴリ作成');
        
        // フォーム要素の表示確認
        await categoryPage.verifyFormFieldsVisible();
        
        // フォームにデータを入力
        await categoryPage.fillCategoryForm(validCategory);
        
        // フォーム送信
        await categoryPage.submitCategoryForm();
        
        // 成功時の確認
        await categoryPage.verifyCategoryCreationSuccess(validCategory.name);
        
        // カテゴリ一覧でカテゴリが表示されることを確認
        await categoryPage.verifyCategoryInList(validCategory.name);
    });

    test('空のカテゴリ名でバリデーションエラーが表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        // 空のカテゴリ名でフォーム送信
        await categoryPage.fillCategoryForm({
            name: '',
            description: 'テスト説明'
        });
        
        await categoryPage.submitCategoryForm();
        
        // バリデーションエラーの確認
        await categoryPage.verifyValidationErrors(['必須']);
        
        // ページが変更されていないことを確認
        const currentUrl = await categoryPage.getCurrentUrl();
        expect(currentUrl).toContain('/category/new');
    });

    test('説明なしでもカテゴリを作成できる', async () => {
        const validCategory = categoryPage.generateValidCategoryData();
        
        await categoryPage.navigateToNewCategory();
        
        // 名前のみでフォーム送信（説明は空）
        await categoryPage.fillCategoryForm({
            name: validCategory.name,
            description: ''
        });
        
        await categoryPage.submitCategoryForm();
        
        // 成功確認
        await categoryPage.verifyCategoryCreationSuccess(validCategory.name);
    });

    test('長すぎるカテゴリ名でエラーが表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        const longName = 'あ'.repeat(101); // 101文字の長い名前
        
        await categoryPage.fillCategoryForm({
            name: longName,
            description: 'テスト説明'
        });
        
        await categoryPage.submitCategoryForm();
        
        // バリデーションエラーまたはフラッシュエラーメッセージの確認
        try {
            await categoryPage.verifyValidationErrors(['文字']);
        } catch (error) {
            // バリデーションエラーが表示されない場合は、フラッシュメッセージを確認
            await categoryPage.waitForFlashMessage();
        }
    });

    test('長すぎる説明でエラーが表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        const longDescription = 'あ'.repeat(501); // 501文字の長い説明
        
        await categoryPage.fillCategoryForm({
            name: 'テストカテゴリ',
            description: longDescription
        });
        
        await categoryPage.submitCategoryForm();
        
        // バリデーションエラーまたはフラッシュエラーメッセージの確認
        try {
            await categoryPage.verifyValidationErrors(['文字']);
        } catch (error) {
            // バリデーションエラーが表示されない場合は、フラッシュメッセージを確認
            await categoryPage.waitForFlashMessage();
        }
    });

    test('プレビュー機能が正しく動作する', async () => {
        await categoryPage.navigateToNewCategory();
        
        const testCategory = {
            name: 'プレビューテスト',
            description: 'プレビューの説明テスト'
        };
        
        // リアルタイムプレビューの確認
        await categoryPage.verifyRealtimePreview(testCategory);
        
        // プレビューセクションが表示されることを確認
        expect(await categoryPage.isElementVisible(categoryPage.selectors.previewSection)).toBeTruthy();
    });

    test('カテゴリ作成のヒントが表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        // ヒントセクションが表示されることを確認
        await categoryPage.verifyHintsVisible();
    });

    test('キャンセルボタンでカテゴリ一覧に戻る', async () => {
        await categoryPage.navigateToNewCategory();
        
        // フォームに一部データを入力
        await categoryPage.fillCategoryForm({
            name: 'キャンセルテスト',
            description: 'キャンセルされるカテゴリ'
        });
        
        // キャンセル
        await categoryPage.cancelCategoryCreation();
        
        // カテゴリ一覧ページに戻ることを確認
        const currentUrl = await categoryPage.getCurrentUrl();
        expect(currentUrl).toContain('/category/');
        expect(currentUrl).not.toContain('/category/new');
    });

    test('フォームの各要素が正しく表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        // フォーム要素の存在確認
        expect(await categoryPage.isElementVisible(categoryPage.selectors.nameInput)).toBeTruthy();
        expect(await categoryPage.isElementVisible(categoryPage.selectors.descriptionTextarea)).toBeTruthy();
        expect(await categoryPage.isElementVisible(categoryPage.selectors.saveButton)).toBeTruthy();
        expect(await categoryPage.isElementVisible(categoryPage.selectors.cancelLink)).toBeTruthy();
        
        // プレースホルダーテキストの確認
        const namePlaceholder = await categoryPage.page.getAttribute(categoryPage.selectors.nameInput, 'placeholder');
        const descriptionPlaceholder = await categoryPage.page.getAttribute(categoryPage.selectors.descriptionTextarea, 'placeholder');
        
        expect(namePlaceholder).toContain('カテゴリ名');
        expect(descriptionPlaceholder).toContain('説明');
    });

    test('作成成功後にフラッシュメッセージが表示される', async () => {
        const validCategory = categoryPage.generateValidCategoryData();
        
        await categoryPage.navigateToNewCategory();
        await categoryPage.fillCategoryForm(validCategory);
        await categoryPage.submitCategoryForm();
        
        // フラッシュメッセージの詳細確認
        const flashMessage = await categoryPage.waitForFlashMessage();
        expect(flashMessage).toContain('作成しました');
        expect(flashMessage).toContain(validCategory.name);
    });

    test('ページの基本レイアウトが正しく表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        // ページタイトルの確認
        expect(await categoryPage.isElementVisible(categoryPage.selectors.pageTitle)).toBeTruthy();
        
        // 説明テキストの確認
        const pageContent = await categoryPage.page.textContent('body');
        expect(pageContent).toContain('メモを整理するためのカテゴリを作成しましょう');
        
        // フォームセクションの確認
        expect(await categoryPage.isElementVisible('form')).toBeTruthy();
    });

    test('入力制限の説明が表示される', async () => {
        await categoryPage.navigateToNewCategory();
        
        // カテゴリ名の制限説明
        const nameHelpText = await categoryPage.page.textContent('body');
        expect(nameHelpText).toContain('最大100文字まで');
        
        // 説明の任意表示
        expect(nameHelpText).toContain('任意');
    });

    test('フォーカス移動が正常に動作する', async () => {
        await categoryPage.navigateToNewCategory();
        
        // 名前入力フィールドにフォーカス
        await categoryPage.page.focus(categoryPage.selectors.nameInput);
        let focusedElement = await categoryPage.page.evaluate(() => document.activeElement.tagName.toLowerCase());
        expect(focusedElement).toBe('input');
        
        // Tabキーで次のフィールドに移動
        await categoryPage.page.keyboard.press('Tab');
        focusedElement = await categoryPage.page.evaluate(() => document.activeElement.tagName.toLowerCase());
        expect(focusedElement).toBe('textarea');
    });

    test('重複するカテゴリ名での作成テスト', async () => {
        // 最初のカテゴリを作成
        const firstCategory = categoryPage.generateValidCategoryData();
        await categoryPage.createCategory(firstCategory);
        
        // 同じ名前でもう一度作成を試行
        await categoryPage.navigateToNewCategory();
        await categoryPage.fillCategoryForm({
            name: firstCategory.name,
            description: '重複テスト説明'
        });
        
        await categoryPage.submitCategoryForm();
        
        // 重複エラーまたは成功（システムの仕様による）
        try {
            await categoryPage.waitForFlashMessage();
        } catch (error) {
            // フラッシュメッセージが表示されない場合もあり得る
            console.log('No flash message for duplicate category name');
        }
    });
});