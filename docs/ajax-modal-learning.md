# Ajax & Modal実装 学習ノート

## 📅 学習日時
2025-09-17

## 🎯 学習目標
- Ajax通信の基本理解
- Bootstrap Modalの実装
- フロントエンド・バックエンド連携
- 実践的なWebアプリケーション開発

---

## 📚 1. Ajax実装の基本

### Ajaxとは？
- **A**synchronous **Ja**vaScript **a**nd **X**ML の略
- ページをリロードせずにサーバーと通信する技術
- 今回は JSON でデータをやり取り

### 実装アーキテクチャ
```
フロントエンド (JavaScript) ←→ バックエンド (Symfony)
     ↓                           ↓
1. モーダル開く                   1. Controller でデータ取得
2. Ajax リクエスト送信            2. JSON レスポンス返却
3. データ受信・HTML生成           3. データベースからデータ取得
4. 画面更新
```

### JavaScript実装例
```javascript
// 1. Ajax リクエスト送信
fetch('/memo/ajax/categories')
  .then(response => response.json())  // JSON に変換
  .then(data => {
    // 2. 成功時の処理
    console.log('データ:', data);

    // HTML を動的生成
    const listHtml = data.message.map(category => `
      <div class="card">
        <h6>${category.name}</h6>
        <button data-id="${category.id}">選択</button>
      </div>
    `).join('');

    // 3. DOM に挿入
    document.getElementById('category-list').innerHTML = listHtml;
  })
  .catch(error => {
    // 4. エラー処理
    console.error('エラー:', error);
  });
```

### Symfony Controller実装例
```php
/**
 * @Route("/memo/ajax/categories", name="memo_ajax_categories")
 */
public function ajaxCategoriesAction()
{
    // 1. データベースからデータ取得
    $categories = $this->getDoctrine()
                       ->getRepository(Category::class)
                       ->findAll();

    // 2. 配列に変換
    $categoryData = [];
    foreach ($categories as $category) {
        $categoryData[] = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'color' => $category->getColor(),
            'memo_count' => $category->getMemoCount()
        ];
    }

    // 3. JSON レスポンス返却
    return new JsonResponse([
        'success' => true,
        'message' => $categoryData
    ]);
}
```

---

## 🎭 2. Modal実装の基本

### Modalとは？
- 画面上に重なって表示されるダイアログボックス
- Bootstrap の Modal コンポーネントを使用
- ユーザーの注意を特定のコンテンツに向ける

### HTML構造
```html
<!-- 1. トリガーボタン -->
<button data-toggle="modal" data-target="#categoryModal">
    📁 選択
</button>

<!-- 2. Modal HTML -->
<div class="modal fade" id="categoryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">ヘッダー</div>
            <div class="modal-body">コンテンツ</div>
            <div class="modal-footer">フッター</div>
        </div>
    </div>
</div>
```

### JavaScript制御
```javascript
// Modal が開いた時のイベント
$('#categoryModal').on('shown.bs.modal', function () {
    console.log('Modal が開きました');
    // Ajax でデータ取得開始
    loadCategories();
});

// Modal を閉じる
$('#categoryModal').modal('hide');
```

---

## 🔧 3. 今回のプロジェクト実装

### 実装したファイル構造
```
プロジェクト/
├── app/Resources/views/memo/new.html.twig  # Modal HTML + CSS
├── web/js/memo-selector.js                 # Ajax + Modal制御
├── src/AppBundle/Controller/
│   ├── MemoController.php                  # カテゴリAPI
│   └── UserController.php                  # ユーザーAPI (バグ修正)
└── docs/ajax-modal-learning.md            # この学習ノート
```

### 実装の流れ
```
1. ユーザーが「📁 選択」ボタンをクリック
   ↓
2. Bootstrap Modal が開く
   ↓
3. 'shown.bs.modal' イベントが発火
   ↓
4. Ajax で /memo/ajax/categories にリクエスト
   ↓
5. Symfony Controller がデータベースからカテゴリ取得
   ↓
6. JSON でレスポンス返却
   ↓
7. JavaScript でカテゴリリストのHTML生成
   ↓
8. Modal内に表示
   ↓
9. ユーザーがカテゴリを選択
   ↓
10. フォームに値を設定してModal閉じる
```

