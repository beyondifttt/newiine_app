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

$include = get_included_files();
if (array_shift($include) === __FILE__) {
    die('このファイルへの直接のアクセスは禁止されています。');
}

include_once(dirname(__FILE__).'/../../newiine.php');

class newiineAdminConfig {

  private static $instance = null;
  private $settings = [];

  private function __construct() {

      if(file_exists(dirname(__FILE__).'/../../datas/setting/fav.dat')) {
        $favarr = file(dirname(__FILE__).'/../../datas/setting/fav.dat',FILE_IGNORE_NEW_LINES);
      } else {
        $favarr = array();
      }

          $this->settings = [
              'yesterday' => date('Y/m/d', strtotime('-1 day')),
              'perpage' => 20,
              'favarr' => $favarr,
              'page' => $_GET['page'] ?? 1,
              'mode' => $_GET['mode'] ?? ''
          ];
  }

  public static function getInstance() {
      if (self::$instance === null)  self::$instance = new self();
      return self::$instance;
  }

  public function get($key) {
      return $this->settings[$key] ?? null;
  }
}

class adminCsvHandler extends csvHandler {
  public static function getAllDatas() {
    global $btnOrder;
    
    setlocale(LC_ALL, 'ja_JP.UTF-8');

    $filenames = glob(dirname(__FILE__, 3). '/datas/*.csv');
    $allDatas = Array();

    if ($btnOrder === 'name_asc') {
      sort($filenames);
    } elseif($btnOrder === 'name_desc') {
      rsort($filenames);
    }
    
    foreach ($filenames as $key => $filename) {
      $newFileName = basename($filename, '.csv');
      $data = csvHandler::openCSV($newFileName, true);
      if(!empty($data[1])) {
        $allDatas[$newFileName] = $data[1];
      }
    }

    return $allDatas;
    
  }
}

class adminLikeSummary extends LikeSummary {
  public static function adminOutput(string $btnName) {
    $sum = 0;
    list(, $csvArray) = adminCsvHandler::openCSV($btnName, true);
    
    if($csvArray !== false) {
      foreach ($csvArray as $row) {
        $sum += (int)$row[5];
      }
    }
    
    return $sum;
  }
}

