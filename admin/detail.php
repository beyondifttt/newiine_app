<?php
session_start();
$subtitle = 'ボタン個別いいねログ | ';
include_once('inc/_header.php');
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    
    <?php
    $detail = new createIineLogs;
    if (isset($_GET["btnname"]) && !isset($_GET["month"])) {
      echo $detail->BtnDetail($_GET["btnname"]);
    } elseif (isset($_GET["btnname"]) && isset($_GET["month"])) {
      echo $detail->BtnDetail($_GET["btnname"], $_GET["month"]);
    } else {
      echo '<p>ログを表示したいいいねボタンが選択されていません。</p>';
    }

    ?>
  </form>

</div>
</main>

<?php include_once('inc/_footer.php'); ?>
