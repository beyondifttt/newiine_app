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

include_once(dirname(__FILE__).'/_core.php');
$koibumiAdm = new newiine_admin();
$rowurl = $_SERVER['HTTP_REFERER'];
$box = parse_url($rowurl);
$url = $box['scheme'] .'://'. $box['host'] . $box['path'];

if (isset($_POST['favorite'])) {
    $filename = dirname(__FILE__).'/../../datas/setting/fav.dat';
    $names = array();
    $newname = $_POST['favorite'];
    $mode = 'add';

    if (!file_exists($filename)) {
        $fp = fopen($filename, 'w');
        fwrite($fp, $newname . "\n");
        // ファイルを閉じる
        fclose($fp);
    } else {

        $col = 0;

        $names = file($filename,FILE_IGNORE_NEW_LINES);
    
        if (in_array($newname, $names)) {
            $newarray = array();
            foreach($names as $item) {
                if($item !== $newname) {
                    $newarray[] = $item;
                }
            }
            $names = $newarray;
            $mode = 'delete';
        } else {
            $names[] = $newname;
        }
    
        $i = 0;
        $fp = fopen($filename, 'w');
        while ($i < count($names)) {
            fwrite($fp, $names[$i] . "\n");
            ++$i;
        }
        // ファイルを閉じる
        fclose($fp);
    }
    
}

header("Location:$url?fav=$mode");