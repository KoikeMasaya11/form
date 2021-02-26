<?php
//*****************************************************
// 会員登録フォーム＆お問い合わせフォーム
//*****************************************************

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

define( "FILE_DIR", "images/");
session_start();

// 変数の初期化
$page_flag = 0;
$clean = array();
$error = array();

// データベースに接続
$mysqli = new mysqli( 'localhost', 'root', '', 'form');
// 接続エラーの確認
if( $mysqli->connect_errno ) {
	$error[] = 'データベースの書き込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} 	

// サニタイズ
if( !empty($_POST) ) {
	foreach( $_POST as $key => $value ) {
		$clean[$key] = htmlspecialchars($value, ENT_QUOTES);
	} 
}

if( !empty($clean['btn_confirm']) ) {
	$error = validation($clean);
	// ファイルのアップロード
	if( !empty($_FILES['attachment_file']['tmp_name']) ) {
		$upload_res = move_uploaded_file( $_FILES['attachment_file']['tmp_name'], FILE_DIR.$_FILES['attachment_file']['name']);
		if( $upload_res !== true ) {
			$error[] = 'ファイルのアップロードに失敗しました。';
		} else {
			$clean['attachment_file'] = $_FILES['attachment_file']['name'];
		}
	}
	
	if( empty($error) ) {
		$page_flag = 1;
		//セッションの書き込み
		$_SESSION['page'] = true;
	}
}

if( !empty($clean['btn_submit']) ) {	
	if(!empty($_SESSION['page']) && $_SESSION['page'] === true){
		//セッション$_SESSION['page']の削除
		unset($_SESSION['page']);
		$page_flag = 2;
		//ハッシュ化
		$hash_pass = password_hash($clean['password'],PASSWORD_DEFAULT);
		// 文字コード設定
		$mysqli->set_charset('utf8');
		// 書き込み日時を取得
		$now_date = date("Y-m-d H:i:s");
		// データを登録するSQL作成
		$sql = "INSERT INTO login (your_name,email,password,gender,age,contact) VALUES ( '$clean[your_name]','$clean[email]','$hash_pass','$clean[gender]','$clean[age]','$clean[contact]')";
		// データを登録
		$res = $mysqli->query($sql);
		// データベースの接続を閉じる
		$mysqli->close();
	
		date_default_timezone_set('Asia/Tokyo');
		mb_language("ja");
		mb_internal_encoding("UTF-8");
		
		
		$mail = new PHPMailer(true);
		
		try {
		//Gmail 認証情報
		$host = 'smtp.gmail.com';
		$username = 'masayasts1127@gmail.com'; // example@gmail.com
		$password = 'masaya.k1127';
		
		//差出人
		$from = 'masayasts1127@gmail.com';
		$fromname = 'フォーム制作運営';
		
		//管理者
		$Administrator = '12181131@g.matsuyama-u.ac.jp';
		
		//宛先
		$to = $clean["email"];
		$toname = $clean["your_name"];
		

		//件名・本文
		$subject = 'お問い合わせありがとうございます。';
		$body = "この度は、お問い合わせ頂き誠にありがとうございます。下記の内容でお問い合わせを受け付けました。\n\n";
		$body .= "お問い合わせ日時：" . date("Y-m-d H:i") . "\n";
		$body .= "氏名：" . $clean['your_name'] . "\n";
		$body .= "メールアドレス：" . $clean['email'] . "\n";
		$body .= "パスワード；" . $clean['password'] . "\n";

		if( $clean['gender'] === "male" ) {
			$body .= "性別：男性\n";
		} else {
			$body .= "性別：女性\n";
		}

		if( $clean['age'] === "1" ){
			$body .= "年齢：〜19歳\n";
		} elseif ( $clean['age'] === "2" ){
			$body .= "年齢：20歳〜29歳\n";
		} elseif ( $clean['age'] === "3" ){
			$body .= "年齢：30歳〜39歳\n";
		} elseif ( $clean['age'] === "4" ){
			$body .= "年齢：40歳〜49歳\n";
		} elseif ( $clean['age'] === "5" ){
			$body .= "年齢：50歳〜59歳\n";
		} elseif ( $clean['age'] === "6" ){
			$body .= "年齢：60歳〜\n";
		}

		$body .= "お問い合わせ内容：" . nl2br($clean['contact']) . "\n\n";

		//メール設定
		$mail->SMTPDebug = 2; //デバッグ用
		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = $host;
		$mail->Username = $username;
		$mail->Password = $password;
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		$mail->CharSet = "utf-8";
		$mail->Encoding = "base64";
		$mail->setFrom($from, $fromname);
		$mail->addAddress($to, $toname);
		$mail->Subject = $subject;
		$mail->Body    = $body;
		$mail->addBCC($Administrator);

		//メール送信
		$mail->send();
		echo '成功';

		} catch (Exception $e) {
		echo '失敗: ', $mail->ErrorInfo;
		}

		$body1 = "--__BOUNDARY__\n";
		$body1 .= "Content-Type: text/plain; charset=\"ISO-2022-JP\"\n";
		$body1 .= $body . "\n";
		$body1 .="--__BOUDARY__\n";

	//ファイルを添付
	if(!empty($clean['attachment_file'])){
		$body1 .="Content-Type: application/octet-stream; name=\"
		{$clean['attachment_file']}\"\n";
		$body1 .="Content-Disposition: attachment; filename=\"
		{$clean['attachment_file']}\"\n";
		$body1 .="Content-Transfer-Ecoding:base64\n";
		$body1 .="\n";
		$body1 .=
		chunk_split(base64_encode(file_get_contents(FILE_DIR.$clean['attachment_file'])));
		$body1 .="--__BOUNDARY__\n";
	}

	}else{
	$page_flag = 0;
	}
}

function validation($data) {
	// データベースに接続
	$mysqli = new mysqli( 'localhost', 'root', '', 'form');
	$error = array();

	// 氏名のバリデーション
	if( empty($data['your_name']) ) {
		$error[] = "「氏名」は必ず入力してください。";
	} elseif( 20 < mb_strlen($data['your_name']) ) {
		$error[] = "「氏名」は20文字以内で入力してください。";
	}

	// メールアドレスのバリデーション
	if( empty($data['email']) ) {
		$error[] = "「メールアドレス」は必ず入力してください。";
	} elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email']) ) {
		$error[] = "「メールアドレス」は正しい形式で入力してください。";
	} 
	
	$sql = "SELECT * from login where email = '$data[email]'";
	$res = $mysqli->query($sql);
	$email_count = $res->fetch_assoc();
	if (isset($email_count)) {
		$error[] = "すでに同じメールアドレスが存在しています。";
	}
	$mysqli->close();
	

	//パスワードのバリデーション
	if(empty($data['password']) ){
		$error[] = "「パスワード」は必ず入力してください。";
	}

	// 性別のバリデーション
	if( empty($data['gender']) ) {
		$error[] = "「性別」は必ず入力してください。";

	} elseif( $data['gender'] !== 'male' && $data['gender'] !== 'female' ) {
		$error[] = "「性別」は必ず入力してください。";
	}

	// 年齢のバリデーション
	if( empty($data['age']) ) {
		$error[] = "「年齢」は必ず入力してください。";

	} elseif( (int)$data['age'] < 1 || 6 < (int)$data['age'] ) {
		$error[] = "「年齢」は必ず入力してください。";
	}

	// お問い合わせ内容のバリデーション
	if( empty($data['contact']) ) {
		$error[] = "「お問い合わせ内容」は必ず入力してください。";
	}

	// プライバシーポリシー同意のバリデーション
	if( empty($data['agreement']) ) {
		$error[] = "プライバシーポリシーをご確認ください。";

	} elseif( (int)$data['agreement'] !== 1 ) {
		$error[] = "プライバシーポリシーをご確認ください。";
	}

	return $error;
}
?>

