<?php

///////////////////////////////////////////////////
// いいねボタン改 Ver3.1
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

header('Content-Type: text/html; charset=UTF-8');

$include = get_included_files();
if (array_shift($include) === __FILE__) {
    die('このファイルへの直接のアクセスは禁止されています。');
}

date_default_timezone_set("Asia/Tokyo");
$newiineVersion = '3.1';

class newiineConfig {

  private static $instance = null;
  private $settings = [];
  private $visitorIP;

  private function __construct() {
      $this->visitorIP = $_SERVER["REMOTE_ADDR"]; // ここで設定する

      // _config.php をインクルードして設定値を取得
      $settingsFile = dirname(__FILE__) . '/admin/inc/_config.php';

      if (file_exists($settingsFile)) {
          include $settingsFile;

          

          // _config.php 内の変数をクラスの設定に取り込む
          $this->settings = [
              'password' => $password ?? 'pass',
              'limitPost' => $limitPost ?? 10,
              'btnOrder' => $btnOrder ?? 'name_asc',
              'saveperiod' => $saveperiod ?? 365,
              'today' => date("Y/m/d"),
              'time' => date("H:i:s"),
              'sorteddate' => date("Y/m/d", strtotime('-'.$saveperiod.' day')),
              'visitorIP' => $this->visitorIP,
          ];

      } else {
        throw new Exception("設定ファイルが見つかりません: $settingsFile");
      }
  }

  public static function getInstance() {
      if (self::$instance === null)  self::$instance = new self();
      return self::$instance;
  }

  public function get($key) {
      return $this->settings[$key] ?? null;
  }
}


class newiineCheckSendData {

  // URL名がindex.htmlもしくはindex.phpで終わる場合はURLを丸める
  public static function checkURL($url) {
    $filenames = array('index.html', 'index.php');
    foreach ($filenames as $filename) {
      if (strpos($url, $filename) !== false)  $url = rtrim($url, $filename);
    }
    return $url;
  }

  // タグなどの送信を拒否
  public static function entity($txt) {
    $newTxt = htmlentities($txt);
    return $newTxt;
  }

  public static function doublequotation($txt) {
    $newTxt = '"' .$txt. '"';
    return $newTxt;
  }
  
  // URLからタイトルを取得する
  public static function getHTMLtitle($URL) {
    $http_response_header = null;
    if( $source = @file_get_contents($URL)) {
      //文字コードをUTF-8に変換し、正規表現でタイトルを抽出
      if (preg_match('/<title>(.*?)<\/title>/i', mb_convert_encoding($source, 'UTF-8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS'), $result)) {
          $title = $result[1];
      } else {
          //TITLEタグが存在しない場合
          $title = 'タイトルを取得できませんでした。';
      }
      
        } else {
                
          //エラー処理
          if($http_response_header === null) {
            $title = "指定したページが見つかりませんでした。data-iineurlの値を確認してください。";
          } elseif(count($http_response_header) > 0){
            //「$http_response_header[0]」にはステータスコードがセットされている
            $status_code = explode(' ', $http_response_header[0]);  //「$status_code[1]」にステータスコードの数字だけが入る

            //エラーの判別
            switch($status_code[1]){
                //404エラーの場合
                case 404:
                    $title = "指定したページが見つかりませんでした。data-iineurlの値を確認してください。";
                    break;
                //500エラーの場合
                case 500:
                  $title = "指定したページがあるサーバーにエラーがあります";
                    break;
                //その他のエラーの場合
                default:
                $title = "何らかのエラーによって指定したページのデータを取得できませんでした";
            }
        }else{
            //タイムアウトの場合 or 存在しないドメインだった場合
            $title = "タイムエラー or URLが間違っています";
        }
      }
    return $title;
  }

}

class csvHandler {
  private $config;

  // コンストラクタ宣言
  public function __construct() {
    var_dump($this->config);
  }

