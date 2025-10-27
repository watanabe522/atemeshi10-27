<?php
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
  <title>お気に入り</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
  <div class="container">
    <header class="page-header">
      <h1>お気に入り</h1>
    </header>

    <main class="favorites-page">
      <div class="search-bar-container">
        <div class="search-bar">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="お気に入りからお店を探す">
        </div>
        <button class="filter-button">
          <i class="fa-solid fa-sliders"></i>
          <span>絞込み</span>
        </button>
      </div>

      <p class="item-count">null件</p>

      <div class="favorites-list">
        <div class="favorite-card" data-store-id="2">
          <img src="images/history02.jpg" alt="店舗画像" class="fav-card-image">
          <div class="fav-card-details">
            <p class="fav-card-category">居酒屋 / 千葉駅</p>
            <h3 class="fav-card-title">七輪焼肉 安安 千葉店</h3>
            <div class="fav-card-info">
              <span class="info-item"><i class="fa-solid fa-money-bill-wave"></i> 2001～3000円</span>
              <span class="info-item"><i class="fa-solid fa-train"></i> JR千葉駅東口徒歩３分 / モノレール栄町か…</span>
            </div>
          </div>
          <div class="favorite-star" data-favorited="true">
            <i class="fa-solid fa-star"></i>
          </div>
        </div>

        <div class="favorite-card" data-store-id="4">
          <img src="images/fav01.jpg" alt="店舗画像" class="fav-card-image">
          <div class="fav-card-details">
            <p class="fav-card-category">居酒屋 / 渋谷センター街</p>
            <h3 class="fav-card-title">【全席喫煙可 飲み放題】炭火串焼きと海鮮 デカ盛り 博多酒場すみ吉屋 渋谷</h3>
            <div class="fav-card-info">
              <span class="info-item"><i class="fa-solid fa-money-bill-wave"></i> 2001～3000円</span>
              <span class="info-item"><i class="fa-solid fa-train"></i> 渋谷駅ハチ公改札口から徒歩１分渋谷駅から…</span>
            </div>
          </div>
          <div class="favorite-star" data-favorited="true">
            <i class="fa-solid fa-star"></i>
          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include('fixed-footer.php'); ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const favoriteStars = document.querySelectorAll('.favorite-star');

      favoriteStars.forEach(star => {
        star.addEventListener('click', (event) => {
          event.stopPropagation();
          
          const isFavorited = star.dataset.favorited === 'true';
          const card = star.closest('.favorite-card');
          const storeId = card.dataset.storeId;

          // このページでは、お気に入り解除のみを想定
          if (isFavorited) {
            // 確認ダイアログを表示
            if (confirm('このお店をお気に入りから削除しますか？')) {
              // 視覚的にカードを削除
              card.style.display = 'none';
              console.log(`店舗ID: ${storeId} のお気に入りを解除しました`);
              // ここでサーバーに解除リクエストを送るAPIなどを呼び出す
            }
          }
        });
      });
    });
  </script>
</body>
</html>