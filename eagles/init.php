<?php
include_once("inc/functions.php");
//$iniParam = parse_ini_file("app.ini");
if(!isset($_POST['init_ok']) || empty($_POST['init_ok'])){
	echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
HTML;
include 'inc/header.php';
	echo <<<HTML
<body>
<div class="container">
HTML;
include 'inc/navbar.php';

	echo <<<HTML
	<h3>DB初期化</h3>
	<!-- Main Form -->
	<div class="login-form-1">
		<form id="login-form" class="text-left" method="POST">
			<div class="login-form-main-message">DBを初期化するとすべてのデータが失われます。<br>
			初期化しますか？<br><br></div>
			<div class="main-login-form">
				<button type="submit" class="btn btn-primary" name="init_ok" value="1" role="button">初期化実行</button>
			</div>
		</form>
	</div>
	<!-- end:Main Form -->
</div>
</body>
</html>
HTML;
	exit;
}
$root_user = $iniParam['root_user'];
$root_pass = $iniParam['root_password'];
$db_name = $iniParam['db_name'];
$db_host = $iniParam['db_host'];
$user = $iniParam['db_user'];
$pass = $iniParam['db_password'];

//テーブル名をapp.iniから取得
$event_table = $iniParam['event_table'];
$attend_table = $iniParam['attend_table'];
$member_table = $iniParam['member_table'];

/*--- root接続 ---*/
//接続のための情報
$dsn = "mysql:host=$db_host;charset=utf8";

try{
	//PDOによる接続
	$dbh = new PDO($dsn, $root_user, $root_pass);
	//属性の設定(接続エラーが発生したら例外を投げる設定)
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//属性の設定(プリペアドステートメントの使用)
	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $e){
	//エラーが発生したらメッセージを表示して終了
	die("エラー内容:".$e->getMessage());
}


//DB作成
/*さくらはできない
try {
	$dbh->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8;
			CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';
			GRANT ALL ON `$db_name`.* TO '$user'@'localhost';
			FLUSH PRIVILEGES;")
			or die(print_r($dbh->errorInfo(), true));

} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}

/*----------------- eventテーブル ----------------------*/
//イベントテーブル削除
$sql = "DROP TABLE IF EXISTS `$db_name`.`$event_table`";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}

//イベントテーブル作成
$sql = "CREATE TABLE `$db_name`.`$event_table` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime NOT NULL,
  `meet_time` time ,
  `meet_place` VARCHAR(1024) ,
  `lunch_flg` TINYINT  ,
  `car_use_flg` TINYINT ,
  `contents` VARCHAR(1024)  ,
  PRIMARY KEY  (`id`)
) ";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}
/*----------------- /eventテーブル ----------------------*/

/*----------------- attendテーブル ----------------------*/
//アテンドテーブル（出欠）削除
$sql = "DROP TABLE IF EXISTS `$db_name`.`$attend_table`";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}

//アテンドテーブル（出欠）作成）
$sql = "CREATE TABLE `$db_name`.`$attend_table` (
`id` int(11) NOT NULL auto_increment,
`event_id` int(11)  ,
`member_id` int(11)  ,
`attend_flg` TINYINT DEFAULT 0 ,
`car_ok_flg` TINYINT DEFAULT 0 ,
`memo`  VARCHAR(1024) ,
`create_datetime` datetime NOT NULL,
`update_datetime` datetime NOT NULL,
PRIMARY KEY  (`id`,`event_id`,`member_id`)
) ";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}
/*----------------- /attendテーブル ----------------------*/


/*----------------- memberテーブル ----------------------*/
//メンバーテーブル削除
$sql = "DROP TABLE IF EXISTS `$db_name`.`$member_table`";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}

//メンバーテーブル作成
$sql = "CREATE TABLE `$db_name`.`$member_table` (
`id` int(11) NOT NULL auto_increment,
`name` VARCHAR(20) ,
`coach_flg` TINYINT  ,
`member_flg` TINYINT  ,
`car_flg` TINYINT  ,
`sort` int(11)  ,
PRIMARY KEY  (`id`)
) ";
try {
	$dbh->exec($sql);
} catch (PDOException $e) {
	die("DB ERROR: ". $e->getMessage());
}
/*----------------- /memberテーブル ----------------------*/


echo "init success!";
exit;

