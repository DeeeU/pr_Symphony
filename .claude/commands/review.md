# Symfony専用 差分レビュー指示書

---
**対応プロジェクト**: `pr_Symphony` (PHP/Symfony 3.4 + JavaScript)
**目的**: タイポ、コードフォーマット、より良い記載方法の確認

---

## コンテキスト
- **差分URL**: {{ diff_url }}
- **対象リポジトリ**: `pr_Symphony`
- **技術スタック**: PHP 7.2+、Symfony 3.4、JavaScript/jQuery

## レビュー実施手順

### Step 1: 基本情報収集

#### 変更ファイルの特定
```bash
git diff HEAD~1 --name-only
git status
```

#### Symfony専用チェックポイント
```bash
# PHP構文チェック
find src/ -name "*.php" -exec php -l {} \;

# Composer依存関係確認
composer validate
composer outdated

# キャッシュクリア（構文エラー回避）
php bin/console cache:clear --env=dev
```

---

## 🚨 **MUST** セクション（必須修正）

### PHP/Symfony 構文・フォーマットエラー

#### 1. PHP構文エラー
- [ ] **セミコロン漏れ**: 文末のセミコロン確認
  ```php
  // 悪い例（構文エラー）
  $category = new Category()  // ← セミコロン漏れ

  // 良い例
  $category = new Category();
  ```

- [ ] **クラス名・メソッド名のタイポ**: PSR-4準拠確認
  ```php
  // 悪い例（タイポ）
  class CatagoryController  // ← Category の誤字
  public function cretaeAction()  // ← create の誤字

  // 良い例
  class CategoryController
  public function createAction()
  ```

- [ ] **名前空間の誤記**: useステートメントとクラス名の一致
  ```php
  // 悪い例
  use AppBundle\Entity\Catagory;  // ← タイポ
  $category = new Category();

  // 良い例
  use AppBundle\Entity\Category;
  $category = new Category();
  ```

#### 2. Doctrine エンティティ定義エラー
- [ ] **アノテーション構文**: 正確な記述確認
  ```php
  // 悪い例（構文エラー）
  /**
   * @ORM\Column(type="string", lenght=255)  // ← length の誤字
   */

  // 良い例
  /**
   * @ORM\Column(type="string", length=255)
   */
  ```

- [ ] **メソッド引数の欠落**: setter/getterの引数確認
  ```php
  // 悪い例（引数漏れ）
  public function setName()  // ← 引数がない
  {
      $this->name = $name;  // ← $nameが未定義
  }

  // 良い例
  public function setName($name)
  {
      $this->name = $name;
  }
  ```

#### 3. コントローラーアクション定義エラー
- [ ] **ルーティングアノテーション**: 正確な記述
  ```php
  // 悪い例（パス記述ミス）
  /**
   * @Route("/memo//edit/{id}", name="memo_edit")  // ← 二重スラッシュ
   */

  // 良い例
  /**
   * @Route("/memo/edit/{id}", name="memo_edit")
   */
  ```

### JavaScript フォーマットエラー

#### 1. 基本構文エラー
- [ ] **セミコロン統一**: 文末セミコロンの一貫性
  ```javascript
  // 悪い例（不統一）
  var category = 'プログラミング'  // セミコロンなし
  var memo = 'Symfony学習';        // セミコロンあり

  // 良い例（統一）
  var category = 'プログラミング';
  var memo = 'Symfony学習';
  ```

- [ ] **クォート統一**: シングル・ダブルクォートの統一
  ```javascript
  // 悪い例（不統一）
  var title = "メモタイトル";
  var content = 'メモ内容';

  // 良い例（シングルクォート統一推奨）
  var title = 'メモタイトル';
  var content = 'メモ内容';
  ```

---

## 💡 **IMO** セクション（改善提案）

### PHP コード品質向上