class createIineLogs {
  private $config;
  private $adminConfig;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = newiineConfig::getInstance();
    $this->adminConfig = newiineAdminConfig::getInstance();
  }
  
  private function getDetailArray($csvArray, $month, &$oldestDate) {
    $detailArray = [];

    $month2 = date('Y-m', strtotime($month . '/01'));
    $firstDay = 1;
    $lastDay = date('t', strtotime($month2 . '-01'));

    // 初期化
    for ($i = $firstDay; $i <= $lastDay; $i++) {
        $dayStr = sprintf('%02d', $i);
        $dateKey = $month . '/' . $dayStr;
        $today = $this->config->get('today');
        $formattedDate = str_replace("/", "", $dateKey);
        if($today < $formattedDate) continue;
        $detailArray[$dateKey] = 0;
    }

    foreach ($csvArray as $value) {
        $logDate = $value[3]; 

        // 最古の日付を設定
        if ($logDate < $oldestDate) {
            $oldestDate = $logDate;
        }
        
        $logDateFormatted = '20' . substr($logDate, 2, 2) . '/' . substr($logDate, -4, 2); // 2025/05
        if (strpos($logDateFormatted, $month) == 0) {
          $formattedDate = substr($logDate, 0, 4) . '/' . substr($logDate, 4, 2) . '/' . substr($logDate, 6, 2);
          // 日付が一致すればカウントを追加
          if (isset($detailArray[$formattedDate])) {
              $detailArray[$formattedDate] += (int)$value[5];
          }
        }
    }

    return $detailArray;
  }

  public function BtnDetail($btnName, $month = null) {
    list(, $csvArray) = adminCsvHandler::openCSV($btnName, true);

    if ($csvArray === false) {
        return 'データがありません。';
    }

    $month = $month ?? date("Y/m");
    $month2 = date('Y-m', strtotime($month . '/01'));
    $firstDay = 1;
    $lastDay = (int)date('t', strtotime($month . '/01'));
    $detailsum = 0;
    $oldestDate = $this->config->get('today');
    $detailArray = $this->getDetailArray($csvArray, $month, $oldestDate);

    // 日別のいいね回数を集計
    for ($i = $lastDay; $i >= $firstDay; $i--) {
        $day = str_pad($i, 2, '0', STR_PAD_LEFT);
        $key = "$month/$day";
        $today = $this->config->get('today');
        if ($today < $key) continue;

        $count = 0;
        foreach ($csvArray as $value) {
          $logDate = $value[3];
          $formattedDate = date('Y/m/d', strtotime($logDate));
            if ($formattedDate === $key) {
                $count += (int)$value[5];
            }
            if ($formattedDate < $oldestDate) {
                $oldestDate = $logDate;
            }
        }
        $detailArray[$key] = $count;
        $detailsum += $count;
      }

      // HTML出力部
      $html = '<h2>「' . htmlspecialchars($btnName) . '」のいいねログ - ' . date('Y年m月', strtotime($month . '/01')) . '</h2>';

      if ($oldestDate >= "$month/" . str_pad($firstDay, 2, '0', STR_PAD_LEFT)) {
          $html .= '<p class="memo">このいいねボタンでは' . date('Y/m/d', strtotime($oldestDate)) . '以前のいいねログデータがありません。<br>いいねログデータを保持する期間は<a href="setting.php">設定変更ページ</a>から変更できます。</p>';
      }

      $tmp = array();
      $array_result = array();

        foreach( $csvArray as $value ){
            // 配列に値が見つからなければ$tmpに格納
            if( !in_array( $value[0], $tmp ) ) {
             $tmp[] = $value[0];
             $array_result[] = $value;
            }
        }
      
        $html .= '<p class="memo"><strong>設置URL</strong><br>';
      for ($i=0; $i < count($array_result); $i++) { 
        $short_url = mb_strimwidth( $array_result[$i][0], 0, 40, '…', 'UTF-8' );
        $title = $array_result[$i][1];
        $html .= '<a href="'.$array_result[$i][0].' "target="_blank">'.$short_url.'<span style="font-size:90%;">（'.$title.'）</span></a>';
        if($array_result[$i] !== end($array_result)){
            $html .= '<br>';
        }
      }
        $html .= '</p>';

      // ページナビゲーションとテーブル作成
      $html .= $this->renderMonthNavigation($btnName, $month2, $oldestDate, $month, $firstDay, $lastDay);
      $html .= $this->renderTable($detailArray, $detailsum);
      $html .= $this->renderMonthNavigation($btnName, $month2, $oldestDate, $month, $firstDay, $lastDay);

      return $html;
    }
    
    private function renderMonthNavigation($btnName, $month2, $oldestDate, $month, $firstDay, $lastDay) {
        $prev = date('Y/m', strtotime($month2 . ' -1 month'));
        $next = date('Y/m', strtotime($month2 . ' +1 month'));
        $today = $this->config->get('today');
    
        $currentMonthStart = date('Y/m/01', strtotime($month.'/01'));
        $currentMonthEnd = date('Y/m/t', strtotime($month.'/01'));
    
        $html = '<div class="page">';
        
        // 前月リンク表示条件
        if ($oldestDate < $currentMonthStart) {
            $html .= '<a href="?btnname=' . urlencode($btnName) . '&month=' . $prev . '" class="prev"><span class="material-icons">navigate_before</span>前月</a>';
        } else {
            $html .= '<div></div>';
        }
    
        // 次月リンク表示条件（未来を表示しない）
        if ($today > $currentMonthEnd && $next <= date('Y/m', strtotime($today))) {
            $html .= '<a href="?btnname=' . urlencode($btnName) . '&month=' . $next . '" class="next">次月<span class="material-icons">navigate_next</span></a>';
        } else {
            $html .= '<div></div>';
        }
    
        $html .= '</div>';
        return $html;
    }


    private function renderTable($detailArray, $detailsum) {
      $week = ['日','月','火','水','木','金','土'];
      $html = '<table><thead><tr><th>月の合計</th><td>' . $detailsum . ' 回</td></tr></thead>';
      foreach (array_reverse($detailArray) as $key => $count) {
          $dow = date('w', strtotime($key));
          $html .= '<tr><th class="week_' . $dow . '">' . $key . '（' . $week[$dow] . '）</th><td>' . ($count === 0 ? '-' : $count . ' 回') . '</td></tr>';
      }
      $html .= '</table>';
      return $html;
    }

}

