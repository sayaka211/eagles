<?php
include_once("inc/functions.php");
if(isset($_REQUEST['event_id'])){
	$event_id = $_REQUEST['event_id'];
}
if(isset($_REQUEST['member_id'])){
	$member_id = $_REQUEST['member_id'];
}
if(isset($_REQUEST['attend_flg'])){
	$attend_flg = $_REQUEST['attend_flg'];
}
if(isset($_REQUEST['car_ok_flg'])){
	$car_ok_flg = $_REQUEST['car_ok_flg'];
}else{
	$car_ok_flg = 0;
}
if(isset($_REQUEST['memo'])){
	$memo = $_REQUEST['memo'];
}
if(empty($attend_id) && empty($event_id)){
	$ret['msg'] = "no attend_id or event_id";
	echo (json_encode($ret));
	exit;
}
if(empty($member_id)){
	$ret['msg'] = "no member_id";
	echo (json_encode($ret));
	exit;
}
if(empty($attend_flg)){
	$ret['msg'] = "no attend_flg";
	echo (json_encode($ret));
	exit;
}
$dbh = db_connection();

//出欠IDがすでにあれば変更
$sql = "select id from $attend_table where member_id=$member_id and event_id=$event_id";
try {
	//SELECT
	$stmt = $dbh->prepare($sql);
	$result = $stmt->execute();
	if (!$result) {
		print "getevent error";
		return null;
	}
} catch (PDOException $e) {
	print "getevent Exception";
	print $e->getMessage();
	return null;
}
$result = $stmt->fetch();
if(isset($result['id'])){
	$attend_id = $result['id'];
}else{
	$attend_id;
}

if(!empty($attend_id)){
	$sql = "UPDATE $attend_table SET attend_flg=:attend_flg,
	car_ok_flg=:car_ok_flg,
	memo=:memo,update_datetime=NOW() WHERE id=:attend_id";
//出欠IDがなければ新規
}else{
	$sql = "INSERT INTO $attend_table (event_id,member_id,attend_flg,car_ok_flg,memo,create_datetime,update_datetime)
		VALUES(:event_id,:member_id,:attend_flg,:car_ok_flg,:memo,NOW(),NOW())";
}
$stmt=$dbh->prepare($sql);
$datas = array();
//出欠IDがなければ新規
if(empty($attend_id)){
	$datas['event_id'] = $event_id;
	$datas['member_id'] = $member_id;
}else{
	$datas['attend_id'] = $attend_id;
}
$datas['attend_flg'] = $attend_flg;
$datas['car_ok_flg'] = $car_ok_flg;
$datas['memo'] = $memo;

$flag = $stmt->execute($datas);

if (!$flag){
	$ret['msg'] = "データ更新に失敗しました";
	echo (json_encode($ret));
	exit;
}

//テキスト
//$ret = array('attend_text'=>$attend_def_list[$attend_flg]);
//ステータス
$ret = array('attend_flg'=>$attend_flg);

echo json_encode($ret);
exit;