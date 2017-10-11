<?php
//初期設定ファイル
$iniParam = parse_ini_file("inc/app.ini");
date_default_timezone_set('Asia/Tokyo');
header("Content-Type: text/html; charset=UTF-8");

//テーブル名をapp.iniから取得
$event_table = $iniParam['event_table'];
$attend_table = $iniParam['attend_table'];
$member_table = $iniParam['member_table'];

//管理者判定
$admin_flg = 0;
if(isset($_COOKIE['admin_ok']) && !empty($_COOKIE['admin_ok'])){
	$admin_flg = 1;
}
//要否リスト（車、審判）
$youhi_list = array(
		'1'=>"あり",
		'2'=>"なし",
);
//可否リスト（車、審判）
$kahi_list = array(
		'1'=>"可",
		'0'=>"不可",
);
//車出せるか出せないか選択リスト
$car_ok_flg_list = array(
		'1'=>"車可",
		'0'=>"車不可",
);
//審判できるかできないか選択リスト
$sinpan_ok_flg_list = array(
		'0'=>"審判不可",
		'1'=>"審判可",
);
$attend_def_list = array(
		'9'=>"未定",
		'1'=>"出席",
		'2'=>"欠席",
);
$attend_disp_list = array(
		'9'=>"ー",
		'1'=>"◯",
		'2'=>"×",
);
$toban_users = array(
		'1'=>"尾崎",
		'2'=>"吉井",
		'3'=>"加賀屋",
		'4'=>"山田",
		'5'=>"仲",
		'6'=>"手塚",
		'7'=>"萱野",
		'8'=>"一方井",
		'9'=>"久木",
		'10'=>"小野",
		'11'=>"品川",
		'12'=>"比留間",
		'13'=>"西山",
		'99'=>"なし",
);
$lunch_flg_list = array(
		1=>"弁当あり",
		2=>"弁当なし",
		3=>"未定",
		);
$tea_flg_list = array(
		1=>"あり",
		2=>"なし",
		3=>"未定",
		);
$onigiri_flg_list = array(
		1=>"あり",
		2=>"なし",
		3=>"未定",
		);
$onigiri_syosai_list = array(
		1=>"６年（１個）",
		2=>"６年（２個）",
		3=>"５年（１個）"
		);

/*
$menbers = array(
		'1'=>"尾崎（湧）",
		'2'=>"佐藤",
		'3'=>"吉井",
		'4'=>"加賀屋（京）",
		'5'=>"山田",
		'6'=>"仲",
		'7'=>"手塚（遼）",
		'8'=>"萱野",
		'9'=>"一方井",
		'10'=>"加賀屋（佑）",
		'11'=>"西山",
		'12'=>"久木（裕）",
		'13'=>"小野",
		'14'=>"品川",
		'15'=>"比留間",
		'16'=>"安永",
);

$coaches = array(
		'1'=>"香取",
		'2'=>"佐藤",
		'3'=>"尾崎",
		'4'=>"加賀屋",
		'5'=>"山田",
		'6'=>"小寺",
		'7'=>"田賀谷",
		'8'=>"仲",
		'9'=>"萱野",
		'10'=>"手塚",
		'11'=>"吉井",
		'12'=>"品川",
);
*/

function db_connection(){
	global $iniParam;

	//DBのユーザ
	$db_user = $iniParam['db_user'];
	//DBのパスワード
	$db_password = $iniParam['db_password'];
	//ホスト
	$db_host = $iniParam['db_host'];
	//接続するDBの名称
	$db_name = $iniParam['db_name'];
	//接続するDBタイプ(他にpgsql,sqliteなどがある)
	$db_type = "mysql";

	//接続のための情報
	$dsn = "$db_type:host=$db_host;dbname=$db_name;charset=utf8";

	try{
		//PDOによる接続
		$dbh = new PDO($dsn, $db_user, $db_password);
		//属性の設定(接続エラーが発生したら例外を投げる設定)
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//属性の設定(プリペアドステートメントの使用)
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}catch(PDOException $e){
		//エラーが発生したらメッセージを表示して終了
		die("エラー内容:".$e->getMessage());
	}
	return $dbh;
}
/**
 * メンバー配列取得
 */
