<?php
//******************************************************
// ログインフォーム
//******************************************************

session_start();

// データベースに接続
$mysqli = new mysqli( 'localhost', 'root', '', 'form');
$error = array();

//押されたらサニタイズ
if( isset($_POST['btn_login'])) {
foreach( $_POST as $key => $value ) {
    $clean[$key] = htmlspecialchars( $value, ENT_QUOTES);
}

$error = validation($clean , $mysqli);
    if( empty($error) ) {
    $_SESSION['page'] = true;
    header('Location: ./register.php');
    exit;  
    }
}

function validation($data , &$mysqli) {
    $error = array();
    // メールアドレスのバリデーション
    if( empty($data['email_login']) ) {
        $error[] = "「メールアドレス」は必ず入力してください。";
        }elseif( !preg_match( '/^[0-9a-z_.\/?-]+@([0-9a-z-]+\.)+[0-9a-z-]+$/', $data['email_login']) ) {
        $error[] = "「メールアドレス」は正しい形式で入力してください。";
    } 
        //パスワードのバリデーション
    if(empty($data['password_login']) ){
        $error[] = "「パスワード」は必ず入力してください。";
        }
    //アカウント参照
    $sql = "SELECT * from login where email = '$data[email_login]'";
    $res = $mysqli->query($sql);
    $user_data = $res->fetch_assoc();
    $mysqli->close();
    if (empty($user_data)) {
        $error[] = "メールアドレスまたはパスワ―ドが間違っています。";
    }elseif(!password_verify($data['password_login'],$user_data['password'])){
        $error[] = "メールアドレスまたはパスワ―ドが間違っています。";
    }
    return $error;
}  

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <title>ログイン画面</title>
</head>
<body>
<?php if( !empty($error) ): ?>
<ul>
<?php foreach( $error as $value ): ?>
    <li><?php echo $value; ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

<form method = "post" action="" enctype="multipart/form-data"> 
<div class="form-group">
    <label for="exampleInputEmail1">メールアドレス</label>
    <input type="email" name = "email_login" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
</div>
<div class="form-group">
    <label for="exampleInputPassword1">パスワード</label>
    <input type="password" name = "password_login" class="form-control" id="exampleInputPassword1" placeholder="Password">
</div>
<button type="submit" name = "btn_login" class="btn btn-primary">サインイン</button>
</form>
<a href="./index.php" class="btn btn-success">新規登録</a>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>    
</body>
</html>