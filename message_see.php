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

//コメントの登録
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!empty($_POST['writer']) && !empty($_POST['body']) && !empty($_POST['password'])) {    //nameと,comment,passwordの値が空値でない場合
    //SQLインジェクション処理
    $id = $mysqli->real_escape_string($_POST['id']);
    $writer = $mysqli->real_escape_string($_POST['writer']);
    $body = $mysqli->real_escape_string($_POST['body']);
    $password = $mysqli->real_escape_string($_POST['password']);
    $insert = $mysqli->query("INSERT INTO `messages` (`thread_id`, `writer`, `body`, `password`) VALUES ('{$id}', '{$writer}', '{$body}', '{$password}')");
    if (!$insert) {   //queryエラーの場合，エラーを表示する
      printf("Query failed: %s\n", $mysqli->error);
      exit();
    }
  }
}

//GETでthread_idを受け取り、スレッドのコメントの読み込み　messagesのid降順
$query = "SELECT threads.name, messages.* FROM threads INNER JOIN messages ON threads.id = messages.thread_id WHERE threads.id = {$_GET['id']} ORDER BY id DESC";
$result = $mysqli->query($query);
$result_count = $mysqli->affected_rows;   //resultの件数を取得
if ($result_count >= 1) {   //取得件数が１件以上の場合、結果を取得
  foreach ($result as $row) {}
} elseif ($result_count == 0) {   //取得件数が０件の場合 スレッド名を取得
  $thread_name = $mysqli->query("SELECT name FROM threads WHERE id = {$_GET['id']}");
  foreach ($name as $row) {}
} else {    //それ以外の場合エラー処理
  printf("Query failed: %s\n", $mysqli->error);
  exit();
}

//接続を閉じる
$mysqli->close();

?>

<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <title>掲示板</title>
</head>

<body>
  <div class="container">
    <div class="page-header">
      <h1>
        <?= $thread_name = htmlspecialchars($row['name']); ?>
      </h1>
      <div style="text-align: right; margin: -5rem 0 10px;">
        <button type="button" class="btn" onclick="location.href='./thread_action.php'">スレッド一覧
          <span class="glyphicon glyphicon-arrow-left"></span>
        </button>
      </div>
    </div>

    <!-- コメントの投稿 -->
    <form name="bbs" action="" method="post">
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
            <td><input type="submit" class="btn btn-primary" value="Submit" onclick="check()"></td>
          </tr>
        </tbody>
      </table>
    </form>

    <!-- コメントの表示 -->
    <table class="table table-striped">
      <thead>
        <tr>
          <th style="width:15%;">Writer</th>
          <th style="width:30%;">Body</th>
          <th style="width:20%;">投稿日時</th>
          <th>パスワード</th>
          <th>編集</th>
          <th>削除</th>
        </tr>
      </thead>
      <tbody>
        <?php
        //XSS対策
        $writer = htmlspecialchars($row['writer']);
        $body = htmlspecialchars($row['body']);
        $timestamp = htmlspecialchars($row['timestamp']);
        $id = htmlspecialchars($row['id']);
        $thread_id = htmlspecialchars($row['thread_id']);
          ?>
          <!-- コメントの削除・編集form -->
          <?php foreach ($result as $row): ?>
          <form action="./massage_edit.php" method="post" name="bbs">
            <tr>
              <td><?= $writer ?></td>
              <td><?= $body ?></td>
              <td><?= $timestamp ?></td>
              <td>
                <input type="password" name="password" class="form-control" size="10">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="thread_id" value="<?= $thread_id ?>">
              </td>
              <td>
                <input type="submit" name="bbs_ope" value="編集" class="btn btn-success">
              </td>
              <td>
                <input type="submit" name="bbs_del" value="削除" class="btn btn-danger">
              </td>
            </tr>
          </form>
          <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- 文字入力がない場合の警告ポップアップ -->
  <script language="JavaScript">
  function check() {
    if(document.bbs.writer.value == "" || document.bbs.body.value == "" || document.bbs.password.value == "") { //formのwriterかbodyの値が空欄だった場合
      alert("writer・body・passwordを記入してください.");    //alertを表示する
      return ;
    }
  }
  </script>

</body>
</html>
