<?php
require_once 'DAO.php';
require_once('MemberDAO.php');

class History {
    public int      $history_id ;   //履歴ＩＤ
    public int      $member_id ;    //会員ＩＤ
    public int      $restaurant_id; //店舗ID
    public DateTime $time;          //日付
    public int      $evaluation;    //評価
    public string   $is_favorite;   //お気に入り
    public string   $review;        //レビュー
}

class HistoryDAO {

    // hotpepper_code、訪問日時、お気に入りフラグなどを一度に取得する
    public function get_history_details($member_id) {

        $dbh = DAO::get_db_connect();

        
        $sql = "SELECT R.hotpepper_code, H.time, H.is_favorite
                FROM Restaurant AS R
                INNER JOIN History AS H ON R.restaurant_id = H.restaurant_id
                WHERE H.member_id = :member_id
                ORDER BY H.time DESC";  // 訪問日時の新しい順でソート

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':member_id', $member_id, PDO::PARAM_INT);
        
        $stmt->execute();

        // 履歴データ（hotpepper_code, time, is_favoriteなど）を配列として返す
        $data = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
        return $data;
    }

    public function update_favorite_status($member_id, $hotpepper_code, $is_favorite) {
        $dbh = DAO::get_db_connect();
        
        try {
            
            // 1. hotpepper_codeからrestaurant_idを取得
            $sql_r = "SELECT restaurant_id FROM Restaurant WHERE hotpepper_code = :hotpepper_code";
            $stmt_r = $dbh->prepare($sql_r);
            $stmt_r->bindValue(':hotpepper_code', $hotpepper_code, PDO::PARAM_STR);
            $stmt_r->execute();
            $restaurant_id = $stmt_r->fetchColumn();

            if (!$restaurant_id) {
                return false; // 店舗IDが見つからない
            }

            // 2. Historyテーブルのis_favoriteを更新（会員IDと店舗IDで特定）
            // 注意: 該当する全ての履歴のフラグが更新されます
            $sql_h = "UPDATE History 
                      SET is_favorite = :is_favorite 
                      WHERE member_id = :member_id 
                      AND restaurant_id = :restaurant_id";
            
            $stmt_h = $dbh->prepare($sql_h);
            
            $stmt_h->bindValue(':is_favorite', $is_favorite, PDO::PARAM_STR); // '0' or '1'
            $stmt_h->bindValue(':member_id', $member_id, PDO::PARAM_INT);
            $stmt_h->bindValue(':restaurant_id', $restaurant_id, PDO::PARAM_INT);

            return $stmt_h->execute();
        } catch (PDOException $e) {
            // エラーハンドリング
            error_log("Favorite update failed: " . $e->getMessage());
            return false;
        }
    }

}