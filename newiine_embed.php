<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons|Material+Icons+Round" rel="stylesheet">
<link rel="stylesheet" href="https://github.com/beyondifttt/newiine_app/blob/main/newiine.css">
<style>
  html, body {
    margin: 0;
    padding: 0;
    background: transparent;
    overflow: visible; /* 上下左右にはみ出すお礼メッセージを切り取らない */
  }
  /* iframe内での余白調整。お好みで */
  .newiine_btn {
    margin: 0;
  }
</style>
</head>
<body>

<!-- いいねボタン改ここから -->
<button type="submit" class="newiine_btn" data-iinename="punknown" data-iineurl="">
  <span class="material-icons-round">favorite</span>
  <span class="newiine_count"></span>いいね

  <!-- お礼メッセージここから -->
  <div class="newiine_thanks newiine_thanks_down" style="display:none;">
    <div class="newiine_box">
      <p>ありがとうございます！</p>
    </div>
  </div>
  <!-- お礼メッセージここまで -->
</button>
<!-- いいねボタン改ここまで -->

<script src="https://github.com/beyondifttt/newiine_app/blob/main/newiine.js"></script>

<script>
  // iframeの高さを親(Blogger側)に伝える。お礼メッセージのポップアップで
  // 見切れないよう、ボタン+ポップアップの実際の高さをpostMessageで通知する。
  function reportHeight() {
    var h = document.body.scrollHeight;
    parent.postMessage({ type: 'newiine-resize', height: h }, '*');
  }
  window.addEventListener('load', reportHeight);
  // お礼メッセージの表示/非表示切り替え時にも高さを再計測
  var observer = new MutationObserver(reportHeight);
  observer.observe(document.body, { attributes: true, subtree: true, attributeFilter: ['style'] });
</script>

</body>
</html>