### 重要な技術ポイント

#### ① フォーム値の二重管理
```javascript
// 表示用 (ユーザーが見える)
document.getElementById('category-select').innerHTML =
    `<option value="${id}" selected>${name}</option>`;

// 送信用 (Symfony が受け取る)
document.getElementById('appbundle_memo_category').value = id;
```

**なぜ二重管理が必要？**
- Symfony Form コンポーネントは独自のフィールド名を生成
- `name="appbundle_memo[category]"` のような形式
- 表示用とデータ送信用で異なるフィールドが必要

#### ② 動的HTML生成
```javascript
const listHtml = data.message.map(category => `
    <div class="card mb-3">
        <div class="card-body">
            <h6>${category.name}</h6>
            <span class="badge" style="background-color: ${category.color}">
                ${category.name}
            </span>
            <button data-id="${category.id}" data-name="${category.name}">
                選択
            </button>
        </div>
    </div>
`).join('');
```

**ポイント:**
- `map()` でデータを HTML に変換
- `join('')` で配列を文字列に結合
- テンプレートリテラル（`\`文字）で複数行HTML記述

#### ③ 動的イベントリスナー
```javascript
// 動的に生成した要素にイベント追加
document.querySelectorAll('.select-category-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault(); // デフォルト動作を防ぐ
        const categoryId = this.dataset.id;
        const categoryName = this.dataset.name;
        selectCategory(categoryId, categoryName);
    });
});
```

**なぜ動的に追加が必要？**
- HTML が Ajax で後から生成される
- ページ読み込み時には要素が存在しない
- 要素生成後にイベントを追加する必要がある

---

## 🐛 4. 解決した問題と対策

### 問題1: フォーム値が送信されない

**症状:**
- Modal で選択はできるが、メモ作成時にカテゴリが保存されない

**原因:**
- 表示用フィールド（`category-select`）にのみ値を設定
- 実際のフォーム送信時は隠しフィールド（`appbundle_memo_category`）が使用される

**解決策:**
```javascript
function selectCategory(id, name, color) {
    // 表示用フィールドを更新
    document.getElementById('category-select').innerHTML =
        `<option value="${id}" selected>${name}</option>`;

    // ✅ 実際のSymfonyフォームフィールドも更新
    document.getElementById('appbundle_memo_category').value = id;
}
```

### 問題2: Modal オーバーレイが残る

**症状:**
- Modal 選択後に薄暗いオーバーレイが残る
- 他の要素がクリックできない

**原因:**
- Bootstrap の `.modal-backdrop` 要素が残る
- `body` の `modal-open` クラスが残る

**解決策:**
```javascript
function selectCategory(id, name, color) {
    // Modal を閉じる
    $('#categoryModal').modal('hide');

    // ✅ オーバーレイを確実に削除
    setTimeout(() => {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    }, 100);
}
```

**CSS での追加対策:**
```css
/* bodyのスクロールロック解除 */
body.modal-open {
    overflow: auto !important;
    padding-right: 0 !important;
}
```

### 問題3: UserController のバグ

**症状:**
- 作成者モーダルでエラーが発生

**原因:**
```php
// ❌ 間違い
'memo_count' => count($user->getMemo())

// ✅ 正解
'memo_count' => count($user->getMemos())
```

**学習ポイント:**
- エンティティのリレーション名は複数形（`memos`）
- エラーメッセージをよく読む習慣の重要性

---

## 💡 5. 開発Tips & ベストプラクティス

### Ajax開発のTips

#### ① エラーハンドリングを必ず入れる
```javascript
fetch('/api/data')
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .catch(error => {
        console.error('エラー:', error);
        // ユーザーにエラー表示
        showErrorMessage('データの取得に失敗しました');
    });
```

#### ② ローディング表示を入れる
```javascript
// ローディング開始
document.getElementById('loading').style.display = 'block';

fetch('/api/data')
    .then(data => {
        // データ処理
    })
    .finally(() => {
        // 必ずローディングを隠す
        document.getElementById('loading').style.display = 'none';
    });