<!DOCTYPE>
<html lang="ja">
<head>
<title>会員登録フォーム＆お問い合わせフォーム</title>
<style rel="stylesheet" type="text/css">
body {
	padding: 20px;
	text-align: center;
}

h1 {
	margin-bottom: 20px;
	padding: 20px 0;
	color: #209eff;
	font-size: 122%;
	border-top: 1px solid #999;
	border-bottom: 1px solid #999;
}

input[type=text] {
	padding: 5px 10px;
	font-size: 86%;
	border: none;
	border-radius: 3px;
	background: #ddf0ff;
}

input[name=btn_confirm],
input[name=btn_submit],
input[name=btn_back] {
	margin-top: 10px;
	padding: 5px 20px;
	font-size: 100%;
	color: #fff;
	cursor: pointer;
	border: none;
	border-radius: 3px;
	box-shadow: 0 3px 0 #2887d1;
	background: #4eaaf1;
}

input[name=btn_back] {
	margin-right: 20px;
	box-shadow: 0 3px 0 #777;
	background: #999;
}

.element_wrap {
	margin-bottom: 10px;
	padding: 10px 0;
	border-bottom: 1px solid #ccc;
	text-align: left;
}

label {
	display: inline-block;
	margin-bottom: 10px;
	font-weight: bold;
	width: 150px;
	vertical-align: top;
}

