<?php
session_start();
$subtitle = '設定変更 | ';
include_once('inc/_header.php');
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    <?php if (isset($_GET['mode']) && $_GET['mode'] === 'changed') : ?>
    <p class="message done">
      変更を保存しました。
    </p>
    <?php endif;
    ?>

    <h2>各種設定</h2>
    <form method="post" action="inc/_setting.php" autocomplete="off" onsubmit="return submitSetting()">

      <div class="tab_wrap">
        <input id="tab1" type="radio" name="tab_btn" checked>
        
        <div class="panel_area">

          <div id="panel1" class="tab_panel">
            <dl>
              <dt>いいねボタン一覧の表示順</dt>
              <dd>
                <label><input type="radio" name="btn_order" value="name_asc" <?php if($btnOrder === 'name_asc') { echo 'checked';} ?>> 名前昇順</label><br>
                <label><input type="radio" name="btn_order" value="name_desc" <?php if($btnOrder === 'name_desc') { echo 'checked';} ?>> 名前降順</label>
              </dd>
            </dl>
            <dl>
              <dt>同一IPによる１日のいいね数上限</dt>
              <dd><input type="number" name="limitPost" id="limitpost" value="<?php echo $limitPost; ?>">
            <p class="alert" id="alert_limitpost"></p></dd>
            </dl>
            <dl>
              <dt>詳細ないいねログデータの保存日数</dt>
              <dd><input type="number" name="saveperiod" id="saveperiod" value="<?php echo $saveperiod; ?>">
              <p class="alert" id="alert_saveperiod"></p></dd>
            </dl>
            <dl>
              <dt>いいねを拒否するIPアドレス</dt>
              <dd>
                <input type="text" name="banIP" value="" placeholder="12.345.678.90"><br>
                <?php
                  echo show::denyIP();
                ?>
                <p style="margin-top:15px;">
                  <code>123.45.67.89</code>の他に、<code>123.45.*.*</code>のようにワイルドカードを使用して広範囲のIPアドレスをブロックすることができます。
                </p>
              </dd>
            </dl>
            <dl>
              <dt>管理画面ログインパスワードの変更</dt>
              <dd><label><input type="password" name="newpw" id="newpw" placeholder="新パスワード" autocomplete="new-password"></label><br>
                <label><input type="password" name="newpw-confirm" id="confirm-pw" placeholder="新パスワード（確認用）"></label>
              <p class="alert" id="alert_pass"></p></dd>
              </dl>
            </div>

        </div>
      </div>

      <button type="submit">設定を変更</button>
      <!-- ※デモなので設定を変更できません。 -->

    </form>
  </div>
</main>

  <?php include_once('inc/_footer.php'); ?>
