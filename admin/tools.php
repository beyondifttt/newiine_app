<?php
session_start();
$subtitle = '各種ツール | ';
include_once('inc/_header.php');
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'changed') : ?>
    <p class="message done">
      変更を保存しました。
    </p>
    <?php elseif (isset($_GET['mode']) && $_GET['mode'] === 'error') : ?>
    <p class="message error">
      設定の変更に失敗しました。入力内容を確認してください。
    </p>
    <?php endif;
    ?>

    <h2>各種ツール</h2>
    <?php
    if(file_exists('inc/_toolsinc.php')) :
        include_once('inc/_toolsinc.php');
        $tools = new createForm;
        echo $tools->printForm();
    else:
    ?>
    <p>
      いいねボタン・改のご支援版をご購入いただくと、以下のツールが使用できるようになります。
    </p>

    <ul>
      <li>
        <strong>既存のいいねログの、いいねボタンの設置URLをひとつに統合するツール</strong>
      </li>
      <li>
        <strong>毎日/毎週/毎月のいいねレポートをメール送信する機能</strong>
      </li>
    </ul>

    <p>
      <a href="https://doshop.booth.pm/" tager="_blank" rel="norefferer">ご購入はこちら</a>（外部サイトBOOTHへジャンプします）
    </p>
    
    <?php endif; ?>

</div>
</main>

<?php include_once('inc/_footer.php'); ?>