#### 1. PSR-12 準拠の改善提案
- **インデント統一**: スペース4個での統一
  ```php
  // 改善前（タブやスペース2個）
  class CategoryController
  {
    public function indexAction()
    {
      $categories = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findAll();
    }
  }

  // 改善後（スペース4個統一）
  class CategoryController
  {
      public function indexAction()
      {
          $categories = $this->getDoctrine()
              ->getRepository(Category::class)
              ->findAll();
      }
  }
  ```

- **メソッドチェーンの改行**: 読みやすい改行位置
  ```php
  // 改善前
  $qb = $this->createQueryBuilder('c')->leftJoin('c.memos', 'm')->where('c.name LIKE :search')->setParameter('search', '%' . $search . '%')->orderBy('c.createdAt', 'DESC');

  // 改善後
  $qb = $this->createQueryBuilder('c')
      ->leftJoin('c.memos', 'm')
      ->where('c.name LIKE :search')
      ->setParameter('search', '%' . $search . '%')
      ->orderBy('c.createdAt', 'DESC');
  ```

#### 2. Symfony ベストプラクティス
- **サービス注入**: コンストラクタインジェクション推奨
  ```php
  // 改善前（サービスロケーター）
  public function indexAction()
  {
      $em = $this->getDoctrine()->getManager();
      $repository = $em->getRepository(Category::class);
  }

  // 改善後（依存注入）
  public function __construct(CategoryRepository $categoryRepository)
  {
      $this->categoryRepository = $categoryRepository;
  }

  public function indexAction()
  {
      $categories = $this->categoryRepository->findAll();
  }
  ```

### JavaScript 改善提案

#### 1. モダンなJavaScript記法
- **constとlet**: varの代わりに使用
  ```javascript
  // 改善前
  var categoryId = document.getElementById('category-select').value;
  var memoTitle = document.querySelector('#memo-title').value;

  // 改善後
  const categoryId = document.getElementById('category-select').value;
  let memoTitle = document.querySelector('#memo-title').value;  // 変更予定あり
  ```

- **テンプレートリテラル**: 文字列結合の改善
  ```javascript
  // 改善前
  var url = '/memo/' + memoId + '/edit';
  var message = 'メモ「' + title + '」を削除しますか？';

  // 改善後
  const url = `/memo/${memoId}/edit`;
  const message = `メモ「${title}」を削除しますか？`;
  ```

---

## 🔧 **NITS** セクション（軽微な改善）

### PHP 細かなフォーマット調整

#### 1. 配列記法の統一
```php
// 改善前（mixed記法）
$options = array(
    'required' => false,
    'empty_data' => null
);
$attributes = ['class' => 'form-control'];  // 短縮記法と混在

// 改善後（短縮記法で統一）
$options = [
    'required' => false,
    'empty_data' => null,
];
$attributes = ['class' => 'form-control'];
```

#### 2. 文字列エスケープの最適化
```php
// 改善前
$message = "カテゴリ「" . $category->getName() . "」を削除しますか？";

// 改善後（Twigで処理推奨）
// Controllerは最小限に
$message = 'confirm_delete_category';
// Twig: {{ 'confirm_delete_category'|trans({'%name%': category.name}) }}
```

### JavaScript 細かな調整

#### 1. イベントハンドリングの改善
```javascript
// 改善前（インライン）
<button onclick="deleteCategory(123)">削除</button>

// 改善後（分離）
<button class="delete-btn" data-category-id="123">削除</button>

// JavaScript
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const categoryId = this.dataset.categoryId;
        deleteCategory(categoryId);
    });
});
```

#### 2. DOM操作の最適化
```javascript
// 改善前（jQuery風味）
$('#memo-form').submit(function() {
    var title = $('#memo-title').val();
    if (title === '') {
        alert('タイトルを入力してください');
        return false;
    }
});

// 改善後（Vanilla JS）
document.getElementById('memo-form').addEventListener('submit', function(e) {
    const title = document.getElementById('memo-title').value.trim();
    if (!title) {
        alert('タイトルを入力してください');
        e.preventDefault();
        return;
    }
});
```

