<?php
    require_once('helpers/MemberDAO.php');
    // セッション開始
    session_start();

    // セッションに会員情報がなければログインページへリダイレクト
    if (empty($_SESSION['member'])) {
        header('Location: login-register.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店舗情報</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
  </head>
  <body>
    <div class="container">
      
    <header class="page-header">
               <a href="javascript:history.back()" class="header-back-button">
           <i class="fa-solid fa-chevron-left"></i>
        </a>
            <h1>お店情報</h1>
    </header>

      <main class="store-detail-page">
        
        <div class="detail-section">

          <div class="detail-item">
            <span class="item-label">店名</span>
            <div class="item-content">
              <span class="content-text" style="font-weight: bold; font-size: 15px;">テスト店舗　千葉店</span>
            </div>
          </div>

          <div class="detail-item">
            <span class="item-label">店舗画像</span>
            <div class="item-content">
             <img src="images/history02.jpg" alt="店舗の画像" class="detail-store-image">
             </div>
          </div>


          <div class="detail-item">
            <span class="item-label">住所</span>
            <div class="item-content">
              <span class="content-text">千葉県千葉市中央区富士見２-４-３ 喜楽ビル 2F</span>
              <a href="#" class="map-icon"><i class="fa-solid fa-map-location-dot"></i></a>
            </div>
          </div>

          <div class="detail-item">
            <span class="item-label">アクセス</span>
            <div class="item-content">
              <span class="content-text">ＪＲ千葉駅東口徒歩３分</span>
            </div>
          </div>

          <div class="detail-item">
            <span class="item-label">電話番号</span>
            <div class="item-content">
              <a href="tel:000-000-0000">電話する</a>
            </div>
          </div>

          <div class="detail-item">
            <span class="item-label">営業時間</span>
            <div class="item-content">
              <div class="multi-line">月～金: 17:00～翌4:30 <br> 土、日、祝日: 11:30～翌4:30 
<span class="notice">営業時間については、各店にお問い合わせ頂くか公式ホームページにてご確認下さい。</span>
              </div>
            </div>
          </div>

          

          <div class="detail-item">
            <span class="item-label">定休日</span>
            <div class="item-content">
              <div class="multi-line">元旦 店休
<span class="notice">※一部店舗では臨時休業・営業時間を状況に応じて変更させて頂く場合がございます。</span>
              </div>
            </div>
          </div>

        </div>

      </main>
    </div>

    <?php include('fixed-footer.php'); ?>
    
  </body>
</html>