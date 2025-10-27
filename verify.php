<?php
require_once('helpers/MemberDAO.php');

$message = '';
$is_success = false;

// URLの?以降にtokenパラメータがあるかチェック
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    try {
        $memberDAO = new MemberDAO();
        
        // 1. トークンに一致する未認証のユーザーを探す
        $member = $memberDAO->get_member_by_token($token);

        if ($member) {
            // 2. ユーザーが見つかったら、is_verifiedを1に更新し、トークンを無効化する
            if ($memberDAO->verify_member($member->member_id)) {
                $message = 'メールアドレスの認証が完了しました！<br>ログインしてサービスをお楽しみください。';
                $is_success = true;
            } else {
                $message = 'データベースの更新処理中にエラーが発生しました。お手数ですが、管理者にお問い合わせください。';
            }
        } else {
            // トークンが無効（既に認証済み、または存在しない）
            $message = 'この認証リンクは無効、または既に使用されています。';
        }

    } catch (Exception $e) {
        $message = 'データベース接続中にエラーが発生しました。';
        // error_log($e->getMessage()); // エラーログを記録
    }

} else {
    // URLにトークンが含まれていない場合
    $message = '不正なアクセスです。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メールアドレス認証</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* このページ専用のスタイル */
        .verification-container { 
            text-align: center; 
            padding: 50px 20px; 
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .verification-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        .icon-success { color: #28a745; }
        .icon-error { color: #dc3545; }
        .message { 
            margin-bottom: 30px; 
            font-size: 1.2em; 
            line-height: 1.6;
        }
        .login-link { 
            display: inline-block; 
            padding: 12px 30px; 
            background-color: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .login-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="verification-container">
            <?php if ($is_success): ?>
                <div class="verification-icon icon-success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h1>認証完了</h1>
            <?php else: ?>
                <div class="verification-icon icon-error">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <h1>認証エラー</h1>
            <?php endif; ?>
            
            <p class="message"><?= $message ?></p>
            
            <?php if ($is_success): ?>
                <a href="login-register.php" class="login-link">ログインページへ進む</a>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>