---

## ❓ **ASK** セクション（質問・確認）

### 技術仕様の確認

#### PHP/Symfony関連
- **PHPバージョン**: 7.2固定での開発継続か？（7.4+への移行予定は？）
- **Symfony 3.4**: LTS終了（2021年11月）後の移行計画は？
- **Doctrine ORM**: パフォーマンス要件に対してのクエリ最適化方針は？

#### JavaScript関連
- **ライブラリ選択**: jQuery継続かVanilla JSへの移行か？
- **フロントエンドビルド**: Webpack/Gulp等の導入予定は？
- **ES6+機能**: どこまでのモダン記法を採用するか？

#### 開発フロー関連
- **コードフォーマッター**: PHP CS Fixer導入の検討は？
- **静的解析**: PHPStan/Psalmer等の導入予定は？
- **JSLint/ESLint**: JavaScript品質チェックツールの採用は？

---

## 📋 レビューチェックリスト

### PHP/Symfony 専用チェック項目

#### 構文・フォーマット確認
- [ ] PHP構文エラーなし（`php -l` チェック済み）
- [ ] PSR-4 名前空間規約準拠
- [ ] PSR-12 コーディングスタイル準拠
- [ ] Symfony 3.4 アノテーション記法正確性
- [ ] Doctrine エンティティ定義の妥当性

#### 命名規則・タイポ確認
- [ ] クラス名：PascalCase（例：`CategoryController`）
- [ ] メソッド名：camelCase（例：`createAction`）
- [ ] プロパティ名：camelCase（例：`$createdAt`）
- [ ] 定数名：UPPER_SNAKE_CASE（例：`DEFAULT_LIMIT`）
- [ ] 変数名・配列キー：snake_case（例：`$category_id`）

#### Symfony固有の確認
- [ ] ルーティングアノテーションの正確性
- [ ] FormTypeの適切な定義
- [ ] Twigテンプレートの構文確認
- [ ] バリデーションアノテーションの記述確認

### JavaScript 専用チェック項目

#### 基本構文・フォーマット
- [ ] セミコロン統一（推奨：あり）
- [ ] クォート統一（推奨：シングル）
- [ ] インデント統一（推奨：スペース2個）
- [ ] 変数宣言：const/let使用（varは非推奨）

#### DOM操作・イベント
- [ ] イベントリスナーの適切な登録
- [ ] DOM要素の存在確認
- [ ] メモリリーク対策（不要なイベントリスナー削除）

---

## 🎯 Rails経験者向け学習ポイント

### Symfony vs Rails 対応表

| **観点** | **Symfony** | **Rails** | **レビュー時の注意点** |
|----------|-------------|-----------|----------------------|
| **Model** | Entity + Repository | Model | DoctrineORMの記法違い |
| **View** | Twig Template | ERB/Haml | エスケープ処理の違い |
| **Controller** | Controller + Action | Controller + Action | アノテーション記法 |
| **Form** | FormType | form_with | バリデーション記述場所 |
| **Route** | @Route annotation | routes.rb | アノテーション記法 |

### よくある間違いパターン（Rails経験者）

1. **Active Recordパターンの混同**
   ```php
   // Rails風に書きがち（間違い）
   $category->save();  // ← このメソッドは存在しない

   // Symfony/Doctrine正解
   $em = $this->getDoctrine()->getManager();
   $em->persist($category);
   $em->flush();
   ```

2. **バリデーション記述場所**
   ```php
   // Rails: モデルに記述
   # app/models/category.rb
   validates :name, presence: true

   // Symfony: エンティティにアノテーション
   /**
    * @Assert\NotBlank(message="カテゴリ名は必須です")
    */
   private $name;
   ```

**レビュー時の心構え**: Railsの知識を活かしつつ、Symfony固有の記法やベストプラクティスに注意を払うことで、より品質の高いコードレビューが可能になります。
