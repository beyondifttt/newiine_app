<?php
include_once('_config.php');
define("PASSWORD", "$password");

    // HTMLエスケープ関数 <- 取得した任意のHTMLコードを実行する脆弱性に対処します
    function to_html($str){
        return htmlspecialchars($str, ENT_QUOTES|ENT_HTML5, "UTF-8");
    }

    //======= ログイン状況の確認 ==============

    if(isset($_SESSION['data'])){
        $login = true;
        $data = $_SESSION['data'];
    } elseif(isset($_SESSION["NEWIINE_LOGIN"]) && $_SESSION["NEWIINE_LOGIN"] != null && sha1(PASSWORD) === $_SESSION["NEWIINE_LOGIN"]){
        $login = true;
    } else {
        $login = false;
    }

    // ログインしていない場合、ログインページに遷移
    if(!$login){
    header('Location: index.php?message=セッションの期限が終了したか、不正なアクセスです。');
        exit;
    }

    if(isset($_COOKIE["COOKIE_NEWIINE"]) && $_COOKIE["COOKIE_NEWIINE"] != ""){
      $_SESSION["NEWIINE_LOGIN"] = $_COOKIE["COOKIE_NEWIINE"];
  }

    //======= ログイン状況の確認END ==============

include_once('inc/_core.php');
$newiineAdm = new newiine_admin();

if(isset($_GET['fav'])) {
  $mode = $_GET['fav'];
} else {
  $mode = false;
}
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo $subtitle; ?>いいねボタン改 管理画面</title>
  <link rel="stylesheet" href="style.css?ver=3.1">
  <link rel="stylesheet" href="chart.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Round" rel="stylesheet">
</head>
<body>
<div id="container">
<header>
  <div id="header">
    <h1><a href="admin.php">いいねボタン改 管理画面</a></h1>
    <form action="logout.php" method="post">
      <button type="submit" name="logout" id="logout">ログアウト</button>
    </form>
  </div>
    <?php if($password === 'pass') : ?>
    <div id="changepass">
      <p><span class="material-icons">warning</span> パスワードが初期設定のままです。設定変更ページから変更してください。</p>
    </div>
  <?php endif;
  if($mode) :
  ?>
  <div id="message">
    <p>
      <?php if($mode === 'add') : ?>
        お気に入りに追加しました。
      <?php elseif($mode === 'delete') : ?>
        お気に入りから削除しました。
      <?php endif; ?>
    </p>
  </div>
  <?php endif; ?>
</header>
