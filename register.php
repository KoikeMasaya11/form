<?php
//******************************************************
// マイページ
//******************************************************

session_start();
if( !empty($_SESSION['page']) && $_SESSION['page'] === true ){
    $mysqli = new mysqli( 'localhost', 'root', '', 'form');
    $sql =  "SELECT * from login ";
    $res = $mysqli->query($sql);
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $mysqli->close();
}else{
    //ログインしてない場合
    header('Location: ./login.php');
    exit;  
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ</title>
</head>
<body>
    <h1>マイページ</h1>
    <table class="table">
  <thead>
    <tr>
      <th scope="col">id</th>
      <th scope="col">your_name</th>
      <th scope="col">email</th>
      <th scope="col">password</th>
      <th scope="col">gender</th>
      <th scope="col">age</th>
      <th scope="col">contact</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($rows as $row) { ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['your_name']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['password']; ?></td>
        <td><?php echo $row['gender']; ?></td>
        <td><?php echo $row['age']; ?></td>
        <td><?php echo $row['contact']; ?></td>
      </tr>
    <?php } ?>
  </tbody>
</table>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>