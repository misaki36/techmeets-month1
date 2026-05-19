<?php
require_once 'db.php';

$id    = $_GET['id'] ?? '';
$error = '';

$conn = getDBConnection();

// 編集対象の記事を取得（GETパラメータのid で検索）
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$posts   = $result->fetch_assoc();
$stmt->close();

// 記事が存在しない場合は一覧に戻す
if (!$posts) {
    $conn->close();
    header('Location: index.php');
    exit;
}

// フォームが送信されたとき（POSTリクエスト）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $author      = $_POST['author'] ?? '';

    if ($title === '' || $content === '' || $author === '') {
        $error = 'タイトル、内容、作成者は必須です。';
    } else {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, author = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $author, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: index.php');
            exit;
        } else {
            $error = '更新に失敗しました: ' . $stmt->error;
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>記事編集</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>記事編集</h1>
    <a href="index.php">← 一覧に戻る</a>

    <?php if ($error): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- value には「POSTで送られた値」か「DBから取得した現在の値」を表示する -->
    <form method="POST">
        <label>タイトル:
            <input type="text" name="title"
                value="<?php echo htmlspecialchars($_POST['title'] ?? $posts['title']); ?>">
        </label><br>
        <label>内容:
            <textarea name="content"><?php echo htmlspecialchars($_POST['content'] ?? $posts['content']); ?></textarea>
        </label><br>
        <label>作成者:
            <input type="text" name="author"
                value="<?php echo htmlspecialchars($_POST['author'] ?? $posts['author']); ?>">
        </label><br>
        <button type="submit">更新する</button>
    </form>
</body>
</html>
