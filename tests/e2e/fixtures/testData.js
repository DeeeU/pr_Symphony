const TestData = {
    validMemo: {
        title: "有効なメモタイトル",
        content: "これは有効なメモ内容です。テスト用のサンプルテキストが含まれています。"
    },
    
    invalidMemo: {
        empty: {
            title: "",
            content: ""
        },
        tooLong: {
            title: "あ".repeat(256), // 256文字の長いタイトル
            content: "あ".repeat(5001) // 5001文字の長い内容
        }
    },

    validCategory: {
        name: "有効なカテゴリ名",
        description: "これは有効なカテゴリの説明です。"
    },
    
    invalidCategory: {
        empty: {
            name: "",
            description: ""
        },
        tooLong: {
            name: "あ".repeat(101), // 101文字の長い名前
            description: "あ".repeat(501) // 501文字の長い説明
        }
    },

    searchKeywords: {
        existing: "テスト",
        nonExisting: "存在しないキーワード12345"
    },

    expectedMessages: {
        memoCreated: "を作成しました",
        memoUpdated: "を更新しました",
        memoDeleted: "を削除しました",
        categoryCreated: "を作成しました",
        categoryUpdated: "を更新しました",
        categoryDeleted: "を削除しました",
        validationError: "必須です",
        invalidRequest: "不正なリクエストです"
    },

    urls: {
        base: "http://localhost:8001",
        memo: {
            list: "/memo/",
            new: "/memo/new"
        },
        category: {
            list: "/category/",
            new: "/category/new"
        }
    }
};

module.exports = TestData;