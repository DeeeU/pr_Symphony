// @ts-check
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/e2e',
  
  // テストファイルのパターン
  testMatch: [
    '**/*.spec.js',
    '**/*.test.js'
  ],

  // 並列実行の設定
  fullyParallel: true,
  
  // CI環境での失敗時の動作
  forbidOnly: !!process.env.CI,
  
  // CI環境でのリトライ設定
  retries: process.env.CI ? 2 : 0,
  
  // ワーカー数の設定
  workers: process.env.CI ? 1 : undefined,
  
  // レポーター設定
  reporter: [
    ['html', { outputFolder: 'tests/e2e/reports' }],
    ['json', { outputFile: 'tests/e2e/reports/results.json' }],
    ['list']
  ],
  
  // グローバル設定
  use: {
    // ベースURL
    baseURL: 'http://localhost:8001',
    
    // ブラウザ設定
    headless: true,
    
    // トレース設定（失敗時のみ）
    trace: 'on-first-retry',
    
    // スクリーンショット設定（失敗時のみ）
    screenshot: 'only-on-failure',
    
    // ビデオ録画設定（失敗時のみ）
    video: 'retain-on-failure',
    
    // タイムアウト設定
    actionTimeout: 10 * 1000,
    navigationTimeout: 30 * 1000,
    
    // ロケール設定
    locale: 'ja-JP',
    timezoneId: 'Asia/Tokyo',
  },

  // プロジェクト設定（複数のブラウザでテスト）
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    // 必要に応じて他のブラウザも追加
    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },
    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],

  // テスト実行前のセットアップ（必要に応じて）
  // globalSetup: require.resolve('./tests/e2e/utils/globalSetup.js'),
  
  // テスト実行後のクリーンアップ（必要に応じて）
  // globalTeardown: require.resolve('./tests/e2e/utils/globalTeardown.js'),
  
  // Webサーバー設定（必要に応じて）
  // webServer: {
  //   command: 'php bin/console server:run localhost:8001',
  //   port: 8001,
  //   timeout: 120 * 1000,
  //   reuseExistingServer: !process.env.CI,
  // },
});