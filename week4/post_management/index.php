<?php
require_once 'db.php';
$conn = getDBConnection();

// 全記事を取得
$query = "SELECT id, title, content, author, created_at, updated_at FROM posts ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>記事管理システム</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>記事一覧</h1>
    <a href="create.php">新規記事作成</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>タイトル</th>
                <th>内容</th>
                <th>作成者</th>
                <th>作成日時</th>
                <th>更新日時</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><a href="show.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
                <td><?php echo htmlspecialchars($row['content']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo $row['updated_at']; ?></td>
                <td>
                    
                    <a href="edit.php?id=<?php echo $row['id']; ?>">編集</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('本当に削除しますか？')">削除</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>