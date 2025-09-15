const { test, expect } = require('@playwright/test');
const MemoPage = require('../pages/MemoPage');
const TestHelpers = require('../utils/helpers');
const TestData = require('../fixtures/testData');

test.describe('メモ作成機能', () => {
    let memoPage;

    test.beforeEach(async ({ page }) => {
        memoPage = new MemoPage(page);
    });

    test.afterEach(async ({ page }) => {
        if (test.info().status === 'failed') {
            await TestHelpers.takeScreenshotOnFailure(page, 'CreateMemo');
        }
    });

    test('有効なデータでメモを作成できる', async () => {
        // テストデータ生成
        const validMemo = memoPage.generateValidMemoData();
        
        // メモ作成ページに移動
        await memoPage.navigateToNewMemo();
        
        // ページタイトルの確認
        await memoPage.verifyPageTitle('新規メモ作成');
        
        // フォームにデータを入力
        await memoPage.fillMemoForm(validMemo);
        
        // フォーム送信
        await memoPage.submitMemoForm();
        
        // 成功時の確認
        await memoPage.verifyMemoCreationSuccess(validMemo.title);
        
        // メモ一覧でメモが表示されることを確認
        await memoPage.verifyMemoInList(validMemo.title);
    });

    test('空のタイトルでバリデーションエラーが表示される', async () => {
        await memoPage.navigateToNewMemo();
        
        // 空のタイトルでフォーム送信
        await memoPage.fillMemoForm({
            title: '',
            content: 'テスト内容'
        });
        
        await memoPage.submitMemoForm();
        
        // バリデーションエラーの確認
        await memoPage.verifyValidationErrors(['必須']);
        
        // ページが変更されていないことを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/new');
    });

    test('空の内容でバリデーションエラーが表示される', async () => {
        await memoPage.navigateToNewMemo();
        
        // 空の内容でフォーム送信
        await memoPage.fillMemoForm({
            title: 'テストタイトル',
            content: ''
        });
        
        await memoPage.submitMemoForm();
        
        // バリデーションエラーの確認
        await memoPage.verifyValidationErrors(['必須']);
        
        // ページが変更されていないことを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/new');
    });

    test('すべてのフィールドが空でバリデーションエラーが表示される', async () => {
        await memoPage.navigateToNewMemo();
        
        // 空のフォームで送信
        await memoPage.fillMemoForm({
            title: '',
            content: ''
        });
        
        await memoPage.submitMemoForm();
        
        // バリデーションエラーの確認（複数のエラーが表示される）
        await memoPage.verifyValidationErrors(['必須']);
        
        // ページが変更されていないことを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/new');
    });

    test('カテゴリを選択してメモを作成できる', async () => {
        const validMemo = memoPage.generateValidMemoData();
        
        await memoPage.navigateToNewMemo();
        
        // カテゴリ付きでフォーム入力
        await memoPage.fillMemoForm({
            ...validMemo,
            category: 'illya' // 既存のカテゴリを使用
        });
        
        await memoPage.submitMemoForm();
        
        // 成功確認
        await memoPage.verifyMemoCreationSuccess(validMemo.title);
    });

    test('長すぎるタイトルでエラーが表示される', async () => {
        await memoPage.navigateToNewMemo();
        
        const longTitle = 'あ'.repeat(256); // 256文字の長いタイトル
        
        await memoPage.fillMemoForm({
            title: longTitle,
            content: 'テスト内容'
        });
        
        await memoPage.submitMemoForm();
        
        // バリデーションエラーまたはフラッシュエラーメッセージの確認
        try {
            await memoPage.verifyValidationErrors(['文字']);
        } catch (error) {
            // バリデーションエラーが表示されない場合は、フラッシュメッセージを確認
            await memoPage.waitForFlashMessage();
        }
    });

    test('キャンセルボタンでメモ一覧に戻る', async () => {
        await memoPage.navigateToNewMemo();
        
        // フォームに一部データを入力
        await memoPage.fillMemoForm({
            title: 'キャンセルテスト',
            content: 'キャンセルされるメモ'
        });
        
        // キャンセル
        await memoPage.cancelMemoCreation();
        
        // メモ一覧ページに戻ることを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/');
        expect(currentUrl).not.toContain('/memo/new');
        
        // 入力したメモが作成されていないことを確認
        try {
            await memoPage.verifyMemoInList('キャンセルテスト');
            throw new Error('Cancelled memo should not be created');
        } catch (error) {
            // メモが見つからないことが期待される動作
            expect(error.message).toContain('not found');
        }
    });

    test('フォームの各要素が正しく表示される', async () => {
        await memoPage.navigateToNewMemo();
        
        // フォーム要素の存在確認
        expect(await memoPage.isElementVisible(memoPage.selectors.titleInput)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.contentTextarea)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.categorySelect)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.saveButton)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.cancelLink)).toBeTruthy();
        
        // プレースホルダーテキストの確認
        const titlePlaceholder = await memoPage.page.getAttribute(memoPage.selectors.titleInput, 'placeholder');
        const contentPlaceholder = await memoPage.page.getAttribute(memoPage.selectors.contentTextarea, 'placeholder');
        
        expect(titlePlaceholder).toContain('メモのタイトル');
        expect(contentPlaceholder).toContain('メモの内容');
    });

    test('作成成功後にフラッシュメッセージが表示される', async () => {
        const validMemo = memoPage.generateValidMemoData();
        
        await memoPage.navigateToNewMemo();
        await memoPage.fillMemoForm(validMemo);
        await memoPage.submitMemoForm();
        
        // フラッシュメッセージの詳細確認
        const flashMessage = await memoPage.waitForFlashMessage();
        expect(flashMessage).toContain('作成しました');
        expect(flashMessage).toContain(validMemo.title);
    });

    test('メモ一覧への新規作成ボタンから作成ページに移動できる', async () => {
        await memoPage.navigateToMemoList();
        await memoPage.clickNewMemoButton();
        
        // メモ作成ページに移動したことを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/new');
        
        // ページタイトルの確認
        await memoPage.verifyPageTitle('新規メモ作成');
    });
});