# Symfony メモアプリケーション（pr_Symphony）

## プロジェクト概要

このプロジェクトは、Symfony 3.4フレームワークを使用して構築されたメモ管理アプリケーションです。
メモをカテゴリ別に整理・管理し、検索やページネーション機能を提供します。

## システム要件

- PHP >= 7.2
- Symfony 3.4.*
- MySQL/MariaDB
- Composer

## アーキテクチャ概要

### MVC構造（Railsとの比較）

| **Symfony** | **Rails** | **説明** |
|------------|-----------|---------|
| Entity | Model | データベースとのマッピングを行うモデル層 |
| Repository | Model（クエリスコープ） | カスタムクエリロジック |
| Controller | Controller | HTTPリクエストを処理 |
| Twig Templates | View | プレゼンテーション層 |
| Form Types | Form Objects | フォーム処理とバリデーション |

### エンティティ関係図

```
Category (1) ←→ (n) Memo
├── id (PK)              ├── id (PK)
├── name                 ├── title
├── description          ├── content
├── color                ├── createdAt
├── createdAt            └── category (FK)
└── memos (Collection)
```

## 主要コンポーネント

### 1. エンティティ（Models）

#### Category エンティティ
- **場所**: `src/AppBundle/Entity/Category.php`
- **機能**: メモのカテゴリ分類
- **重要なメソッド**:
  - `getMemoCount()`: 関連するメモの数を取得
  - `addMemo()` / `removeMemo()`: メモとの関連を管理
- **バリデーション**:
  - カテゴリ名: 必須、100文字以内
  - 説明文: 任意、1000文字以内

#### Memo エンティティ
- **場所**: `src/AppBundle/Entity/Memo.php`
- **機能**: メモの内容を管理
- **関連**: ManyToOne で Category と関連

### 2. リポジトリ（Data Access Layer）

#### CategoryRepository
- **場所**: `src/AppBundle/Repository/CategoryRepository.php`
- **主要メソッド**:
  - `createSearchQueryBuilder()`: 検索機能付きクエリビルダー
  - `findByNameExcluding()`: 特定IDを除外した名前検索
  - `getCategoriesWithMemoCount()`: メモ数付きカテゴリ一覧
  - `findActiveCategories()`: メモが存在するカテゴリのみ取得

> **Rails比較**: Railsのscopeやクラスメソッドに相当する機能

### 3. コントローラー（Controller Layer）

#### MemoController
- **場所**: `src/AppBundle/Controller/MemoController.php`
- **ルーティング**: `/memo` 配下
- **主要アクション**:
  - `indexAction()`: 一覧表示（検索・ページネーション対応）
  - `newAction()`: 新規作成
  - `editAction()`: 編集

### 4. フォーム（Form Layer）

#### CategoryType
- **場所**: `src/AppBundle/Form/CategoryType.php`
- **機能**: カテゴリのフォーム処理
- **フィールド**:
  - name: テキスト入力
  - description: テキストエリア
- **セキュリティ**: CSRF保護有効

> **Rails比較**: Rails の Strong Parameters + Form Objects の役割

## テスト構成

### テストの網羅性

#### 1. エンティティテスト
- **ファイル**: `tests/AppBundle/Entity/CategoryTest.php`
- **カバレッジ**:
  - ✅ インスタンス生成
  - ✅ プロパティの設定・取得
  - ✅ デフォルト値の検証
  - ✅ Fluent Interface（メソッドチェーン）

#### 2. リポジトリテスト
- **ファイル**: `tests/AppBundle/Repository/CategoryRepositoryTest.php`
- **カバレッジ**:
  - ✅ CRUD操作
  - ✅ カスタムクエリメソッド
  - ✅ データベースクリーンアップ

#### 3. フォームテスト
- **ファイル**: `tests/AppBundle/Form/CategoryTypeTest.php`
- **カバレッジ**:
  - ✅ 正常データの送信
  - ✅ バリデーションエラーケース
  - ✅ フォームフィールドの存在確認

#### 4. コントローラーテスト
- **ファイル**: `tests/AppBundle/Controller/DefaultControllerTest.php`
- **カバレッジ**:
  - ✅ 基本的なHTTPレスポンス

