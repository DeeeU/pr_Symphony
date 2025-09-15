class TestHelpers {
    static async waitForFlashMessage(page, expectedMessage, timeout = 10000) {
        try {
            const flashSelector = '.alert, .flash-notice, .flash-success, .flash-error';
            await page.waitForSelector(flashSelector, { timeout });
            const flashText = await page.textContent(flashSelector);
            
            if (expectedMessage && !flashText.includes(expectedMessage)) {
                throw new Error(`Expected flash message "${expectedMessage}" but got "${flashText}"`);
            }
            
            return flashText;
        } catch (error) {
            throw new Error(`Flash message not found or incorrect: ${error.message}`);
        }
    }

    static async waitForPageLoad(page, expectedUrl = null, timeout = 10000) {
        await page.waitForLoadState('networkidle', { timeout });
        
        if (expectedUrl) {
            await page.waitForURL(expectedUrl, { timeout });
        }
    }

    static async checkValidationErrors(page, expectedErrors = []) {
        const errorSelector = '.form-error, .invalid-feedback, .error';
        const errors = await page.locator(errorSelector).allTextContents();
        
        if (expectedErrors.length > 0) {
            for (const expectedError of expectedErrors) {
                const found = errors.some(error => error.includes(expectedError));
                if (!found) {
                    throw new Error(`Expected validation error "${expectedError}" not found. Found: ${errors.join(', ')}`);
                }
            }
        }
        
        return errors;
    }

    static async takeScreenshotOnFailure(page, testName) {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        const filename = `failure-${testName}-${timestamp}.png`;
        await page.screenshot({ 
            path: `tests/e2e/screenshots/${filename}`, 
            fullPage: true 
        });
        console.log(`Screenshot saved: ${filename}`);
    }

    static generateTestData() {
        const timestamp = Date.now();
        return {
            memo: {
                title: `テストメモ ${timestamp}`,
                content: `これはテスト用のメモ内容です。作成日時: ${new Date().toLocaleString()}`
            },
            category: {
                name: `テストカテゴリ ${timestamp}`,
                description: `テスト用のカテゴリ説明 ${timestamp}`
            }
        };
    }

    static async clearForm(page, formSelector = 'form') {
        const inputs = await page.locator(`${formSelector} input[type="text"], ${formSelector} textarea`).all();
        for (const input of inputs) {
            await input.fill('');
        }
    }

    static async getPageTitle(page) {
        return await page.title();
    }

    static async getCurrentUrl(page) {
        return page.url();
    }
}

module.exports = TestHelpers;