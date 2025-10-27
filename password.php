<?php
// PHP処理の開始（セッション開始など）
// session_start();
// $error_message = ''; // エラーメッセージ用変数

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     // ログイン処理をここに追加
// }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
    </head>
<body>
    <div class="container">
        <div class="login-page">
            <div class="login-box">
                <h1>ログイン</h1>
                
                <form action="login.php" method="post" class="login-form">
                    
                    <label for="email">メールアドレス</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="password">パスワード</label>
                    <input type="password" id="password" name="password" required>
                    
                    <div class="form-actions">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">ログイン状態を保存</label>
                        </div>
                        <button type="submit" class="login-button">ログイン</button>
                    </div>
                </form>
                
                <div class="helper-links">
                    <a href="forgot_password.php" class="forgot-password-link">パスワードを忘れた方</a>
                    <a href="unlock_account.php">ロック解除</a>
                    <a href="resend_email.php">登録確認メール再送</a>
                    <a href="sso.php">シングルサインオン (SSO)</a>
                </div>
            </div>
        </div>
        </div>
</body>
</html>