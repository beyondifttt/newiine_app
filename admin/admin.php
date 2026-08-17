<?php
session_start();
$subtitle = '';
include_once('inc/_header.php');

if(isset($_GET['period']) && $_GET['period'] === 'month') {
  $period = 'month';
} else {
  $period = 'week';
}
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    
    <?php if($period === 'week'): ?>
    <h2>直近１週間の日別いいね数</h2>
      
      <div id="chart-container" class="<?php echo $period; ?>"></div>
      
    <a href="admin.php?period=month" class="btn">直近1ヶ月のデータを見る</a>
    <?php else: ?>
    <h2>直近１ヶ月の日別いいね数</h2>
      
      <div id="chart-container" class="<?php echo $period; ?>"></div>

    <a href="admin.php?period=week" class="btn">直近1週間のデータを見る</a>
    <?php endif; ?>

    <p class="memo" style="margin-top:40px">
      指定期間中にいいねされたボタンの一覧です。<br>
      ボタン名をクリックすると月別のいいねログデータが見られます。
    </p>

    <?php
    echo $newiineAdm->recentlyReport();
    ?>
  </form>

</div>
</main>

<?php include_once('inc/_footer.php'); ?>