  // CSVを開いて当該いいねボタンに関するデータを引っ張り出す関数
  public static function openCSV($planeBtnName, $mode = null, $URL = null) {
    $config = newiineConfig::getInstance();
    $visitorIP = $config->get('visitorIP');
    $today = $config->get('today');
  
    $btnName = mb_convert_encoding($planeBtnName, "UTF-8");
    $filename = ($mode === true) ? dirname(__FILE__, 1) . '/datas/' . $btnName . '.csv' : 'datas/' . $btnName . '.csv';
  
    // カウントモードかつファイルが存在しないなら 0 を返す
    if ($mode === 'count' && !file_exists($filename)) {
      return 0;
    }
  
    $csvArray = [];
    $num = false;
  
    if (file_exists($filename)) {
      if (($fp = fopen($filename, "r")) !== false) {
        while (($row = fgetcsv($fp, 0, ",", "\"", "\\")) !== false) {
          $csvArray[] = $row;
        }
        fclose($fp);
      }
  
      // countモード：IP + 日付で合致する行のカウント合計を返す
      if ($mode === 'count') {
        $count = 0;
        foreach ($csvArray as $row) {
          if ($row[2] === $visitorIP && $row[3] === $today) {
            $count += (int)$row[5];
          }
        }
        return $count;
      }
  
      // 通常モード：該当データの行番号を探す
      if ($mode === null) {
        foreach ($csvArray as $key => $row) {
          if ($row[2] === $visitorIP && $row[3] === $today && $row[0] === $URL) {
            $num = $key;
            break; // 最初に見つかった行でOKなら break 推奨
          }
        }
      }
    } else {
      $num = 0;
      $csvArray = false; // 読み取り失敗でも明示的に false
    }
  
    return [$num, $csvArray];
  }

  public static function rewriteCSV($planeBtnName, $csvArray, $num) {
    $btnName = mb_convert_encoding($planeBtnName, "UTF-8");
    $filename = 'datas/' . $btnName . '.csv';
  
    if (($fp = fopen($filename, 'w')) === false) {
      // ファイル書き込みに失敗したら処理を中断（or ログに出すなど）
      return false;
    }
  
    foreach ($csvArray as $key => $row) {
      if ($key !== $num) {
        // エスケープ処理
        $row[0] = newiineCheckSendData::doublequotation($row[0]);
        $row[1] = newiineCheckSendData::doublequotation($row[1]);
      }
  
      $line = implode(',', $row);
      fwrite($fp, $line . "\n");
    }
  
    fclose($fp);
    return true;
  }
  
}

class newiineToken {
  private $config;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = newiineConfig::getInstance();
  }

  public function makeToken() {
    if (isset($_SESSION['csrf_token'])) {
        return $_SESSION['csrf_token']; // すでにあればそれを返す
    }

    // 暗号学的に安全なランダムなバイナリを生成し、それを16進数に変換することでASCII文字列に変換します
    if (function_exists('random_bytes')) {
      $toke_byte = random_bytes(16);
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $toke_byte = openssl_random_pseudo_bytes(16);
    } else {
        // 最後の手段として mt_rand() ベースの疑似ランダムを使用
        $toke_byte = '';
        for ($i = 0; $i < 16; $i++) {
            $toke_byte .= chr(mt_rand(0, 255));
        }
    }
  
    $csrf_token = bin2hex($toke_byte);

    // 生成したトークンをセッションに保存します
    $_SESSION['csrf_token'] = $csrf_token;
    return $csrf_token;
  }
}

class newiineSetupButton {
  public static function checkTodaysCount($btnName, $iineNewCountLimit) {
    $config = newiineConfig::getInstance();
    $count = csvHandler::openCSV($btnName, 'count');
    $countLimit = '';
    $iineLimit = $config->get('iineLimit');

      if ($iineNewCountLimit !== "false") {
        // 個別にいいね回数上限が設定されていれば、それに従う
        $countLimit = $iineNewCountLimit;
      } else {
        $countLimit = $iineLimit;
      }

      if ($count < $countLimit) {
        // 上限に達していない場合はfalseを返す
        return false;
      } else {
        // 上限に達している場合はtrueを返す
        return true;
      }
    }
}

class IineManager {
  private $config;

  public function __construct() {
    $this->config = newiineConfig::getInstance();
  }

