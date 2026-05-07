<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>コメント投稿</title>
</head>
<body>

<h1>コメント投稿フォーム</h1>

<form method="POST">
  <label>名前:</label>
  <input type="text" name="name"><br>
  <label>コメント:</label>
  <textarea name="comment"></textarea><br>
  <button type="submit">投稿する</button>
</form>

<?php
// フォームがPOSTで送信されたときだけ処理を実行する
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // POSTデータから名前とコメントを取得する
    // 【修正箇所1】?? "" を追加し、キーが未定義のときのWarningを防ぐ
    $name    = $_POST["name"] ?? "";
    $comment = $_POST["comment"] ?? "";

    // 名前が空かどうかチェックする
    // 【修正箇所2】= (代入) を == (比較) に修正
    if ($name == "") {
        echo "名前を入力してください。";
    } else {
        // 名前とコメントを画面に表示する
        // 【修正箇所3】htmlspecialchars() でXSS対策をする
        echo "<p>" . htmlspecialchars($name) . "さんのコメント:</p>";
        echo "<p>" . htmlspecialchars($comment) . "</p>";
    }
}
?>

</body>
</html>