function getmemberlist($dbh,$where=null){
	global $member_table;
	if(empty($dbh)){
		echo "ハンドルなし";
		return null;
	}
	$sql = "SELECT * from $member_table [where] order by sort";
	if(!empty($where)){
		$sql = str_replace("[where]","where ".$where,$sql);
	}else{
		$sql = str_replace("[where]","",$sql);
	}

	try {
		//SELECT
		$result = $dbh->query($sql);
		if (!$result) {
			print "getmemberlist error";
			return null;
		}
	} catch (PDOException $e) {
		print "getmemberlist Exception";
		print $e->getMessage();
		return null;
	}
	return $result;
}

/**
 * イベント情報取得
 */
function getevent($dbh,$id){
	global $event_table;
	if(empty($dbh)){
		echo "ハンドルなし";
		return null;
	}
	if(empty($id)){
		echo "IDなし";
		return null;
	}
	$sql = "SELECT *,IF(meet_time,DATE_FORMAT(meet_time, '%k:%i'),NULL) AS meet_time from $event_table where id=".$id;
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
	return $result;
}

/**
 * メンバー情報取得
 */
function getmember($dbh,$id){
	global $member_table;
	if(empty($dbh)){
		echo "ハンドルなし";
		return null;
	}
	if(empty($id)){
		echo "IDなし";
		return null;
	}
	$sql = "SELECT * from $member_table where id=".$id;
	try {
		//SELECT
		$stmt = $dbh->prepare($sql);
		$result = $stmt->execute();
		if (!$result) {
			print "getmember error";
			return null;
		}
	} catch (PDOException $e) {
		print "getmember Exception";
		print $e->getMessage();
		return null;
	}
	$result = $stmt->fetch();
	return $result;
}

/**
 * ヘッダとイベントを合わせて配列で取得
 */

function getEventDatas($dbh){
	global $member_table;
	global $event_table;
	global $attend_table;
	//未来の予定のみ表示
	$event_datas = array();
	$today = date('Y-m-d');
	$sql = "select *,DATE_FORMAT(meet_time, '%k:%i') AS meet_time from $event_table WHERE date >= '$today' order by date ";

	try {
		//SELECT
		$result = $dbh->query($sql);
		if (!$result) {
			print "event data select error";
		}
		$event_datas = $result->fetchAll(PDO::FETCH_ASSOC);
	
	} catch (PDOException $e) {
		print "event data select Exception";
		print $e->getMessage();
		return;
	}
	global $attend_def_list;
	if(!empty($event_datas)){
		foreach($event_datas as $ei=>$event_data){
			$event_datas[$ei]['attend'] = array();
			$event_id = $event_data['id'];
			$sql = "SELECT event_id,attend_flg,coach_flg,count(*) AS cnt FROM $attend_table as attend inner join $member_table as member on attend.member_id=member.id
			WHERE event_id='$event_id' GROUP BY event_id,attend_flg,member.coach_flg;";
			try {
				//SELECT
				$result = $dbh->query($sql);
				if (!$result) {
					print "attend data select error";
				}
				$attend_datas = $result->fetchAll(PDO::FETCH_ASSOC);
	
			} catch (PDOException $e) {
				print "attend data select Exception";
				print $e->getMessage();
				//exit;
			}
			//0で初期化
			foreach($attend_def_list as $attend_flg=>$val){
				//coach_flg、attend_flgをキーに
				$event_datas[$ei]['attend'][0][$attend_flg] = 0;
				$event_datas[$ei]['attend'][1][$attend_flg] = 0;
			}
			foreach($attend_datas as $atd_i=>$attend_data){
				$attend_flg = $attend_data['attend_flg'];
				$cnt = $attend_data['cnt'];
				$coach_flg = $attend_data['coach_flg'];
				$event_datas[$ei]['attend'][$coach_flg][$attend_flg] = $cnt;
			}
		}
	}
	return $event_datas;
}