class newiine_admin {
  private $config;
  private $adminConfig;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = newiineConfig::getInstance();
    $this->adminConfig = newiineAdminConfig::getInstance();
  }

    public function recentlyReport() {
      if(isset($_GET['period']) && $_GET['period'] === 'month') {
        $end = date('Y/m/d', strtotime('today'));
        $start = date('Y/m/d', strtotime('-29 days'));
      } else {
        $end = date('Y/m/d', strtotime('today'));
        $start = date('Y/m/d', strtotime('-6 days'));
      }

      $result = specifiedPeriodDatas::getIineDatas($start, $end);
      
    ksort($result);
      $html = '';

      if(empty($result)) {
        $html .= '<p>データがありません。</p>';
      } else {
        $html .= '<form method="post" action="inc/_fav.php">';
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '  <tr>';
        $html .= '    <th>日付</th>';
        $html .= '    <th>いいねボタン</th>';
        $html .= '    <th>回数</th>';
        $html .= '  </tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($result as $date => $buttons) {

          $rowspan = count($buttons) +1;

          $week = ['日','月','火','水','木','金','土'];
          $dow = date('w', strtotime($date));
              
          
          $html .= '<tr class="noborder">';
          $html .= '<th rowspan="'.$rowspan.'" class="week_'.$dow.'">'.$date.'（'.$week[$dow].'）</th>';
          
          $add = '';
          $total = 0;

          $f = true;

          foreach ($buttons as $name => $data) {
            $sum = 0;
                foreach ($data as $val) {
                    $sum += $val[5];
                }
            $total += $sum;
            if(!$f)  {
              $add .= '<tr class="noborder">';
            }
            $add .= '<td class="noborder"><a href="detail.php?btnname='.$name.'"">'.$name.'</a>';
            if (in_array($name, $this->adminConfig->get('favarr'), true)) {
              $add .= '<button type="submit" class="fav_button faved" name="favorite" value="'.$name.'"><span class="material-icons" style="font-size:16px;vertical-align:middle">favorite</span></input></td>';
            } else {
              $add .= '<button type="submit" class="fav_button" name="favorite" value="'.$name.'"><span class="material-icons" style="font-size:16px;vertical-align:middle">favorite</span></input></td>';
            }
            $add .= '<td class="noborder">'.$sum.'回</td>';
            $add .= '</tr>';
              $f = false;
          }
        $html .= $add;
        $html .= '<tr><td class="noborder"><strong>合計</strong></td><td class="noborder"><strong>'.$total.'回</strong></td></tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</form>';

      }

      echo $html;
    }

    public function showSearchBox() {
      $s = null;
      if(isset($_GET['s'])) {
        $s = $_GET['s'];
      }

      $ret = '';
      $ret .= '
      <form method="get" action="" class="searchbox">
        <input type="text" name="s" placeholder="いいねボタン名を入力" value="'.$s.'">
        <button type="submit">検索</button>
      </form>';

      return $ret;
    }

    public function allBtnReport($mode = null) {
      $allDatas = adminCsvHandler::getAllDatas();
      $selectedDatas = array();
      $sums = array();
      $todaySums = array();
      $yesterdaySums = array();
      $s = null;

      if(isset($_GET['s'])) {
        $s = $_GET['s'];
      }

      if ($mode === 'fav') {
        $newArr = array();
        foreach ($allDatas as $key => $datas) {
         if (in_array($key, $this->adminConfig->get('favarr'))) {
          $newArr[$key] = $allDatas[$key];
         }
        }
        $allDatas = $newArr;
      }

      if($s) {
        $keywords = preg_split('/\s+/', $_GET['s']); // 空白で分割（全角・半角対応したい場合はmb_convert_kana使う）
        foreach ($allDatas as $key => $value) {
            $matched = true;
            foreach ($keywords as $word) {
                if (mb_strpos($key, $word) === false) {
                    $matched = false;
                    break;
                }
            }
            if (!$matched) {
                unset($allDatas[$key]);
            }
        }
      }

      // それぞれのボタンが今日もしくは昨日いいねされたか判定
      foreach ($allDatas as $key => $datas) {
        $todaySums[$key] = 0;
        $yesterdaySums[$key] = 0;
        for ($i=0; $i < count($datas); $i++) { 
          if($datas[$i][3] === $this->config->get('today') || $datas[$i][3] === $this->adminConfig->get('yesterday')) {
            $selectedDatas[$key] = $datas[$i];
            if($datas[$i][3] === $this->config->get('today')){
              $todaySums[$key] = $todaySums[$key] + $datas[$i][5];
            } elseif($datas[$i][3] === $this->adminConfig->get('yesterday')) {
              $yesterdaySums[$key] = $yesterdaySums[$key] + $datas[$i][5];
            }
          }
        }
        if(!empty($selectedDatas[$key])) {
          $sums[$key] = adminLikeSummary::adminOutput($key);
        }
      }

      $html = '';
      if(empty($allDatas)) {
        $html .= '<p>データがありません。</p>';
      } else {
        if ($this->adminConfig->get('mode') === 'rank') {
          $html .= '<p style="text-align:right;font-size:90%;"><a href="?page=1">いいねボタン名前順に並べ替え</a></p>';
        } else {
          $html .= '<p style="text-align:right;font-size:90%;"><a href="?mode=rank">いいね数が多い順に並べ替え</a></p>';
        }

        $html .= '<form method="post" action="inc/_fav.php">';
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '  <tr>';
        $html .= '    <th>ボタン名</th>';
        $html .= '    <th>設置アドレス</th>';
        $html .= '    <th>いいね数</th>';
        $html .= '  </tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $countitem = 0;
        $startitem = ($this->adminConfig->get('page') - 1) * ($this->adminConfig->get('perpage'));
        $enditem = $this->adminConfig->get('page') * $this->adminConfig->get('perpage') -1;
        
        $prevfrag = false;
        $nextfrag = false;
        $sumarray = array();

        $newarray = array();

        if($this->adminConfig->get('mode') === 'rank') {

          foreach ($allDatas as $key => $data) {
          $sumarray[$key] = adminLikeSummary::adminOutput($key);
          }

          arsort($sumarray);
          
          foreach ($sumarray as $key => $data) {
            $newarray[$key] = $allDatas[$key];
          }

          $allDatas = $newarray;

        }

        foreach ($allDatas as $key => $data) {

          if($countitem > $enditem) {
            $nextfrag = true;
            break;
          } elseif ($countitem < $startitem) {
            ++$countitem;
            $prevfrag = true;
            continue;
          }
          
        $tmp = array();
        $array_result = array();

          foreach( $allDatas[$key] as $value ){
              // 配列に値が見つからなければ$tmpに格納
              if( !in_array( $value[0], $tmp ) ) {
               $tmp[] = $value[0];
               $array_result[] = $value;
              }
          }

          $html .= '<tr>';
          $html .= '<th><a href="detail.php?btnname='.$key.'"">'.$key.'</a>';

          if (in_array( $key, $this->adminConfig->get('favarr'))) {
            $html .= '<button type="submit" class="fav_button faved" name="favorite" value="'.$key.'"><span class="material-icons" style="font-size:16px;vertical-align:middle">favorite</span></input></th>';
          } else {
            $html .= '<button type="submit" class="fav_button" name="favorite" value="'.$key.'"><span class="material-icons" style="font-size:16px;vertical-align:middle">favorite</span></input></th>';
          }

          $html .= '<td>';
          for ($i=0; $i < count($array_result); $i++) { 
            $short_url = mb_strimwidth( $array_result[$i][0], 0, 40, '…', 'UTF-8' );
            $title = $array_result[$i][1];
            $html .= '<a href="'.$array_result[$i][0].' "target="_blank">'.$short_url.'<span style="font-size:90%;">（'.$title.'）</span></a>';
            if($array_result[$i] !== end($array_result)){
                $html .= '<br>';
            }
          }

          $html .= '</td>';
          $html .= '  <td>';
          $html .= '   今日：'.$todaySums[$key].'<br>';
          $html .= '    昨日：'.$yesterdaySums[$key].'<br>';
          $html .= '   合計：'.adminLikeSummary::adminOutput($key);
          $html .= ' </td>';
          $html .= ' </tr>';

          ++$countitem;
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</form>';
        
        $html .= '<div id="pagenation">';

        $allitemcount = count($allDatas);
        $lastofpage = ceil($allitemcount / $this->adminConfig->get('perpage'));

        if( $prevfrag  && $this->adminConfig->get('mode') === 'rank' ) {
          $n = $this->adminConfig->get('page') - 1;
          $html .= '<a href="?page=1&mode=rank" class="first"><<</a>';
          $html .= '<a href="?page='.$n.'&mode=rank" class="prev">前の'.$this->adminConfig->get('perpage').'件</a>';
        } elseif( $prevfrag ) {
          $n = $this->adminConfig->get('page') - 1;
          $html .= '<a href="?page=1" class="first"><<</a>';
          $html .= '<a href="?page='.$n.'" class="prev">前の'.$this->adminConfig->get('perpage').'件</a>';
        }

        if( $prevfrag  || $nextfrag ) {
          $html .= '<div class="current"><p>'.$this->adminConfig->get('page').' / '.$lastofpage.'</p></div>';
        }

        if( $nextfrag && $this->adminConfig->get('mode') === 'rank' ) {
          $n = $this->adminConfig->get('page') + 1;
          $html .= '<a href="?page='.$n.'&mode=rank" class="next">次の'.$this->adminConfig->get('perpage').'件</a>';
          $html .= '<a href="?page='.$lastofpage.'&mode=rank" class="last">>></a>';
        } elseif( $nextfrag ) {
          $n = $this->adminConfig->get('page') + 1;
          $html .= '<a href="?page='.$n.'" class="next">次の'.$this->adminConfig->get('perpage').'件</a>';
          $html .= '<a href="?page='.$lastofpage.'" class="last">>></a>';
        }

        $html .= '</div>';

      }

      echo $html;
    }

}

