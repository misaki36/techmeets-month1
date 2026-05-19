<?php
require_once 'db.php';

$error = '';

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');

    if ($title === '' || $content === '' || $author === '') {
        $error = 'タイトル、内容、作成者は必須です。';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO posts (title, content, author) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $author);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            // 登録成功したら一覧ページに移動する
            header('Location: index.php');
            exit;
        } else {
            $error = '登録に失敗しました: ' . $stmt->error;
            $stmt->close();
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>記事登録</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>新規記事登録</h1>
    <a href="index.php">← 一覧に戻る</a>

    <?php if ($error): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>記事タイトル:
            <input type="text" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
        </label><br>
        <label>記事内容:
            <textarea name="content"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
        </label><br>
        <label>作成者:
            <input type="text" name="author" value="<?php echo htmlspecialchars($_POST['author'] ?? ''); ?>">
        </label><br>
        <button type="submit">登録する</button>
    </form>
</body>
</html>