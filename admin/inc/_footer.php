<?php include_once('../newiine.php'); ?>
<footer>
  <p><a href="https://do.gt-gt.org/" target="_blank">いいねボタン改（Ver<?php echo $newiineVersion; ?>）</a></p>
</footer>

</div>

<script src="func.js"></script>

<?php
if (preg_match('/admin\.php($|\?)/', $_SERVER['REQUEST_URI'])):
?>
<script src="chart.js"></script>

<?php endif; ?>
</body>
</html>
