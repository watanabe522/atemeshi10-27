<?php
require_once 'DAO.php';

class Member {
    public int    $member_id;           //会員ID
    public string $member_password;     //パスワード
    public string $member_email;        //メールアドレス
    public string $member_name;         //会員名
    public int    $is_member;           //管理者識別
    
}
class MemberDAO {
    //DBからメールアドレスとパスワードが一致する会員データを取得
    public function get_member(string $member_email ,string $member_password){
        $dbh = DAO::get_db_connect();

        $sql = "SELECT * FROM Member 
                WHERE member_email = :member_email";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':member_email',$member_email,PDO::PARAM_STR);
        
        $stmt->execute();

        $member = $stmt->fetchObject('Member');

        // 会員データが取得できたとき
        if($member !== false){
            if(password_verify($member_password,$member->member_password)){
                return $member;
            }
        }
        return false;
    }

       // 指定したメールアドレスの会員データが存在すれば true を返す
    public function email_exists(string $member_email){

        $dbh = DAO::get_db_connect();
        $sql = "SELECT member_email FROM Member
                    WHERE member_email = :member_email";
        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':member_email',$member_email,PDO::PARAM_STR);

        $stmt->execute();

        if($stmt->fetch() !== false){
            return true; //member_emailが存在する
        }else{
            return false; //member_emailが存在しない
        }
    }

    // ▼▼▼ 修正 ▼▼▼
    // 会員データを登録する (is_member も登録できるように変更)
    public function insert(Member $member){
        $dbh = DAO::get_db_connect();
        $member_email = $member->member_email;

        if(!($this->email_exists($member_email))){

            // is_member も変数から受け取るように修正
            $sql = "INSERT INTO Member(member_email,member_name,member_password,is_member)
                    VALUES(:member_email,:member_name,:member_password,:is_member)";
            $stmt = $dbh->prepare($sql);

            // パスワードをハッシュ化
            $member_password = password_hash($member->member_password, PASSWORD_DEFAULT);

            $stmt->bindValue(':member_email',$member->member_email,PDO::PARAM_STR);
            $stmt->bindValue(':member_name',$member->member_name,PDO::PARAM_STR);
            $stmt->bindValue(':member_password',$member_password,PDO::PARAM_STR);
            // is_member を $member オブジェクトから取得
            $stmt->bindValue(':is_member',$member->is_member,PDO::PARAM_INT);

            $stmt->execute();

            return true;
            
        }else{

            return false; 

        }

    }

    // ▼▼▼ 修正 ▼▼▼
    // 会員データを更新する (管理者による is_member の更新にも対応)
    public function update(Member $member) {
        $dbh = DAO::get_db_connect();

        // SQL文を動的に構築
        $sql_parts = [
            "member_name = :member_name",
            "member_email = :member_email"
        ];
        
        // パスワードが入力されている場合のみ、パスワードの更新SQLを追加
        if (!empty($member->member_password)) {
            $sql_parts[] = "member_password = :member_password";
        }

        // is_member がセットされている場合（管理者による更新など）
        // (isset を使うため、呼び出し側は 0 または 1 を明示的にセットすること)
        if (isset($member->is_member)) {
            $sql_parts[] = "is_member = :is_member";
        }
        
        $sql = "UPDATE Member SET " . implode(", ", $sql_parts) . " WHERE member_id = :member_id";
        
        $stmt = $dbh->prepare($sql);

        // 値をバインド
        $stmt->bindValue(':member_name', $member->member_name, PDO::PARAM_STR);
        $stmt->bindValue(':member_email', $member->member_email, PDO::PARAM_STR);
        $stmt->bindValue(':member_id', $member->member_id, PDO::PARAM_INT);
        
        // パスワードが入力されている場合のみバインド
        if (!empty($member->member_password)) {
            $member_password_hashed = password_hash($member->member_password, PASSWORD_DEFAULT);
            $stmt->bindValue(':member_password', $member_password_hashed, PDO::PARAM_STR);
        }

        // is_member がセットされている場合のみバインド
        if (isset($member->is_member)) {
            $stmt->bindValue(':is_member', $member->is_member, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }
    // ▲▲▲ 修正ここまで ▲▲▲


    //パスワード確認
    public function get_pass(string $member_email ,string $member_password){
        $dbh = DAO::get_db_connect();

        $sql = "SELECT * FROM member 
                WHERE member_email = :member_email and member_password = :member_password";

        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':member_email',$member_email,PDO::PARAM_STR);
        $stmt->bindValue(':member_password',$member_password,PDO::PARAM_STR);
        
        $stmt->execute();

        $member = $stmt->fetchObject('Member');

        // 会員データが取得できたとき
        if($member !== false){
            return true;
        }
            return false;
    }


    // ▼▼▼ ここから admin_dashboard.php 用の関数 ▼▼▼

    /**
     * すべての会員データを取得する (管理画面用)
     * @return Member[]
     */
    public function get_all_members() {
        $dbh = DAO::get_db_connect();
        $sql = "SELECT member_id, member_email, member_name, is_member 
                FROM Member 
                ORDER BY member_id ASC";
        
        $stmt = $dbh->query($sql);
        
        // Memberオブジェクトの配列として取得
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Member');
    }

    /**
     * 会員データをIDで削除する (管理画面用)
     * @param int $member_id
     * @return bool
     */
    public function delete_member_by_id(int $member_id) {
        $dbh = DAO::get_db_connect();
        $sql = "DELETE FROM Member WHERE member_id = :member_id";
        
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':member_id', $member_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * フィルタ条件に基づいて会員データを取得する (管理画面用)
     * @param string $filter_status ('all', 'admin', 'user')
     * @param string $filter_keyword (名前 or email)
     * @return Member[]
     */
    public function get_members_by_filter(string $filter_status, string $filter_keyword) {
        $dbh = DAO::get_db_connect();
        
        $sql_parts = [];
        $params = [];

        // 基本のSQL
        $sql = "SELECT member_id, member_email, member_name, is_member FROM Member WHERE 1=1";

        // 1. ステータス (管理者/一般) のフィルタ
        if ($filter_status === 'admin') {
            $sql .= " AND is_member = 1";
        } elseif ($filter_status === 'user') {
            $sql .= " AND is_member = 0";
        }
        // 'all' の場合は条件を追加しない

        // 2. キーワード (名前 or メール) のフィルタ
        if (!empty($filter_keyword)) {
            $sql .= " AND (member_name LIKE :keyword OR member_email LIKE :keyword)";
            $params[':keyword'] = '%' . $filter_keyword . '%';
        }

        $sql .= " ORDER BY member_id ASC";
        
        $stmt = $dbh->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Member');
    }
}