### テストコマンド（composer.json）

```bash
# 全テスト実行
composer test

# カテゴリ別テスト実行
composer test-unit        # エンティティテスト
composer test-repo        # リポジトリテスト
composer test-controller  # コントローラーテスト

# カバレッジ測定
composer test-coverage
```

## 使用技術・ライブラリ

### 主要バンドル

| **バンドル** | **役割** | **Rails相当** |
|-------------|---------|---------------|
| DoctrineBundle | ORM/データベース | ActiveRecord |
| TwigBundle | テンプレートエンジン | ERB/Haml |
| SecurityBundle | 認証・認可 | Devise |
| SwiftmailerBundle | メール送信 | ActionMailer |
| MonologBundle | ログ管理 | Rails.logger |
| KnpPaginatorBundle | ページネーション | Kaminari |

### 開発・テスト用バンドル

- **WebProfilerBundle**: デバッグツールバー（Rails の debug gem 相当）
- **PHPUnit**: テストフレームワーク（RSpec 相当）
- **SensioGeneratorBundle**: コード生成（Rails の generator 相当）

## 現在の課題と改善点

### 🚨 緊急対応が必要

1. **Memo.php の setCategory() メソッド**
   - 引数が欠落している（`$category` パラメータがない）
   - テストが失敗する原因

### ⚠️ 推奨改善事項

1. **テストカバレッジの拡充**
   - MemoController のテストが不完全
   - MemoRepository のテスト未実装
   - 統合テストの追加

2. **セキュリティ強化**
   - CSRF保護の全フォームへの適用
   - 入力サニタイゼーションの強化

3. **パフォーマンス最適化**
   - N+1問題の対策
   - クエリの最適化

### 📝 今後追加予定の機能

1. **ユーザー認証システム**
   - SecurityBundle を使用した認証機能
   - ユーザー別メモ管理

2. **メモ検索機能の拡張**
   - 全文検索の実装
   - タグ機能

3. **API機能**
   - REST API の追加
   - JSON レスポンス対応

4. **フロントエンド強化**
   - JavaScript による非同期処理
   - リアルタイム更新

## 開発・デバッグ用コマンド

```bash
# データベース操作（テスト環境）
composer db-create-test    # テストDB作成
composer db-update-test    # スキーマ更新
composer db-drop-test      # テストDB削除

# キャッシュクリア
composer cache-clear-test

# 詳細テスト実行
composer test-verbose
```

## 学習ポイント（Rails経験者向け）

### 1. Doctrine ORM vs ActiveRecord

```php
// Symfony/Doctrine
$category = new Category();
$category->setName('プログラミング');
$em->persist($category);
$em->flush();
```

```ruby
# Rails/ActiveRecord
category = Category.new(name: 'プログラミング')
category.save!
```

### 2. Repository Pattern

Symfonyでは、複雑なクエリはRepositoryクラスに分離します（Railsのscopeに相当）：

```php
// Symfony Repository
public function findActiveCategories() {
    return $this->createQueryBuilder('c')
                ->leftJoin('c.memos', 'm')
                ->having('count(m.id) > 0')
                ->getQuery()
                ->getResult();
}
```

### 3. Form Types

RailsのStrong Parametersとform_withを合わせた機能：

```php
// Symfony Form Type
$builder->add('name', TextType::class, [
    'label' => 'カテゴリ名',
    'attr' => ['class' => 'form-control']
]);
```

---

## 元の英語版README

Symfony Standard Edition
========================

**WARNING**: This distribution does not support Symfony 4. See the
[Installing & Setting up the Symfony Framework][15] page to find a replacement
that fits you best.

Welcome to the Symfony Standard Edition - a fully-functional Symfony
application that you can use as the skeleton for your new applications.

For details on how to download and get started with Symfony, see the
[Installation][1] chapter of the Symfony Documentation.

What's inside?
--------------

The Symfony Standard Edition is configured with the following defaults:

  * An AppBundle you can use to start coding;

  * Twig as the only configured template engine;

  * Doctrine ORM/DBAL;

  * Swiftmailer;

  * Annotations enabled for everything.

