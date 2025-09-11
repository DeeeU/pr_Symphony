# E2E テストスイート

このディレクトリには、Symfony メモアプリケーションの E2E テストが含まれています。

## 概要

Microsoft Playwright MCP を使用して、以下の機能をテストしています：

- メモ作成機能 (`/memo/new`)
- メモ一覧表示 (`/memo`)
- カテゴリ作成機能 (`/category/new`)

## ディレクトリ構造

```
tests/e2e/
├── pages/                    # Page Object Model
│   ├── BasePage.js          # 基底ページクラス
│   ├── MemoPage.js          # メモ関連のページオブジェクト
│   └── CategoryPage.js      # カテゴリ関連のページオブジェクト
├── fixtures/                 # テストデータ
│   └── testData.js          # 共通テストデータ定義
├── utils/                    # ヘルパー関数
│   └── helpers.js           # テスト用ユーティリティ関数
├── MemoManagement/           # メモ管理機能のテスト
│   ├── CreateMemo.spec.js   # メモ作成テスト
│   └── ListMemos.spec.js    # メモ一覧テスト
├── CategoryManagement/       # カテゴリ管理機能のテスト
│   └── CreateCategory.spec.js # カテゴリ作成テスト
├── screenshots/             # 失敗時のスクリーンショット
└── reports/                 # テストレポート出力先
```

## テスト対象機能

### 1. メモ作成機能 (`CreateMemo.spec.js`)

- ✅ 有効なデータでのメモ作成
- ✅ バリデーションエラーの確認（空のタイトル・内容）
- ✅ カテゴリ選択機能
- ✅ 長すぎるタイトルのエラー処理
- ✅ キャンセル機能
- ✅ フラッシュメッセージ表示
- ✅ 成功時のリダイレクト

### 2. メモ一覧表示 (`ListMemos.spec.js`)

- ✅ メモ一覧の基本表示
- ✅ キーワード検索機能
- ✅ 日付範囲検索機能
- ✅ 検索結果クリア機能
- ✅ ページネーション
- ✅ 詳細・編集リンク
- ✅ 新規メモ作成ボタン

### 3. カテゴリ作成機能 (`CreateCategory.spec.js`)

- ✅ 有効なデータでのカテゴリ作成
- ✅ バリデーションエラーの確認
- ✅ プレビュー機能
- ✅ ヒント表示
- ✅ キャンセル機能
- ✅ フォーム要素の表示確認
- ✅ 入力制限の確認

## 実行方法

### 前提条件

1. Symfony アプリケーションが `http://localhost:8001` で実行されている
2. Microsoft Playwright MCP が利用可能
3. テスト用のデータベースが適切に設定されている

### テスト実行

```bash
# 全てのテストを実行
npx playwright test

# 特定の機能のテストのみ実行
npx playwright test tests/e2e/MemoManagement/
npx playwright test tests/e2e/CategoryManagement/

# 特定のテストファイルを実行
npx playwright test tests/e2e/MemoManagement/CreateMemo.spec.js

# ヘッドレスモードを無効化（ブラウザ画面を表示）
npx playwright test --headed

# デバッグモード
npx playwright test --debug
```

### レポート表示

```bash
# HTMLレポートを表示
npx playwright show-report tests/e2e/reports
```

## Page Object Model パターン

このテストスイートは、保守性を向上させるために Page Object Model パターンを採用しています。

### BasePage.js

- 全ページ共通の基本機能を提供
- ナビゲーション、要素の操作、検証メソッドなど

### MemoPage.js

- メモ関連ページの操作を抽象化
- メモ作成、一覧表示、検索機能のメソッド

### CategoryPage.js

- カテゴリ関連ページの操作を抽象化
- カテゴリ作成、プレビュー機能のメソッド

## テストデータ管理

`fixtures/testData.js` で共通のテストデータを管理：

- 有効・無効なメモデータ
- 有効・無効なカテゴリデータ
- 検索キーワード
- 期待されるメッセージ
- URL 定義

## ユーティリティ関数

`utils/helpers.js` で共通のヘルパー関数を提供：

- フラッシュメッセージの待機・確認
- バリデーションエラーの確認
- スクリーンショット撮影
- テストデータ生成
- フォームクリア

## エラーハンドリング

- テスト失敗時の自動スクリーンショット撮影
- 詳細なエラーメッセージの出力
- タイムアウト設定によるテストの安定化

## 注意事項

- テスト実行前に、アプリケーションサーバーが起動していることを確認してください
- テスト用のデータベースを使用し、本番データを破損しないよう注意してください
- テスト間でのデータの独立性を保つため、必要に応じてクリーンアップ処理を実装してください