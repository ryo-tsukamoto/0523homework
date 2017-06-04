<?php
//データベースに接続
$db_user = 'root';         // ユーザー名
$db_pass = 'jusjh47y';     // パスワード
$db_name = 'bbs';          // データベース名

// MySQLに接続
$mysqli = new mysqli('localhost', $db_user, $db_pass, $db_name);
//mysqlの接続エラー処理
if ($mysqli->connect_errno) {
  printf("Connect failed: %s\n", $mysqli->connect_errno);
  exit();
}

//スレッドの読み込み　最新のスレッドを一件読み込む
$result = $mysqli->query('SELECT * FROM threads ORDER BY id DESC LIMIT 1');
if (!$result) {    //queryエラーの場合，エラーを表示する
  printf("Query failed: %s\n", $mysqli->error);
  exit();
} else {
  foreach ($result as $row) {
    //XSS対策
    $writer = htmlspecialchars($row['writer']);
    $id = htmlspecialchars($row['id']);
  }
}

//スレッドにコメントを登録
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!empty($_POST['id']) && !empty($_POST['writer']) && !empty($_POST['body']) && !empty($_POST['password'])) {    //formの値がそれぞれ空でない時
    //SQLインジェクション処理
    $id = $mysqli->real_escape_string($_POST['id']);
    $writer = $mysqli->real_escape_string($_POST['writer']);
    $body = $mysqli->real_escape_string($_POST['body']);
    $password = $mysqli->real_escape_string($_POST['password']);
    $insert = $mysqli->query("INSERT INTO `messages` (`thread_id`, `writer`, `body`, `password`) VALUES ('{$id}', '{$writer}', '{$body}', '{$password}')");
    if ($insert) {  //登録できた場合スレッド内容一覧を表示
      header("location: ./message_see.php?id={$id}");
    } else {    //それ以外の場合エラー処理
      printf("Query failed: %s\n", $mysqli->error);
      exit();
    }
  }
}

//接続を閉じる
$mysqli->close();

?>

<html>
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <title>掲示板</title>
  </head>

  <body>
    <div class="container">
      <div class="page-header">
        <h1><?= $writer ?></h1>
      </div>

      <!-- 新規コメントの登録 -->
      <form name="comment" action="" method="post">
        <table class="table">
          <thead>
            <tr>
              <th>Writer</th>
              <th>Body</th>
              <th colspan="2">Password</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><input type="text" name="writer" class="form-control"></td>
              <td><input type="text" name="body" class="form-control"></td>
              <td><input type="password" name="password" class="form-control"></td>
              <td>
                <input type="hidden" name="id" value="<?= $id ?> ">
                <input type="submit" class="btn btn-primary" value="Submit" onclick="check()"></td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>

    <!-- 文字入力がない場合の警告ポップアップ -->
    <script language="JavaScript">
    function check() {
        if(document.comment.writer.value == "" || document.comment.body.value == "" || document.comment.password.value == "") { //新規スレッドフォームのnameかpasswordの値が空だった場合
          alert("Name・comment・passwordを記入してください.");    //alertを表示する
          return ;
        }
    }
    </script>
  </body>
</html>