class show {
    // いいね拒否しているIPアドレスを表示する関数
    public static function denyIP() {
      $html = '';
      
      $IPs = file(dirname(__FILE__).'/../../datas/setting/deny.dat');

      if(empty($IPs)) {
        $html .= '<p class="memo">現在いいねを拒否しているIPアドレスはありません。<br>';
        $html .= '※いいねした人のIPアドレスを調べる方法は<a href="tips.php#how_to_add_IP">こちら</a></p>';
      } else {
        $html .= '<p class="memo">現在いいねを拒否しているIPアドレス：<br>';
        foreach ($IPs as $IP) {
          $html .= $IP . '<br>';
        }
        $html .= '※いいねした人のIPアドレスを調べる方法は<a href="tips.php#how_to_add_IP">こちら</a><br>';
        $html .= '※拒否IPアドレスを削除する方法は<a href="tips.php#how_to_delete_IP">こちら</a>';
        $html .= '</p>';
      }

    echo $html;
  }
}

class specifiedPeriodDatas {
  public static function getIineDatas($start, $end) {
    $alldatas = adminCsvHandler::getAllDatas();
    $diff = (strtotime($end) - strtotime($start)) / ( 60 * 60 * 24);
  
    if($alldatas === false || empty($alldatas)) {
      return false;
    }
    
    $result = [];
  
    foreach ($alldatas as $key => $entries) {
      foreach ($entries as $row => $entry) {
        for ($i = 0; $i <= $diff; $i++) {
          $day = date('Y/m/d', strtotime($start . '+' . $i . 'days'));
          if ($entry[3] === $day) {
            // まず $entry をコピー
            $newEntry = $entry;

            // 条件が合えば初回フラグを追加
            if ($row === 0) {
              $newEntry[6] = true;
            }

            // 結果に追加
            $result[$day][$key][] = $newEntry;
          }
        }
      }
    }
  
    return $result;
  }
}

  ?>
