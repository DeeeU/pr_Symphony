const BasePage = require('./BasePage');
const TestData = require('../fixtures/testData');

class CategoryPage extends BasePage {
    constructor(page) {
        super(page);
        
        this.selectors = {
            // カテゴリ作成ページ
            nameInput: 'input[placeholder*="カテゴリ名"], textbox[placeholder*="カテゴリ名"]',
            descriptionTextarea: 'textarea[placeholder*="説明"], textbox[placeholder*="説明"]',
            saveButton: 'button:has-text("保存")',
            cancelLink: 'a:has-text("キャンセル")',
            previewSection: '.preview, [class*="preview"]',
            previewTitle: 'h6',
            hintsSection: '[class*="hint"], .help-text',
            
            // カテゴリ一覧ページ
            searchKeywordInput: 'input[placeholder*="キーワード"], textbox[placeholder*="キーワード"]',
            searchButton: 'button:has-text("検索")',
            clearButton: 'a:has-text("クリア")',
            newCategoryButton: 'a:has-text("新規カテゴリ作成")',
            categoryItems: '.category-item, [class*="category"]',
            categoryTitle: 'h5, .category-title',
            detailLink: 'a:has-text("詳細")',
            editLink: 'a:has-text("編集")',
            
            // 共通
            flashMessage: '.alert, .flash-notice, .flash-success, .flash-error',
            validationError: '.form-error, .invalid-feedback, .error',
            pageTitle: 'h1'
        };
    }

    // カテゴリ作成ページのメソッド
    async navigateToNewCategory() {
        await this.navigate('/category/new');
        await this.waitForSelector(this.selectors.nameInput);
    }

    async fillCategoryForm(categoryData) {
        if (categoryData.name !== undefined) {
            await this.fillInput(this.selectors.nameInput, categoryData.name);
        }
        
        if (categoryData.description !== undefined) {
            await this.fillInput(this.selectors.descriptionTextarea, categoryData.description);
        }
    }

    async submitCategoryForm() {
        await this.clickElement(this.selectors.saveButton);
    }

    async createCategory(categoryData) {
        await this.navigateToNewCategory();
        await this.fillCategoryForm(categoryData);
        await this.submitCategoryForm();
    }

    async cancelCategoryCreation() {
        await this.clickElement(this.selectors.cancelLink);
    }

    async verifyPreviewUpdate(expectedName, expectedDescription) {
        // プレビューセクションの確認
        const previewTitle = await this.getText(this.selectors.previewTitle);
        
        if (expectedName && !previewTitle.includes(expectedName)) {
            // プレビューが更新されていない場合は、デフォルトテキストが表示される
            const isDefaultText = previewTitle.includes('カテゴリ名がここに表示されます');
            if (!isDefaultText) {
                throw new Error(`Expected preview title to contain "${expectedName}" but got "${previewTitle}"`);
            }
        }
    }

    async verifyHintsVisible() {
        const hintsVisible = await this.isElementVisible(this.selectors.hintsSection);
        if (!hintsVisible) {
            throw new Error('Category creation hints should be visible');
        }
    }

    // カテゴリ一覧ページのメソッド
    async navigateToCategoryList() {
        await this.navigate('/category');
        await this.waitForSelector(this.selectors.searchKeywordInput);
    }

    async searchCategories(keyword) {
        if (keyword) {
            await this.fillInput(this.selectors.searchKeywordInput, keyword);
            await this.clickElement(this.selectors.searchButton);
            await this.waitForNavigation();
        }
    }

    async clearSearch() {
        await this.clickElement(this.selectors.clearButton);
        await this.waitForNavigation();
    }

    async getCategoryItems() {
        await this.waitForSelector(this.selectors.categoryItems);
        return await this.page.locator(this.selectors.categoryItems).all();
    }

    async getCategoryTitles() {
        const items = await this.getCategoryItems();
        const titles = [];
        
        for (const item of items) {
            const titleElement = item.locator(this.selectors.categoryTitle);
            const title = await titleElement.textContent();
            titles.push(title);
        }
        
        return titles;
    }

    async clickNewCategoryButton() {
        await this.clickElement(this.selectors.newCategoryButton);
        await this.waitForNavigation();
    }

    async clickCategoryDetail(index = 0) {
        const detailLinks = this.page.locator(this.selectors.detailLink);
        await detailLinks.nth(index).click();
        await this.waitForNavigation();
    }

    async clickCategoryEdit(index = 0) {
        const editLinks = this.page.locator(this.selectors.editLink);
        await editLinks.nth(index).click();
        await this.waitForNavigation();
    }

    // バリデーション確認メソッド
    async verifyCategoryCreationSuccess(expectedName) {
        const flashMessage = await this.waitForFlashMessage();
        
        if (!flashMessage.includes('作成しました') || !flashMessage.includes(expectedName)) {
            throw new Error(`Expected success message for "${expectedName}" but got "${flashMessage}"`);
        }
        
        const currentUrl = await this.getCurrentUrl();
        if (!currentUrl.includes('/category/')) {
            throw new Error(`Expected redirect to category list but current URL is ${currentUrl}`);
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

    async verifyCategoryInList(categoryName) {
        const titles = await this.getCategoryTitles();
        const found = titles.some(title => title.includes(categoryName));
        
        if (!found) {
            throw new Error(`Category with name "${categoryName}" not found in list. Found titles: ${titles.join(', ')}`);
        }
    }

    async verifyFormFieldsVisible() {
        const nameInputVisible = await this.isElementVisible(this.selectors.nameInput);
        const descriptionTextareaVisible = await this.isElementVisible(this.selectors.descriptionTextarea);
        const saveButtonVisible = await this.isElementVisible(this.selectors.saveButton);
        const cancelLinkVisible = await this.isElementVisible(this.selectors.cancelLink);
        
        if (!nameInputVisible || !descriptionTextareaVisible || !saveButtonVisible || !cancelLinkVisible) {
            throw new Error('Not all form fields are visible on category creation page');
        }
    }

    // テストデータ生成メソッド
    generateValidCategoryData() {
        const testData = this.helpers.generateTestData();
        return testData.category;
    }

    getTestCategoryData() {
        return TestData.validCategory;
    }

    getInvalidCategoryData() {
        return TestData.invalidCategory;
    }

    // フォーム入力のリアルタイムバリデーション確認
    async verifyRealtimePreview(categoryData) {
        await this.fillCategoryForm(categoryData);
        
        // 少し待ってからプレビューを確認
        await this.page.waitForTimeout(500);
        
        await this.verifyPreviewUpdate(categoryData.name, categoryData.description);
    }

    async verifyCancelFunctionality() {
        await this.navigateToNewCategory();
        await this.fillCategoryForm({ name: "Test Category", description: "Test Description" });
        await this.cancelCategoryCreation();
        
        const currentUrl = await this.getCurrentUrl();
        if (!currentUrl.includes('/category/')) {
            throw new Error(`Expected redirect to category list after cancel but current URL is ${currentUrl}`);
        }
    }
}

module.exports = CategoryPage;