```

#### ③ データ検証を行う
```javascript
.then(data => {
    if (data.success && Array.isArray(data.categories)) {
        renderCategories(data.categories);
    } else {
        throw new Error('不正なデータ形式');
    }
});
```

### Modal開発のTips

#### ① Bootstrap バージョンの確認
```html
<!-- Bootstrap 4 の場合 -->
<button data-toggle="modal" data-target="#myModal">開く</button>

<!-- Bootstrap 5 の場合 -->
<button data-bs-toggle="modal" data-bs-target="#myModal">開く</button>
```

#### ② Modal 内の動的コンテンツ
```javascript
// Modal が開くたびにコンテンツをリセット
$('#myModal').on('show.bs.modal', function () {
    $(this).find('.modal-body').html('読み込み中...');
});
```

### セキュリティ注意点

#### ① XSS対策
```javascript
// ❌ 危険: HTMLをそのまま挿入
element.innerHTML = userInput;

// ✅ 安全: エスケープする
element.textContent = userInput;

// または
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

#### ② CSRF対策 (Symfony)
```php
// Controller で CSRF トークン確認
if (!$this->isCsrfTokenValid('ajax_action', $token)) {
    return new JsonResponse(['error' => 'Invalid token'], 403);
}
```

### パフォーマンス Tips

#### ① 重複リクエスト防止
```javascript
let isLoading = false;

function loadData() {
    if (isLoading) return; // 重複防止

    isLoading = true;
    fetch('/api/data')
        .finally(() => {
            isLoading = false;
        });
}
```

#### ② キャッシュ活用
```javascript
let cachedData = null;

function loadCategories() {
    if (cachedData) {
        renderCategories(cachedData);
        return;
    }

    fetch('/api/categories')
        .then(data => {
            cachedData = data; // キャッシュ保存
            renderCategories(data);
        });
}
```

---

## 🎓 6. 学習の振り返り

### 理解できたこと
- [ ] Ajax通信の基本的な流れ
- [ ] Bootstrap Modal の使い方
- [ ] JavaScript での DOM 操作
- [ ] フロントエンド・バックエンドの連携方法
- [ ] Symfony でのJSON API作成
- [ ] デバッグ手法（console.log, ネットワークタブ）

### 難しかったポイント
- [ ] フォーム値の二重管理の概念
- [ ] 動的に生成した要素へのイベント追加
- [ ] Modal のライフサイクル理解
- [ ] Bootstrap のオーバーレイ問題

### 次回の学習課題
- [ ] より複雑なAPI設計（ページネーション、検索）
- [ ] エラーハンドリングの充実
- [ ] アニメーションやトランジション
- [ ] テストコードの追加
- [ ] パフォーマンス最適化

---

## 🔗 7. 参考資料

### 公式ドキュメント
- [Bootstrap Modal](https://getbootstrap.com/docs/4.6/components/modal/)
- [MDN - Fetch API](https://developer.mozilla.org/ja/docs/Web/API/Fetch_API)
- [Symfony HttpFoundation](https://symfony.com/doc/current/components/http_foundation.html)

### 学習に役立つリソース
- [JavaScript Promise の理解](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Global_Objects/Promise)
- [DOM操作の基本](https://developer.mozilla.org/ja/docs/Web/API/Document_Object_Model)
- [CSS Flexbox ガイド](https://css-tricks.com/snippets/css/a-guide-to-flexbox/)

---

## 📝 8. 次のステップ

### 短期目標（1-2週間）
1. バリデーション機能の追加
2. 検索・フィルタリング機能
3. ページネーション実装

### 中期目標（1-2ヶ月）
1. Vue.js または React への移行検討
2. WebSocket でのリアルタイム機能
3. PWA（Progressive Web App）対応

### 長期目標（3-6ヶ月）
1. TypeScript の導入
2. テスト駆動開発（TDD）
3. CI/CD パイプラインの構築

---

**学習日時:** 2025-09-17
**実装時間:** 約3時間
**習得技術:** Ajax, Bootstrap Modal, Symfony API, JavaScript DOM操作