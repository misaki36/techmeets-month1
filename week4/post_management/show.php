<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';

$conn = getDBConnection();

// 記事を取得
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// 記事が存在しない場合は一覧に戻す
if (!$post) {
    $conn->close();
    header('Location: index.php');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>記事詳細</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <a href="index.php">← 一覧に戻る</a>

    <p><?php echo htmlspecialchars($post['content']); ?></p>
    <p>作成者: <?php echo htmlspecialchars($post['author']); ?></p>
    <p>作成日時: <?php echo $post['created_at']; ?></p>
    <p>更新日時: <?php echo $post['updated_at']; ?></p>

    <a href="edit.php?id=<?