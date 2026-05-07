<?php
// XSS対策用のヘルパー関数
// 出力する文字列をHTMLエスケープして、スクリプト埋め込みなどの攻撃を防ぐ
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 変数の初期化
// フォームが初めて表示されるとき（GETアクセス時）に備えて空文字をセットしておく
$errors  = [];
$name    = '';
$email   = '';
$subject = '';
$message = '';
$step    = 'form'; // 表示する画面を管理する変数。'form'=フォーム画面 / 'confirm'=確認画面

// フォームが送信されたとき（POSTリクエスト）の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 送信された各入力値を取得する
    // trim() で前後の空白を除去し、?? '' で値がない場合は空文字にする
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (isset($_POST['back'])) {
        // 確認画面の「戻る」ボタンが押された場合
        // バリデーションはせず、入力値を保持したままフォーム画面に戻す
        $step = 'form';
    } else {
        // 「確認画面へ」ボタンが押された場合 → バリデーション実行

        // 名前の必須チェック
        if ($name === '') {
            $errors['name'] = '名前を入力してください。';
        }

        // メールアドレスの必須チェックと形式チェック
        if ($email === '') {
            $errors['email'] = 'メールアドレスを入力してください。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // filter_var() でメールアドレスの形式が正しいか検証する
            $errors['email'] = '正しいメールアドレスの形式で入力してください。';
        }

        // 件名の必須チェック
        if ($subject === '') {
            $errors['subject'] = '件名を入力してください。';
        }

        // メッセージの必須チェック
        if ($message === '') {
            $errors['message'] = 'メッセージを入力してください。';
        }

        // エラーが1件もなければ確認画面へ、1件でもあればフォーム画面に留まる
        $step = empty($errors) ? 'confirm' : 'form';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 確認画面のときはタイトルに「- 確認」を追加する -->
    <title>お問合せフォーム<?= $step === 'confirm' ? ' - 確認' : '' ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ページ全体を中央揃えにする */
        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', sans-serif;
            background: #f4f6f8;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* フォームを囲む白いカード */
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 40px 48px;
            width: 100%;
            max-width: 560px;
        }

        /* ページ見出し */
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a2e;
            border-left: 4px solid #4f6ef7;
            padding-left: 12px;
        }

        /* ── フォーム画面のスタイル ── */
        h1.form-title { margin-bottom: 32px; }

        /* 各入力項目のまとまり */
        .form-group {
            margin-bottom: 20px;
        }

        /* 入力項目のラベル */
        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
        }

        /* 必須マーク（*） */
        label .required {
            color: #e53e3e;
            margin-left: 4px;
            font-size: 0.75rem;
        }

        /* テキスト入力・メール入力・テキストエリアの共通スタイル */
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            color: #333;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        /* フォーカス時に青い枠を表示する */
        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            outline: none;
            border-color: #4f6ef7;
            box-shadow: 0 0 0 3px rgba(79, 110, 247, 0.15);
            background: #fff;
        }

        /* バリデーションエラーがある入力欄を赤枠にする */
        input.is-error,
        textarea.is-error {
            border-color: #e53e3e;
        }

        /* テキストエリアは縦方向にのみリサイズ可能にする */
        textarea {
            resize: vertical;
            min-height: 140px;
        }

        /* エラーメッセージのテキスト */
        .error-msg {
            color: #e53e3e;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        /* ── 確認画面のスタイル ── */
        h1.confirm-title { margin-bottom: 8px; }

        /* 確認画面の説明文 */
        .subtitle {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 32px;
            padding-left: 16px;
        }

        /* 入力内容を表示するテーブル */
        .confirm-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }

        .confirm-table th,
        .confirm-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.95rem;
        }

        /* 項目名（左列）*/
        .confirm-table th {
            width: 36%;
            color: #555;
            font-weight: 600;
            background: #f9fafb;
            white-space: nowrap;
        }

        /* 入力値（右列）。改行やURLの折り返しに対応 */
        .confirm-table td {
            word-break: break-all;
            white-space: pre-wrap;
        }

        /* 最終行だけ下線を非表示にする */
        .confirm-table tr:last-child th,
        .confirm-table tr:last-child td {
            border-bottom: none;
        }

        /* 戻る・送信ボタンを横並びにするコンテナ */
        .actions {
            display: flex;
            gap: 12px;
        }

        /* ── ボタンのスタイル ── */

        /* 戻るボタン（白抜き） */
        .btn-back {
            flex: 1;
            padding: 12px;
            background: #fff;
            color: #4f6ef7;
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid #4f6ef7;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-back:hover { background: #eef1fe; }

        /* 送信ボタン（青塗り）。フォーム画面・確認画面の両方で使う */
        .btn-submit {
            display: block;
            width: 100%;
            padding: 12px;
            background: #4f6ef7;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s, transform 0.1s;
        }

        /* 確認画面では .actions の中に入るので横幅の指定を上書きする */
        .actions .btn-submit {
            flex: 2;
            margin-top: 0;
        }

        .btn-submit:hover { background: #3a57e8; }
        .btn-submit:active { transform: scale(0.98); }
    </style>
</head>
<body>
    <div class="card">

        <?php if ($step === 'form'): ?>
        <!-- ========== フォーム画面 ========== -->
        <h1 class="form-title">お問合せフォーム</h1>
        <form method="post" action="">

            <!-- 名前の入力欄 -->
            <div class="form-group">
                <label for="name">名前<span class="required">*</span></label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= h($name) ?>"
                    class="<?= isset($errors['name']) ? 'is-error' : '' ?>"
                >
                <?php if (isset($errors['name'])): ?>
                    <p class="error-msg"><?= h($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <!-- メールアドレスの入力欄 -->
            <div class="form-group">
                <label for="email">メールアドレス<span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= h($email) ?>"
                    class="<?= isset($errors['email']) ? 'is-error' : '' ?>"
                >
                <?php if (isset($errors['email'])): ?>
                    <p class="error-msg"><?= h($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <!-- 件名の入力欄 -->
            <div class="form-group">
                <label for="subject">件名<span class="required">*</span></label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= h($subject) ?>"
                    class="<?= isset($errors['subject']) ? 'is-error' : '' ?>"
                >
                <?php if (isset($errors['subject'])): ?>
                    <p class="error-msg"><?= h($errors['subject']) ?></p>
                <?php endif; ?>
            </div>

            <!-- メッセージの入力欄 -->
            <div class="form-group">
                <label for="message">メッセージ<span class="required">*</span></label>
                <textarea
                    id="message"
                    name="message"
                    class="<?= isset($errors['message']) ? 'is-error' : '' ?>"
                ><?= h($message) ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <p class="error-msg"><?= h($errors['message']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">確認画面へ</button>
        </form>

        <?php else: ?>
        <!-- ========== 確認画面 ========== -->
        <h1 class="confirm-title">お問合せフォーム</h1>
        <p class="subtitle">以下の内容でよろしければ「送信する」を押してください。</p>

        <!-- 入力内容の一覧表示 -->
        <table class="confirm-table">
            <tr>
                <th>名前</th>
                <td><?= h($name) ?></td>
            </tr>
            <tr>
                <th>メールアドレス</th>
                <td><?= h($email) ?></td>
            </tr>
            <tr>
                <th>件名</th>
                <td><?= h($subject) ?></td>
            </tr>
            <tr>
                <th>メッセージ</th>
                <td><?= h($message) ?></td>
            </tr>
        </table>

        <div class="actions">
            <!-- 戻るボタン：hiddenフィールドで入力値をPOSTに乗せて送り、フォーム画面に戻す -->
            <form method="post" action="">
                <input type="hidden" name="name"    value="<?= h($name) ?>">
                <input type="hidden" name="email"   value="<?= h($email) ?>">
                <input type="hidden" name="subject" value="<?= h($subject) ?>">
                <input type="hidden" name="message" value="<?= h($message) ?>">
                <!-- name="back" が送信されると、PHP側で戻るボタン押下と判定する -->
                <button type="submit" name="back" value="1" class="btn-back">戻る</button>
            </form>

            <button type="button" class="btn-submit">送信する</button>
        </div>

        <?php endif; ?>

    </div>
</body>
</html>
