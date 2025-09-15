const BasePage = require('./BasePage');
const TestData = require('../fixtures/testData');

class MemoPage extends BasePage {
    constructor(page) {
        super(page);
        
        this.selectors = {
            // メモ作成ページ
            titleInput: 'input[placeholder*="メモのタイトル"], textbox[placeholder*="メモのタイトル"]',
            contentTextarea: 'textarea[placeholder*="メモの内容"], textbox[placeholder*="メモの内容"]',
            categorySelect: 'select, combobox',
            saveButton: 'button:has-text("保存")',
            cancelLink: 'a:has-text("キャンセル")',
            
            // メモ一覧ページ
            searchKeywordInput: 'input[placeholder*="タイトルまたは内容"], textbox[placeholder*="タイトルまたは内容"]',
            startDateInput: 'input[type="date"], textbox:near(:text("開始日"))',
            endDateInput: 'input[type="date"], textbox:near(:text("終了日"))',
            searchButton: 'button:has-text("検索")',
            clearButton: 'a:has-text("クリア")',
            newMemoButton: 'a:has-text("新規メモ作成")',
            memoItems: 'h5:contains("テストメモ"), .memo-row, tr:has(h5)',
            memoTitle: 'h5, .memo-title',
            detailLink: 'a:has-text("詳細")',
            editLink: 'a:has-text("編集")',
            
            // 共通
            flashMessage: '.alert, .flash-notice, .flash-success, .flash-error',
            validationError: '.form-error, .invalid-feedback, .error',
            pageTitle: 'h1'
        };
    }

    // メモ作成ページのメソッド
    async navigateToNewMemo() {
        await this.navigate('/memo/new');
        await this.waitForSelector(this.selectors.titleInput);
    }

    async fillMemoForm(memoData) {
        if (memoData.title !== undefined) {
            await this.fillInput(this.selectors.titleInput, memoData.title);
        }
        
        if (memoData.content !== undefined) {
            await this.fillInput(this.selectors.contentTextarea, memoData.content);
        }
        
        if (memoData.category) {
            await this.selectOption(this.selectors.categorySelect, memoData.category);
        }
    }

    async submitMemoForm() {
        await this.clickElement(this.selectors.saveButton);
    }

    async createMemo(memoData) {
        await this.navigateToNewMemo();
        await this.fillMemoForm(memoData);
        await this.submitMemoForm();
    }

    async cancelMemoCreation() {
        await this.clickElement(this.selectors.cancelLink);
    }

    // メモ一覧ページのメソッド
    async navigateToMemoList() {
        await this.navigate('/memo');
        await this.waitForSelector(this.selectors.newMemoButton);
    }

    async searchMemos(searchCriteria) {
        if (searchCriteria.keyword) {
            await this.fillInput(this.selectors.searchKeywordInput, searchCriteria.keyword);
        }
        
        if (searchCriteria.startDate) {
            await this.fillInput(this.selectors.startDateInput, searchCriteria.startDate);
        }
        
        if (searchCriteria.endDate) {
            await this.fillInput(this.selectors.endDateInput, searchCriteria.endDate);
        }
        
        await this.clickElement(this.selectors.searchButton);
        await this.waitForNavigation();
    }

    async clearSearch() {
        await this.clickElement(this.selectors.clearButton);
        await this.waitForNavigation();
    }

    async getMemoItems() {
        await this.waitForSelector(this.selectors.memoItems);
        return await this.page.locator(this.selectors.memoItems).all();
    }

    async getMemoTitles() {
        const items = await this.getMemoItems();
        const titles = [];
        
        for (const item of items) {
            const titleElement = item.locator(this.selectors.memoTitle);
            const title = await titleElement.textContent();
            titles.push(title);
        }
        
        return titles;
    }

    async clickNewMemoButton() {
        await this.clickElement(this.selectors.newMemoButton);
        await this.waitForNavigation();
    }

    async clickMemoDetail(index = 0) {
        const detailLinks = this.page.locator(this.selectors.detailLink);
        await detailLinks.nth(index).click();
        await this.waitForNavigation();
    }

    async clickMemoEdit(index = 0) {
        const editLinks = this.page.locator(this.selectors.editLink);
        await editLinks.nth(index).click();
        await this.waitForNavigation();
    }

    // バリデーション確認メソッド
    async verifyMemoCreationSuccess(expectedTitle) {
        const flashMessage = await this.waitForFlashMessage();
        
        if (!flashMessage.includes('作成しました') || !flashMessage.includes(expectedTitle)) {
            throw new Error(`Expected success message for "${expectedTitle}" but got "${flashMessage}"`);
        }
        
        const currentUrl = await this.getCurrentUrl();
        if (!currentUrl.includes('/memo/')) {
            throw new Error(`Expected redirect to memo list but current URL is ${currentUrl}`);
        }
    }

    async verifyValidationErrors(expectedErrors = []) {
        const errors = await this.checkValidationErrors(expectedErrors);
        return errors;
    }

    async verifyPageTitle(expectedTitle) {
        const pageTitle = await this.getText(this.selectors.pageTitle);
        if (pageTitle !== expectedTitle) {
            throw new Error(`Expected page title "${expectedTitle}" but got "${pageTitle}"`);
        }
    }

    async verifyMemoInList(memoTitle) {
        const titles = await this.getMemoTitles();
        const found = titles.some(title => title.includes(memoTitle));
        
        if (!found) {
            throw new Error(`Memo with title "${memoTitle}" not found in list. Found titles: ${titles.join(', ')}`);
        }
    }

    // テストデータ生成メソッド
    generateValidMemoData() {
        const testData = this.helpers.generateTestData();
        return testData.memo;
    }

    getTestMemoData() {
        return TestData.validMemo;
    }

    getInvalidMemoData() {
        return TestData.invalidMemo;
    }
}

module.exports = MemoPage;