  public function handleLike($postPath, $btnName, $iineLimit) {
    $visitorIP = $this->config->get('visitorIP');

    if ($iineLimit === 'false') {
      $iineLimit = $this->config->get('limitPost');
    }

    if (newiineSetupButton::checkTodaysCount($btnName, $iineLimit)) {
      echo 'upper';
      return;
    }

    if (IPBlocker::isDenied($visitorIP)) {
      echo 'denyIP';
      return;
    }

    LikeRecorder::record($btnName, $postPath, $this->config);
    LikeSummary::output($btnName, $visitorIP);
  }
}

class LikeRecorder {
  public static function record(string $btnName, string $postPath, $config) {
    list($num, $csvArray) = csvHandler::openCSV($btnName, null, $postPath);

    $filename = 'datas/' . $btnName . '.csv';
    $today = $config->get('today');
    $time = $config->get('time');
    $visitorIP = $config->get('visitorIP');

    $title = newiineCheckSendData::doublequotation(
      newiineCheckSendData::getHTMLtitle($postPath)
    );
    $url = newiineCheckSendData::doublequotation($postPath);

    if ($num === false || !$csvArray) {
      DataSorter::sort($btnName, $postPath, $config);
      $data = [$url, $title, $visitorIP, $today, $time, 1];
      $fp = fopen($filename, 'a');
      if (flock($fp, LOCK_EX)) {
        fwrite($fp, implode(',', $data) . "\n");
        flock($fp, LOCK_UN);
      }
      fclose($fp);
    } else {
      $count = $csvArray[$num][5];
      $newData = [$url, $title, $visitorIP, $today, $time, $count + 1];
      array_splice($csvArray, $num, 1, [$newData]);
      csvHandler::rewriteCSV($btnName, $csvArray, $num);
    }
  }
}

class LikeSummary {
  public static function output(string $btnName, string $visitorIP) {
    list(, $csvArray) = csvHandler::openCSV($btnName);

    if (!is_array($csvArray)) {
      echo json_encode([0, false]);
      return;
    }
    
    $sum = 0;
    $clickedToday = false;

    foreach ($csvArray as $row) {
      $sum += (int)$row[5];
      if (!$clickedToday && $row[2] === $visitorIP) {
        $clickedToday = true;
      }
    }

    echo json_encode([$sum, $clickedToday]);
  }
}

class DataSorter {
  public static function sort(string $btnName, string $url, $config): void {
    list(, $csvArray) = csvHandler::openCSV($btnName);
    if ($csvArray === false) return;

    $today = strtotime($config->get('today'));
    $time = $config->get('time');
    $savePeriod = $config->get('saveperiod');
    $sortedDate = $config->get('sorteddate');

    $firstTime = strtotime($csvArray[0][3]);
    if (($today - $firstTime) / 86400 <= $savePeriod) return;

    $sum = 0;
    $newArray = [];

    foreach ($csvArray as $row) {
      if ((($today - strtotime($row[3])) / 86400) > $savePeriod) {
        $sum += $row[5];
      } else {
        $row[0] = newiineCheckSendData::doublequotation($row[0]);
        $row[1] = newiineCheckSendData::doublequotation($row[1]);
        $newArray[] = $row;
      }
    }

    $summary = [
      newiineCheckSendData::doublequotation($url),
      newiineCheckSendData::doublequotation(newiineCheckSendData::getHTMLtitle($url)),
      'admin',
      $sortedDate,
      $time,
      $sum
    ];

    array_unshift($newArray, $summary);

    $filename = 'datas/' . $btnName . '.csv';
    $fp = fopen($filename, 'w');
    foreach ($newArray as $v) {
      fwrite($fp, implode(',', $v) . "\n");
    }
    fclose($fp);
  }
}

class IPBlocker {
  public static function isDenied(string $ip): bool {
    $denyList = file('datas/setting/deny.dat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($denyList as $denyIP) {
      $denyIP = trim($denyIP);

      // ワイルドカードを正規表現に変換
      $pattern = preg_quote($denyIP, '/');         // ドット等をエスケープ
      $pattern = str_replace('\*', '.*', $pattern); // * → 任意の長さ
      $pattern = str_replace('\?', '.', $pattern);  // ? → 任意の1文字
      $pattern = '/^' . $pattern . '$/';

      if (preg_match($pattern, $ip)) {
        return true;
      }
    }

    return false;
  }
}

?>