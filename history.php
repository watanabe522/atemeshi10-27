<?php
  require_once('helpers/MemberDAO.php');
  require_once('helpers/HistoryDAO.php');

  // セッション開始
  session_start();

  // セッションに会員情報がなければログインページへリダイレクト
  if (empty($_SESSION['member'])) {
      header('Location: login-register.php');
      exit;
  }
  $member = $_SESSION['member'];
  $HistoryDAO = new HistoryDAO();
 
  // DAOからhotpepper_code, time, is_favoriteを取得
  $raw_history_data = $HistoryDAO->get_history_details($member->member_id);

  //hotpepper
  $api_key = '8b7a467ccf017947'; // 💡 取得したAPIキーに置き換えてください
  $base_url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/';

  // ----------------------------------------------------
  // 📌 データ取得処理 (既存の店名検索)
  // ----------------------------------------------------
  $all_shops = []; 
  $error_message = null; 
  $search_name = '';
  $results_available = 0;

  // GETリクエストで店名が送信されたかチェック
  if (isset($_GET['shop_name']) && !empty($_GET['shop_name'])) {
      // ... (既存の店名検索処理は変更なし) ...
      $search_name = trim($_GET['shop_name']);
      
    // APIパラメータの設定
    $params = [
        'key' => $api_key,
        'format' => 'json',
        'name' => $search_name, // ★ 店名検索パラメータ
        'count' => 10, // 最大10件まで取得
        'start' => 1,
    ];

    // APIリクエスト
    $query_string = http_build_query($params);
    $request_url = $base_url . '?' . $query_string;
    $response = @file_get_contents($request_url);

    if ($response === FALSE) {
        $error_message = "APIリクエストエラーが発生しました。";
    } else {
      $data = json_decode($response, true);
        
      if (isset($data['results']['error'][0]['message'])) {
          $error_message = "APIエラー: " . $data['results']['error'][0]['message'];
      } else {
        $results = $data['results'];
        $all_shops = $results['shop'] ?? [];
        $results_available = (int)($results['results_available'] ?? 0);
        
        if (count($all_shops) === 0 && $results_available === 0) {
            $error_message = "店名「{$search_name}」に一致する店舗が見つかりませんでした。";
        }
      }
    }
  }

  // ----------------------------------------------------
  // 📌 履歴の店舗情報取得処理 (hotpepper_codeによる検索)
  // ----------------------------------------------------
  $combined_history = [];
  
  if (!empty($raw_history_data)) {
      // 1. hotpepper_codeのリストを作成（重複排除はAPI側で行うため不要だが、念のため）
      $hotpepper_codes_list = array_column($raw_history_data, 'hotpepper_code');
      
      // hotpepper_codeが空でない場合のみAPIを叩く
      if (!empty($hotpepper_codes_list)) {

          // APIパラメータの設定（ID検索用）
          $params = [
              'key' => $api_key,
              'format' => 'json',
              'id' => implode(',', $hotpepper_codes_list), // 複数のIDをカンマ区切りで指定
          ];
    
          // APIリクエスト
          $query_string = http_build_query($params);
          $request_url = $base_url . '?' . $query_string;
          $response = @file_get_contents($request_url);
    
          if ($response === FALSE) {
              // エラーログ出力 (画面表示はしない)
              error_log("History APIリクエストエラー"); 
          } else {
              $data = json_decode($response, true);
              
              if (isset($data['results']['error'][0]['message'])) {
                  error_log("History APIエラー: " . $data['results']['error'][0]['message']);
              } else {
                  $history_shops_info = $data['results']['shop'] ?? [];
    
                  // 2. hotpepper_codeをキーにした連想配列に変換（検索効率化のため）
                  $shops_map = [];
                  foreach ($history_shops_info as $shop) {
                      $shops_map[$shop['id']] = $shop;
                  }
    
                  // 3. 履歴データとAPIデータを結合
                  foreach ($raw_history_data as $history_item) {
                      $code = $history_item['hotpepper_code'];
                      
                      if (isset($shops_map[$code])) {
                          $shop_info = $shops_map[$code];
                          $combined_history[] = [
                              'hotpepper_code' => $code,
                              'visit_time' => $history_item['time'], // DAOから取得した訪問日時
                              'is_favorite' => $history_item['is_favorite'], // DAOから取得したお気に入りフラグ
                              'shop_name' => $shop_info['name'] ?? '店舗名情報なし',
                              'access' => $shop_info['access'] ?? '最寄り駅情報なし',
                              // PC用のLサイズ画像を優先、なければモバイル用のLサイズ
                              'image_url' => $shop_info['photo']['pc']['l'] ?? ($shop_info['photo']['mobile']['l'] ?? 'images/no_image.jpg'), 
                          ];
                      }
                      // APIで情報が取得できなかった店舗はスキップ、またはデフォルト情報で表示
                  }
              }
          }
      }
  }