.element_wrap p {
	display: inline-block;
	margin:  0;
	text-align: left;
}

label[for=gender_male],
label[for=gender_female],
label[for=agreement] {
	margin-right: 10px;
	width: auto;
	font-weight: normal;
}

textarea[name=contact] {
	padding: 5px 10px;
	width: 60%;
	height: 100px;
	font-size: 86%;
	border: none;
	border-radius: 3px;
	background: #ddf0ff;
}

.error_list {
	padding: 10px 30px;
	color: #ff2e5a;
	font-size: 86%;
	text-align: left;
	border: 1px solid #ff2e5a;
	border-radius: 5px;
}
</style>
</head>
<body>
<h1>会員登録フォーム＆お問い合わせフォーム</h1>
<?php if( $page_flag === 1 ): ?>

<form method="post" action=""> 

	<div class="element_wrap">
		<label>氏名</label>
		<p><?php echo $clean['your_name']; ?></p>
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<p><?php echo $clean['email']; ?></p>
	</div>
	<div class="element_wrap">
		<label>パスワード</label>
		<p><?php echo $clean['password']; ?></p>
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<p><?php if( $clean['gender'] === "male" ){ echo '男性'; }else{ echo '女性'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<p><?php if( $clean['age'] === "1" ){ echo '〜19歳'; }
		elseif( $clean['age'] === "2" ){ echo '20歳〜29歳'; }
		elseif( $clean['age'] === "3" ){ echo '30歳〜39歳'; }
		elseif( $clean['age'] === "4" ){ echo '40歳〜49歳'; }
		elseif( $clean['age'] === "5" ){ echo '50歳〜59歳'; }
		elseif( $clean['age'] === "6" ){ echo '60歳〜'; } ?></p>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<p><?php echo nl2br($clean['contact']); ?></p>
	</div>
	<?php if( !empty($clean['attachment_file']) ): ?>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<p><img src="<?php echo FILE_DIR.$clean['attachment_file']; ?>"></p>
	</div>
	<?php endif; ?>
	<div class="element_wrap">
		<label>プライバシーポリシーに同意する</label>
		<p><?php if( $clean['agreement'] === "1" ){ echo '同意する'; }else{ echo '同意しない'; } ?></p>
	</div>
	<input type="submit" name="btn_back" value="戻る">
	<input type="submit" name="btn_submit" value="送信">
	<input type="hidden" name="your_name" value="<?php echo $clean['your_name']; ?>">
	<input type="hidden" name="email" value="<?php echo $clean['email']; ?>">
	<input type="hidden" name="password" value="<?php echo $clean['password']; ?>">
	<input type="hidden" name="gender" value="<?php echo $clean['gender']; ?>">
	<input type="hidden" name="age" value="<?php echo $clean['age']; ?>">
	<input type="hidden" name="contact" value="<?php echo $clean['contact']; ?>">
	<?php if( !empty($clean['attachment_file']) ): ?>
		<input type="hidden" name="attachment_file" value="<?php echo $clean['attachment_file']; ?>">
	<?php endif; ?>
	<input type="hidden" name="agreement" value="<?php echo $clean['agreement']; ?>">
</form>

<?php elseif( $page_flag === 2 ): ?>

	

<p>送信が完了しました。</p>

<?php else: ?>

<?php if( !empty($error) ): ?>
	<ul class="error_list">
	<?php foreach( $error as $value ): ?>
		<li><?php echo $value; ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
	<div class="element_wrap">
		<label>氏名</label>
		<input type="text" name="your_name" value="<?php if( !empty($clean['your_name']) ){ echo $clean['your_name']; } ?>">
	</div>
	<div class="element_wrap">
		<label>メールアドレス</label>
		<input type="text" name="email" value="<?php if( !empty($clean['email']) ){ echo $clean['email']; } ?>">
	</div>
	<div class="element_wrap">
		<label>パスワード</label>
		<input type="text" name="password" value="<?php if( !empty($clean['password']) ){ echo $clean['password']; } ?>">
	</div>
	<div class="element_wrap">
		<label>性別</label>
		<label for="gender_male"><input id="gender_male" type="radio" name="gender" value="male" <?php if( !empty($clean['gender']) && $clean['gender'] === "male" ){ echo 'checked'; } ?>>男性</label>
		<label for="gender_female"><input id="gender_female" type="radio" name="gender" value="female" <?php if( !empty($clean['gender']) && $clean['gender'] === "female" ){ echo 'checked'; } ?>>女性</label>
	</div>
	<div class="element_wrap">
		<label>年齢</label>
		<select name="age">
			<option value="">選択してください</option>
			<option value="1" <?php if( !empty($clean['age']) && $clean['age'] === "1" ){ echo 'selected'; } ?>>〜19歳</option>
			<option value="2" <?php if( !empty($clean['age']) && $clean['age'] === "2" ){ echo 'selected'; } ?>>20歳〜29歳</option>
			<option value="3" <?php if( !empty($clean['age']) && $clean['age'] === "3" ){ echo 'selected'; } ?>>30歳〜39歳</option>
			<option value="4" <?php if( !empty($clean['age']) && $clean['age'] === "4" ){ echo 'selected'; } ?>>40歳〜49歳</option>
			<option value="5" <?php if( !empty($clean['age']) && $clean['age'] === "5" ){ echo 'selected'; } ?>>50歳〜59歳</option>
			<option value="6" <?php if( !empty($clean['age']) && $clean['age'] === "6" ){ echo 'selected'; } ?>>60歳〜</option>
		</select>
	</div>
	<div class="element_wrap">
		<label>お問い合わせ内容</label>
		<textarea name="contact"><?php if( !empty($clean['contact']) ){ echo $clean['contact']; } ?></textarea>
	</div>
	<div class="element_wrap">
		<label>画像ファイルの添付</label>
		<input type="file" name="attachment_file">
	</div>
	<div class="element_wrap">
		<label for="agreement"><input id="agreement" type="checkbox" name="agreement" value="1" <?php if( !empty($clean['agreement']) && $clean['agreement'] === "1" ){ echo 'checked'; } ?>>プライバシーポリシーに同意する</label>
	</div>
	<input type="submit" name="btn_confirm" value="入力内容を確認する">
</form>

<?php endif; ?>
</body>
</htm>