<?php
    $url = "https://www.jma.go.jp/bosai/forecast/data/overview_forecast/130000.json";    // WebAPIのURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($ch);         //JSONデータ取得
    $json = json_decode($data);     //JSONをオブジェクト形式に変換
    curl_close($ch);                //切断

   $json->text = preg_replace('/\n\n/','<br>',$json->text);
?>