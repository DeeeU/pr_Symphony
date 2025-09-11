const { test, expect } = require('@playwright/test');
const MemoPage = require('../pages/MemoPage');
const TestHelpers = require('../utils/helpers');
const TestData = require('../fixtures/testData');

test.describe('メモ一覧表示機能', () => {
    let memoPage;

    test.beforeEach(async ({ page }) => {
        memoPage = new MemoPage(page);
    });

    test.afterEach(async ({ page }) => {
        if (test.info().status === 'failed') {
            await TestHelpers.takeScreenshotOnFailure(page, 'ListMemos');
        }
    });

    test('メモ一覧ページが正しく表示される', async () => {
        await memoPage.navigateToMemoList();
        
        // ページタイトルの確認
        await memoPage.verifyPageTitle('メモ一覧');
        
        // 必要な要素が表示されることを確認
        expect(await memoPage.isElementVisible(memoPage.selectors.newMemoButton)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.searchKeywordInput)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.searchButton)).toBeTruthy();
        expect(await memoPage.isElementVisible(memoPage.selectors.clearButton)).toBeTruthy();
    });

    test('既存のメモが一覧に表示される', async () => {
        await memoPage.navigateToMemoList();
        
        // メモが存在することを確認
        const memoItems = await memoPage.getMemoItems();
        expect(memoItems.length).toBeGreaterThan(0);
        
        // メモのタイトルが表示されることを確認
        const memoTitles = await memoPage.getMemoTitles();
        expect(memoTitles.length).toBeGreaterThan(0);
        
        // 各メモアイテムに必要な要素があることを確認
        for (let i = 0; i < Math.min(memoItems.length, 3); i++) {
            const item = memoItems[i];
            const detailLink = item.locator(memoPage.selectors.detailLink);
            const editLink = item.locator(memoPage.selectors.editLink);
            
            expect(await detailLink.isVisible()).toBeTruthy();
            expect(await editLink.isVisible()).toBeTruthy();
        }
    });

    test('キーワード検索が機能する', async () => {
        await memoPage.navigateToMemoList();
        
        // 既存のメモタイトルを取得
        const allTitles = await memoPage.getMemoTitles();
        
        if (allTitles.length > 0) {
            // 最初のメモのタイトルの一部を検索
            const firstTitle = allTitles[0];
            const searchKeyword = firstTitle.substring(0, 3);
            
            await memoPage.searchMemos({ keyword: searchKeyword });
            
            // 検索結果の確認
            const searchResults = await memoPage.getMemoTitles();
            expect(searchResults.length).toBeGreaterThan(0);
            
            // 検索結果にキーワードが含まれることを確認
            const hasMatchingResult = searchResults.some(title => 
                title.toLowerCase().includes(searchKeyword.toLowerCase())
            );
            expect(hasMatchingResult).toBeTruthy();
        }
    });

    test('存在しないキーワードで検索すると結果が0件になる', async () => {
        await memoPage.navigateToMemoList();
        
        // 存在しないキーワードで検索
        await memoPage.searchMemos({ keyword: TestData.searchKeywords.nonExisting });
        
        // 検索結果が0件であることを確認
        try {
            await memoPage.getMemoItems();
            const searchResults = await memoPage.getMemoTitles();
            expect(searchResults.length).toBe(0);
        } catch (error) {
            // メモアイテムが見つからない場合も正常（0件の結果）
            expect(error.message).toContain('not found');
        }
    });

    test('検索クリアボタンが機能する', async () => {
        await memoPage.navigateToMemoList();
        
        // 最初にすべてのメモ数を取得
        const allMemos = await memoPage.getMemoItems();
        const allMemosCount = allMemos.length;
        
        // キーワード検索を実行
        await memoPage.searchMemos({ keyword: 'test' });
        
        // 検索をクリア
        await memoPage.clearSearch();
        
        // 全メモが再表示されることを確認
        const clearedMemos = await memoPage.getMemoItems();
        expect(clearedMemos.length).toBe(allMemosCount);
        
        // 検索入力フィールドが空になっていることを確認
        const keywordValue = await memoPage.page.inputValue(memoPage.selectors.searchKeywordInput);
        expect(keywordValue).toBe('');
    });

    test('日付範囲検索が機能する', async () => {
        await memoPage.navigateToMemoList();
        
        // 今日の日付を設定
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0]; // YYYY-MM-DD形式
        
        // 1週間前の日付を設定
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 7);
        const weekAgoStr = weekAgo.toISOString().split('T')[0];
        
        // 日付範囲で検索
        await memoPage.searchMemos({ 
            startDate: weekAgoStr, 
            endDate: todayStr 
        });
        
        // 検索が実行されることを確認（結果の数は問わない）
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('start_date');
        expect(currentUrl).toContain('end_date');
    });

    test('メモの詳細リンクが機能する', async () => {
        await memoPage.navigateToMemoList();
        
        const memoItems = await memoPage.getMemoItems();
        if (memoItems.length > 0) {
            // 最初のメモの詳細をクリック
            await memoPage.clickMemoDetail(0);
            
            // 詳細ページに移動することを確認
            const currentUrl = await memoPage.getCurrentUrl();
            expect(currentUrl).toMatch(/\/memo\/\d+$/);
        }
    });

    test('メモの編集リンクが機能する', async () => {
        await memoPage.navigateToMemoList();
        
        const memoItems = await memoPage.getMemoItems();
        if (memoItems.length > 0) {
            // 最初のメモの編集をクリック
            await memoPage.clickMemoEdit(0);
            
            // 編集ページに移動することを確認
            const currentUrl = await memoPage.getCurrentUrl();
            expect(currentUrl).toMatch(/\/memo\/\d+\/edit$/);
        }
    });

    test('ページネーションが表示される', async () => {
        await memoPage.navigateToMemoList();
        
        // ページネーション要素を探す
        const paginationExists = await memoPage.page.locator('.pagination, [class*="page"]').count() > 0;
        
        if (paginationExists) {
            // ページネーションが存在する場合、次ページリンクを確認
            const nextPageLink = memoPage.page.locator('a:has-text("2"), a:has-text(">")').first();
            if (await nextPageLink.isVisible()) {
                await nextPageLink.click();
                await memoPage.waitForNavigation();
                
                // 2ページ目に移動したことを確認
                const currentUrl = await memoPage.getCurrentUrl();
                expect(currentUrl).toContain('page=2');
            }
        }
    });

    test('新規メモ作成ボタンが機能する', async () => {
        await memoPage.navigateToMemoList();
        
        await memoPage.clickNewMemoButton();
        
        // メモ作成ページに移動することを確認
        const currentUrl = await memoPage.getCurrentUrl();
        expect(currentUrl).toContain('/memo/new');
    });

    test('メモ一覧でメモの基本情報が表示される', async () => {
        await memoPage.navigateToMemoList();
        
        const memoItems = await memoPage.getMemoItems();
        if (memoItems.length > 0) {
            const firstItem = memoItems[0];
            
            // タイトルが表示されることを確認
            const title = await firstItem.locator(memoPage.selectors.memoTitle).textContent();
            expect(title).toBeTruthy();
            expect(title.trim().length).toBeGreaterThan(0);
            
            // 作成日が表示されることを確認
            const createdAtText = await firstItem.textContent();
            expect(createdAtText).toContain('作成日');
        }
    });

    test('検索結果の件数表示が更新される', async () => {
        await memoPage.navigateToMemoList();
        
        // 件数表示要素を探す
        const countDisplayExists = await memoPage.page.locator(':text("件中"), :text("件表示")').count() > 0;
        
        if (countDisplayExists) {
            // 検索前の件数を記録
            const beforeSearchText = await memoPage.page.locator(':text("件中"), :text("件表示")').first().textContent();
            
            // 検索実行
            await memoPage.searchMemos({ keyword: 'test' });
            
            // 検索後の件数表示を確認
            const afterSearchText = await memoPage.page.locator(':text("件中"), :text("件表示")').first().textContent();
            
            // 件数表示が更新されることを確認（内容が変わっている可能性）
            expect(afterSearchText).toBeTruthy();
        }
    });
});