?>


<!DOCTYPE html>
  <head>
    <meta charset="UTF-8">
    <title>マイ履歴</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  <body>
    <div class="container">
      <header class="page-header">
        <h1>マイ履歴</h1>
      </header>

      <main class="history-page">
        <h2 class="section-title">過去の履歴</h2>

  <div class="history-list">
          <?php if (empty($combined_history)): ?>
            <p>過去の履歴はありません。</p>
          <?php else: ?>
            <?php foreach ($combined_history as $history_item): 
                // is_favorite が '1' の場合は true、それ以外は false
                $is_favorited_str = $history_item['is_favorite'] === '1' ? 'true' : 'false';
                $star_icon = $is_favorited_str === 'true' ? 'fa-solid fa-star' : 'fa-regular fa-star';
            ?>
              <div class="history-card" data-store-id="<?= htmlspecialchars($history_item['hotpepper_code']) ?>">
                <div class="card-main-content">
                  <img src="<?= htmlspecialchars($history_item['image_url']) ?>" alt="店舗画像" class="card-image">
                  <div class="card-details">
                    <div class="card-header">
                      <span class="status-tag visited">来店済み</span>
                      <span class="reservation-code">お気に入り</span> </div>
                    <h3 class="card-title"><?= htmlspecialchars($history_item['shop_name']) ?></h3>
                    <p class="card-access"><?= htmlspecialchars($history_item['access']) ?></p>
                    <p class="card-datetime"><?= htmlspecialchars($history_item['visit_time']) ?></p>
                  </div>
                  <i class="fa-solid fa-chevron-right card-arrow"></i>
                </div>
                <div class="favorite-star" data-favorited="<?= $is_favorited_str ?>">
                  <i class="<?= $star_icon ?>"></i>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>
    </div>

    <?php include('fixed-footer.php'); ?>

<script>
      document.addEventListener('DOMContentLoaded', () => {
        const favoriteStars = document.querySelectorAll('.favorite-star');

        favoriteStars.forEach(star => {
          star.addEventListener('click', (event) => {
            // クリックイベントが親要素に伝播するのを防ぐ
            event.stopPropagation(); 
            
            const card = star.closest('.history-card');
            const isFavorited = star.dataset.favorited === 'true';
            // hotpepper_code (店舗ID) を取得
            const storeId = card.dataset.storeId; 

            // 新しい状態（現在の逆）
            const newFavoriteStatus = !isFavorited;
            const newFavoriteStatusStr = newFavoriteStatus ? 'true' : 'false';

            // 1. UIを先に更新（即時フィードバック）
            // UI更新時に、通信中であることを示すローディング表示などを入れても良い
            if (newFavoriteStatus) {
              star.innerHTML = '<i class="fa-solid fa-star"></i>';
            } else {
              star.innerHTML = '<i class="fa-regular fa-star"></i>';
            }
            star.dataset.favorited = newFavoriteStatusStr;
            
            // 2. データベース更新リクエスト（AJAX）
            const formData = new URLSearchParams();
            formData.append('code', storeId);
            formData.append('favorite', newFavoriteStatusStr); // 'true' または 'false'を送信

            fetch('update_favorite.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // サーバー側でエラーレスポンス (4xx/5xx) が返された場合
                    throw new Error('Server response not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log(`店舗ID: ${storeId} のお気に入りをDBに反映しました。新しい状態: ${newFavoriteStatusStr}`);
                } else {
                    // DB更新に失敗した場合（data.success = false）
                    console.error('DB更新失敗:', data.message);
                    alert('お気に入りの状態を更新できませんでした。');
                    // UIを元に戻す（DBの状態と一致させる）
                    star.dataset.favorited = isFavorited ? 'true' : 'false';
                    star.innerHTML = isFavorited ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                }
            })
            .catch(error => {
                // 通信自体が失敗した場合
                console.error('通信エラー:', error);
                alert('通信エラーが発生しました。お気に入りの状態を更新できませんでした。');
                // UIを元に戻す
                star.dataset.favorited = isFavorited ? 'true' : 'false';
                star.innerHTML = isFavorited ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
            });
          });
        });
      });
    </script>
    
  </body>
</html>