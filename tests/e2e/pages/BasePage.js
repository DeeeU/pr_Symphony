const TestHelpers = require('../utils/helpers');

class BasePage {
    constructor(page) {
        this.page = page;
        this.baseUrl = 'http://localhost:8001';
        this.helpers = TestHelpers;
    }

    async navigate(path = '') {
        const url = `${this.baseUrl}${path}`;
        await this.page.goto(url);
        await this.helpers.waitForPageLoad(this.page);
    }

    async waitForSelector(selector, timeout = 5000) {
        return await this.page.waitForSelector(selector, { timeout });
    }

    async clickElement(selector) {
        await this.page.click(selector);
    }

    async fillInput(selector, value) {
        await this.page.fill(selector, value);
    }

    async selectOption(selector, value) {
        await this.page.selectOption(selector, value);
    }

    async submitForm(formSelector = 'form') {
        await this.page.locator(`${formSelector} button[type="submit"], ${formSelector} input[type="submit"]`).first().click();
    }

    async getPageTitle() {
        return await this.page.title();
    }

    async getCurrentUrl() {
        return this.page.url();
    }

    async getText(selector) {
        return await this.page.textContent(selector);
    }

    async isElementVisible(selector) {
        try {
            return await this.page.isVisible(selector);
        } catch {
            return false;
        }
    }

    async waitForFlashMessage(expectedMessage = null) {
        return await this.helpers.waitForFlashMessage(this.page, expectedMessage);
    }

    async checkValidationErrors(expectedErrors = []) {
        return await this.helpers.checkValidationErrors(this.page, expectedErrors);
    }

    async takeScreenshot(filename) {
        await this.page.screenshot({ 
            path: `tests/e2e/screenshots/${filename}`, 
            fullPage: true 
        });
    }

    async waitForNavigation(expectedUrl = null) {
        if (expectedUrl) {
            await this.page.waitForURL(expectedUrl);
        } else {
            await this.page.waitForLoadState('networkidle');
        }
    }
}

module.exports = BasePage;