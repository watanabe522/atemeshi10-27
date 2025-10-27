<?php
// PHPMailerライブラリの読み込み (手動設置の場合)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

/**
 * 認証メールを送信する関数
 *
 * @param string $email_to 送信先のメールアドレス
 * @param string $token    認証用トークン
 * @return bool             送信が成功した場合はtrue、失敗した場合はfalse
 */
function send_verification_email($email_to, $token) {
    $mail = new PHPMailer(true);

    try {
        //==============【ここからGmailの設定】==============
        
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // ★★★ 自分のGmailアドレスに変更 ★★★
        $mail->Username   = 'atemeshi.com@gmail.com';
        // ★★★ 取得した16桁のアプリパスワードに変更 ★★★
        $mail->Password   = 'excheurwepmrrtgt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //==============【ここまでGmailの設定】==============


        //==============【ここからメール内容の設定】==============

        // 送信元情報（Fromヘッダー）
        // ★★★ 自分のGmailアドレスと、表示させたい送信者名に変更 ★★★
        $mail->setFrom('atemeshi.com@gmail.com', 'アテメシ運営事務局');
        
        // 宛先（Toヘッダー）
        $mail->addAddress($email_to);

        // メールの形式と文字コード
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        // 件名
        $mail->Subject = '【アテメシ】ご登録ありがとうございます（メールアドレス認証）';

        // 認証用URLを生成
        // ★★★ あなたのサイトのURLに合わせて変更してください ★★★
        // (例: ローカル環境 http://localhost/10.32.97.1/verify.php)
        // (例: 本番環境   https://www.your-domain.com/verify.php)
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['PHP_SELF']);
        $verification_url = "{$protocol}://{$host}{$path}/verify.php?token=" . urlencode($token);

        // メール本文 (HTML形式)
        $mail->Body    = "
            <div style='font-family: sans-serif;'>
                <h2 style='color: #333;'>アテメシにご登録いただき、ありがとうございます！</h2>
                <p>まだ登録は完了しておりません。</p>
                <p>お手数ですが、以下のリンクをクリックしてメールアドレスの認証を完了してください。</p>
                <p style='margin: 25px 0;'>
                    <a href='{$verification_url}' style='background-color: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>認証を完了する</a>
                </p>
                <p>もし上記のボタンをクリックできない場合は、以下のURLをコピーしてブラウザのアドレスバーに貼り付けてください。</p>
                <p><a href='{$verification_url}'>{$verification_url}</a></p>
                <hr style='border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 0.9em; color: #777;'>※このメールに心当たりがない場合は、お手数ですがこのまま削除してください。</p>
            </div>
        ";

        // HTMLメールが非対応のメーラー向けの、プレーンテキスト版の本文
        $mail->AltBody = "アテメシへのご登録ありがとうございます。\n以下のリンクをブラウザで開いて、認証を完了してください。\n{$verification_url}";

        //==============【ここまでメール内容の設定】==============

        // メールを送信
        $mail->send();
        return true;

    } catch (Exception $e) {
        // エラーが発生した場合はfalseを返す
        // 本番環境では、エラーログを記録するとデバッグに役立ちます
        // error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}