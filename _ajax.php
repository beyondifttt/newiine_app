<?php

///////////////////////////////////////////////////
// いいねボタン改 Ver2.2
// 製作者    ：ガタガタ
// サイト    ：https://do.gt-gt.org/
// ライセンス：MITライセンス
// 全文      ：https://ja.osdn.net/projects/opensource/wiki/licenses%2FMIT_license
// 公開日    ：2021.12.30
// 最終更新日：2025.06.19
//
// このプログラムはどなたでも無償で利用・複製・変更・
// 再配布および複製物を販売することができます。
// ただし、上記著作権表示ならびに同意意志を、
// このファイルから削除しないでください。
///////////////////////////////////////////////////

session_start();
require_once(__DIR__ . '/newiine.php');

$newiineApp = new IineManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!isset($_POST['mode'])) {
      throw new \Exception('mode not set!');
    }

    switch ($_POST['mode']) {
      case 'check':
        // URLとボタン名を整形
        $postPath = newiineCheckSendData::checkURL($_POST['path'] ?? '');
        $btnName  = newiineCheckSendData::entity($_POST['buttonname'] ?? '');
        $iineNewCountLimit = $_POST['iineNewCountLimit'];
        $token = $_POST['token'];

        // 結果を出力
        echo $newiineApp->handleLike($postPath, $btnName, $iineNewCountLimit, $token);
        exit;
    }
  } catch (Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo $e->getMessage();
    exit;
  }
} else {
  try {
    echo LikeSummary::output($_GET['buttonname'] ?? '', $_SERVER["REMOTE_ADDR"]);
    exit;
  } catch (Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo $e->getMessage();
    exit;
  }
}
