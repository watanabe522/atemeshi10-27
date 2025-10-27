<?php
require_once('helpers/MemberDAO.php');

session_start();

// POSTリクエスト以外はマイページへリダイレクト
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mypage.php');
    exit;
}

// ログインユーザーのIDを取得
if (empty($_SESSION['member'])) {
    header('Location: login-register.php');
    exit;
}
$member_id = $_SESSION['member']->member_id;

// POSTデータから新しい情報を取得
$new_name = filter_input(INPUT_POST, 'member_name');
$new_email = filter_input(INPUT_POST, 'member_email');
$new_password = $_POST['member_password'];
$new_password_confirm = $_POST['member_password_confirm'];

$errs = [];

// --- サーバーサイド バリデーションの強化 ---

// 1. 氏名の未入力チェック
if (empty($new_name)) {
    $errs[] = 'お名前を入力してください。';
}

// 2. メールアドレスの形式チェックと必須チェック
if (empty($new_email)) {
    $errs[] = 'メールアドレスを入力してください。';
} elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $errs[] = 'メールアドレスの形式が正しくありません。';
}

// 3. パスワードの形式チェック (入力がある場合のみ)
// パスワードが入力されているか、確認用パスワードが入力されているかチェック
if (!empty($new_password) || !empty($new_password_confirm)) {
    
    // パスワードと確認用パスワードの必須チェックと一致チェック
    if (empty($new_password) || empty($new_password_confirm)) {
        $errs[] = '新しいパスワードと確認用パスワードの両方を入力してください。';
    } else if ($new_password !== $new_password_confirm) {
        $errs[] = 'パスワードと確認用パスワードが一致しません。';
    }

    // login-register.phpと同じバリデーションルール: 半角英数字のみ・8文字以上
    if (!empty($new_password) && !preg_match('/^[a-zA-Z0-9]{8,}$/', $new_password)) {
        $errs[] = 'パスワードは8文字以上の半角英数字で入力してください。';
    }
}

// 4. エラーがある場合はセッションにエラーメッセージを保存してリダイレクト
if (!empty($errs)) {
    $_SESSION['error'] = implode('<br>', $errs); // 複数のエラーを改行で結合
    header('Location: mypage.php');
    exit;
}

// エラーがなければDAO処理へ進む
$memberDAO = new MemberDAO();

// メールアドレスの重複チェック（メールアドレスが変更された場合のみ）
if ($new_email !== $_SESSION['member']->member_email) {
    if ($memberDAO->email_exists($new_email)) {
        $_SESSION['error'] = 'そのメールアドレスは既に使用されています。';
        header('Location: mypage.php');
        exit;
    }
}

// 更新用 Member オブジェクトを作成し、値をセット
$updated_member = new Member();
$updated_member->member_id = $member_id;
$updated_member->member_name = $new_name;
$updated_member->member_email = $new_email;
$updated_member->member_password = $new_password;

// データベースを更新
if ($memberDAO->update($updated_member)) {
    // 成功した場合
    
    // セッション内の会員情報を更新
    $_SESSION['member']->member_name = $new_name;
    $_SESSION['member']->member_email = $new_email;
    
    // ※アレルギー情報はMemberクラスにないので更新していません
    
    $_SESSION['success'] = '会員情報を更新しました。';
    
} else {
    // 失敗した場合
    $_SESSION['error'] = '会員情報の更新に失敗しました。';
}

header('Location: mypage.php');
exit;
?>