It comes pre-configured with the following bundles:

  * **FrameworkBundle** - The core Symfony framework bundle

  * [**SensioFrameworkExtraBundle**][6] - Adds several enhancements, including
    template and routing annotation capability

  * [**DoctrineBundle**][7] - Adds support for the Doctrine ORM

  * [**TwigBundle**][8] - Adds support for the Twig templating engine

  * [**SecurityBundle**][9] - Adds security by integrating Symfony's security
    component

  * [**SwiftmailerBundle**][10] - Adds support for Swiftmailer, a library for
    sending emails

  * [**MonologBundle**][11] - Adds support for Monolog, a logging library

  * **WebProfilerBundle** (in dev/test env) - Adds profiling functionality and
    the web debug toolbar

  * **SensioDistributionBundle** (in dev/test env) - Adds functionality for
    configuring and working with Symfony distributions

  * [**SensioGeneratorBundle**][13] (in dev env) - Adds code generation
    capabilities

  * [**WebServerBundle**][14] (in dev env) - Adds commands for running applications
    using the PHP built-in web server

  * **DebugBundle** (in dev/test env) - Adds Debug and VarDumper component
    integration

All libraries and bundles included in the Symfony Standard Edition are
released under the MIT or BSD license.

Enjoy!

[1]:  https://symfony.com/doc/3.4/setup.html
[6]:  https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
[7]:  https://symfony.com/doc/3.4/doctrine.html
[8]:  https://symfony.com/doc/3.4/templating.html
[9]:  https://symfony.com/doc/3.4/security.html
[10]: https://symfony.com/doc/3.4/email.html
[11]: https://symfony.com/doc/3.4/logging.html
[13]: https://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html
[14]: https://symfony.com/doc/current/setup/built_in_web_server.html
[15]: https://symfony.com/doc/current/setup.html

Symfony Standard Edition
========================

**WARNING**: This distribution does not support Symfony 4. See the
[Installing & Setting up the Symfony Framework][15] page to find a replacement
that fits you best.

Welcome to the Symfony Standard Edition - a fully-functional Symfony
application that you can use as the skeleton for your new applications.

For details on how to download and get started with Symfony, see the
[Installation][1] chapter of the Symfony Documentation.

What's inside?
--------------

The Symfony Standard Edition is configured with the following defaults:

  * An AppBundle you can use to start coding;

  * Twig as the only configured template engine;

  * Doctrine ORM/DBAL;

  * Swiftmailer;

  * Annotations enabled for everything.

It comes pre-configured with the following bundles:

  * **FrameworkBundle** - The core Symfony framework bundle

  * [**SensioFrameworkExtraBundle**][6] - Adds several enhancements, including
    template and routing annotation capability

  * [**DoctrineBundle**][7] - Adds support for the Doctrine ORM

  * [**TwigBundle**][8] - Adds support for the Twig templating engine

  * [**SecurityBundle**][9] - Adds security by integrating Symfony's security
    component

  * [**SwiftmailerBundle**][10] - Adds support for Swiftmailer, a library for
    sending emails

  * [**MonologBundle**][11] - Adds support for Monolog, a logging library

  * **WebProfilerBundle** (in dev/test env) - Adds profiling functionality and
    the web debug toolbar

  * **SensioDistributionBundle** (in dev/test env) - Adds functionality for
    configuring and working with Symfony distributions

  * [**SensioGeneratorBundle**][13] (in dev env) - Adds code generation
    capabilities

  * [**WebServerBundle**][14] (in dev env) - Adds commands for running applications
    using the PHP built-in web server

  * **DebugBundle** (in dev/test env) - Adds Debug and VarDumper component
    integration

All libraries and bundles included in the Symfony Standard Edition are
released under the MIT or BSD license.

Enjoy!

[1]:  https://symfony.com/doc/3.4/setup.html
[6]:  https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
[7]:  https://symfony.com/doc/3.4/doctrine.html
[8]:  https://symfony.com/doc/3.4/templating.html
[9]:  https://symfony.com/doc/3.4/security.html
[10]: https://symfony.com/doc/3.4/email.html
[11]: https://symfony.com/doc/3.4/logging.html
[13]: https://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html
[14]: https://symfony.com/doc/current/setup/built_in_web_server.html
[15]: https://symfony.com/doc/current/setup.html
