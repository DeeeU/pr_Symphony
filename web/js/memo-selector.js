document.addEventListener('DOMContentLoaded', function () {
  console.log('memo-selector.js が読み込まれました');

  // カテゴリモーダルが開いた時
  $('#categoryModal').on('shown.bs.modal', function () {
    console.log('カテゴリモーダルが開きました');

    // 初期化
    document.getElementById('category-loading').style.display = 'block';
    document.getElementById('category-list').style.display = 'none';

    fetch('/memo/ajax/categories')
      .then(response => {
        console.log('カテゴリレスポンス:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('カテゴリデータ:', data);

        // 読み込み表示を隠す
        document.getElementById('category-loading').style.display = 'none';

        if (data.success && data.message) {
          const listHtml = data.message.map(category => `
                        <div class="card mb-3 category-card" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">
                                            <span class="badge badge-primary mr-2"
                                                  style="background-color: ${category.color}; color: white;">
                                                ${category.name}
                                            </span>
                                        </h6>
                                        <p class="card-text text-muted small mb-1">
                                            ${category.description || '説明なし'}
                                        </p>
                                        <small class="text-info">
                                            📄 メモ数: ${category.memo_count}件
                                        </small>
                                    </div>
                                    <button class="btn btn-primary btn-sm ml-3 select-category-btn"
                                            data-id="${category.id}"
                                            data-name="${category.name}"
                                            data-color="${category.color}"
                                            style="min-width: 60px;">
                                        ✓ 選択
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');

          document.getElementById('category-list').innerHTML = listHtml;
          document.getElementById('category-list').style.display = 'block';

          // ✅ イベントリスナーを追加（onclick ではなくaddEventListener）
          document.querySelectorAll('.select-category-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              const categoryId = this.dataset.id;
              const categoryName = this.dataset.name;
              const categoryColor = this.dataset.color;
              selectCategory(categoryId, categoryName, categoryColor);
            });
          });
        } else {
          document.getElementById('category-list').innerHTML = '<p>カテゴリが見つかりません</p>';
          document.getElementById('category-list').style.display = 'block';
        }
      })
      .catch(error => {
        console.error('カテゴリ取得エラー:', error);
        document.getElementById('category-loading').style.display = 'none';
        document.getElementById('category-list').innerHTML = '<p class="text-danger">エラーが発生しました: ' + error.message + '</p>';
        document.getElementById('category-list').style.display = 'block';
      });
  });

  // ユーザーモーダルが開いた時
  $('#authorModal').on('shown.bs.modal', function () {
    console.log('ユーザーモーダルが開きました');

    // 初期化
    document.getElementById('author-loading').style.display = 'block';
    document.getElementById('author-list').style.display = 'none';

    fetch('/user/ajax/list')
      .then(response => {
        console.log('ユーザーレスポンス:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('ユーザーデータ:', data);

        // 読み込み表示を隠す
        document.getElementById('author-loading').style.display = 'none';

        if (data.success && data.users) {
          const listHtml = data.users.map(user => `
                        <div class="card mb-3 user-card" style="cursor: pointer;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="card-title mb-1">${user.name}</h6>
                                        <p class="card-text text-muted small mb-1">${user.email}</p>
                                        <small class="text-info">
                                            📄 メモ数: ${user.memo_count}件
                                        </small>
                                    </div>
                                    <button class="btn btn-success btn-sm ml-3 select-author-btn"
                                            data-id="${user.id}"
                                            data-name="${user.name}"
                                            style="min-width: 60px;">
                                        ✓ 選択
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');

          document.getElementById('author-list').innerHTML = listHtml;
          document.getElementById('author-list').style.display = 'block';

          // ✅ イベントリスナーを追加
          document.querySelectorAll('.select-author-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              const userId = this.dataset.id;
              const userName = this.dataset.name;
              selectAuthor(userId, userName);
            });
          });
        } else {
          document.getElementById('author-list').innerHTML = '<p>ユーザーが見つかりません</p>';
          document.getElementById('author-list').style.display = 'block';
        }
      })
      .catch(error => {
        console.error('ユーザー取得エラー:', error);
        document.getElementById('author-loading').style.display = 'none';
        document.getElementById('author-list').innerHTML = '<p class="text-danger">エラーが発生しました: ' + error.message + '</p>';
        document.getElementById('author-list').style.display = 'block';
      });
  });
});

// ✅ モーダルを確実に閉じる関数
function selectCategory(id, name, color) {
  console.log('カテゴリ選択:', id, name);

  // 表示用フィールドを更新
  const selectElement = document.getElementById('category-select');
  selectElement.innerHTML = `<option value="${id}" selected>${name}</option>`;
  selectElement.value = id;

  // 実際のSymfonyフォームフィールドも更新
  document.getElementById('appbundle_memo_category').value = id;

  // プレビュー表示
  const previewElement = document.getElementById('selected-category-preview');
  previewElement.innerHTML = `
        <div class="alert alert-success">
            <strong>選択済み:</strong>
            <span class="badge" style="background-color: ${color}; color: white;">
                ${name}
            </span>
        </div>
    `;
  previewElement.style.display = 'block';

  // ✅ モーダルを強制的に閉じる
  $('#categoryModal').modal('hide');

  // オーバーレイを確実に削除
  setTimeout(() => {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
  }, 100);

  // 成功メッセージ
  setTimeout(() => {
    alert('カテゴリを選択しました: ' + name);
  }, 300);
}

function selectAuthor(id, name) {
  console.log('作成者選択:', id, name);

  // 表示用フィールドを更新
  const selectElement = document.getElementById('author-select');
  selectElement.innerHTML = `<option value="${id}" selected>${name}</option>`;
  selectElement.value = id;

  // 実際のSymfonyフォームフィールドも更新
  document.getElementById('appbundle_memo_author').value = id;

  // プレビュー表示
  const previewElement = document.getElementById('selected-author-preview');
  previewElement.innerHTML = `
        <div class="alert alert-info">
            <strong>選択済み:</strong> ${name}
        </div>
    `;
  previewElement.style.display = 'block';

  // ✅ モーダルを強制的に閉じる
  $('#authorModal').modal('hide');

  // オーバーレイを確実に削除
  setTimeout(() => {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('padding-right', '');
  }, 100);

  // 成功メッセージ
  setTimeout(() => {
    alert('作成者を選択しました: ' + name);
  }, 300);
}
