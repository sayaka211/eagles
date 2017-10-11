<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$event_id = isset($_REQUEST['event_id'])?$_REQUEST['event_id']:null;
$dbh = db_connection();
//要ログイン
include_once('inc/login.php');

//エラーメッセージ初期化
$err_msg = null;
//新規登録か
$sinki_flg = 0;
//初期値設定
$params = array(
		'id'=>null,
		'date'=>date('Y-m-d'),
		'meet_time'=>"09:00",
		'meet_place'=>"",
		'lunch_flg'=>1,
		'car_use_flg'=>'0',
		'contents' => '',
		);
if(empty($event_id)){
	//新規登録
	$sinki_flg = 1;
	$title = "登録";
}else{
	$title = "編集";
	$sql = "select * from $event_table where id='$event_id'";
	try {
		//SELECT
 		$stmt = $dbh->prepare($sql);
 		$result = $stmt->execute();
		if (!$result) {
			print "error";
		}
	} catch (PDOException $e) {
		print "Exception";
		    print $e->getMessage();
		    exit;
	}
	$result = $stmt->fetch();
	//該当のIDが存在しない
	if(empty($result)){
		echo "指定したevent_idは存在しません。event_id:".$event_id;
		exit;
	}else{
		$params = $result;
	}
}
//登録する
if(isset($_REQUEST['toroku'])){
	//画面情報取得
	$params = $_REQUEST;

	$sql="INSERT INTO $event_table (date,meet_time,meet_place,lunch_flg,
			car_use_flg,contents)
	VALUES(:date,:meet_time,:meet_place,:lunch_flg,
			:car_use_flg,:contents)";

	if(isset($_REQUEST['event_id'])){
		$sql="UPDATE $event_table set date=:date,meet_time=:meet_time,meet_place=:meet_place,lunch_flg=:lunch_flg,
		car_use_flg=:car_use_flg,contents=:contents
		where id = :id";
	}else{
		//すでに登録された日ではないかチェック
		$chk_sql = "SELECT * FROM $event_table WHERE date='{$params['date']} 00:00:00'";
		try {
			//SELECT
			$stmt = $dbh->prepare($chk_sql);
			$result = $stmt->execute();
		} catch (PDOException $e) {
		}
		$result = $stmt->fetch();
		//該当のIDが存在しない
		if(!empty($result)){
			//登録済み！エラー表示
			$err_msg = "「{$params['date']}」のデータは既に登録済みです";
			goto ERR_DISP;
		}
	}

	$stmt=$dbh->prepare($sql);
	if(isset($params['event_id'])){
		$datas['id'] = $params['event_id'];
	}
	$datas['date'] = $params['date'];
	$datas['meet_time'] = $params['meet_time'];
	$datas['meet_place'] = $params['meet_place'];
	$datas['lunch_flg'] = (int)$params['lunch_flg'];
	$datas['car_use_flg'] = (int)$params['car_use_flg'];
	$datas['contents'] = $params['contents'];

	$stmt->execute($datas);

	//新規登録の場合event_idを取得
	if(!isset($_REQUEST['event_id'])){
		$event_id = $dbh->lastInsertId('id');
	}

	header( "Location: ./admin.php" ) ;
	exit;

}
//削除する
if(isset($_REQUEST['delete'])){
	$sql = 'delete from $event_table where id = :event_id';
	$stmt = $dbh->prepare($sql);
	$flag = $stmt->execute(array(':event_id' => $event_id));

	if (!$flag){
		print('eventデータの削除に失敗しました<br>');
		exit;
	}
	//出席者情報も削除
	$sql = "delete from $attend_table where event_id = :event_id";
	$stmt = $dbh->prepare($sql);
	$flag = $stmt->execute(array(':event_id' => $event_id));

	if (!$flag){
		print('attendデータの削除に失敗しました<br>');
		exit;
	}

	header( "Location: ./admin.php" ) ;
	exit;

}
//エラーの場合、エラーとともに再表示
ERR_DISP:

?>
<!DOCTYPE html>
<html lang="ja">
	<?php include 'inc/header.php'?>
	<body>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3> <?php echo $title?><a href="./admin.php"  class="pull-right btn btn-default">一覧に戻る</a></h3>
	<?php if(!empty($err_msg)){?>
	<div class="alert alert-danger" role="alert">
	<?php echo $err_msg;?>
	</div>
	<?php }?>

		<form class="well form-horizontal" action="#" method="POST">
	<?php if(!empty($event_id)){?>
	<input type="hidden" name="event_id" value="<?php echo $event_id?>">
	<?php }?>
	<div class="form-group required">
		<label class="col-sm-3 control-label">日付</label>
		<div class="col-sm-4 ">
			<input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d',strtotime($params['date']))?>">
		</div>
	</div>
	<div class="form-group required">
		<label class="col-sm-3 control-label">集合時間</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="meet_time" list="data_time" value="<?php echo $params['meet_time']?>">
			<datalist id="data_time">
			<option value="07:00"></option>
			<option value="08:00"></option>
			<option value="09:00"></option>
			<option value="10:00"></option>
			<option value="11:00"></option>
			<option value="12:00"></option>
			<option value="13:00"></option>
			<option value="14:00"></option>
			</datalist>
		</div>
	</div>

	<div class="form-group required">
		<label class="col-sm-3 control-label">集合場所</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="meet_place" list="data_place" value="<?php echo $params['meet_place']?>">
			<datalist id="data_place">
			<option value="中目黒公園"></option>
			<option value="中目黒小学校"></option>
			<option value="駒場野公園"></option>
			<option value="田道広場公園"></option>
			<option value="碑文谷公園野球場"></option>
			</datalist>
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">弁当</label>
		<div class="col-sm-2">
		<select class="form-control" name="lunch_flg">
			<?php foreach($lunch_flg_list as $key=>$value){?>
			<option value="<?php echo $key?>"<?php if($params['lunch_flg'] == $key){echo " selected=\"selected\"";}?>><?php echo $value?></option>
			<?php }?>
		</select>
		</div>
	</div>

	<div class="form-group">
	<label class="col-sm-3 control-label">車移動</label>
		<div class="col-sm-2">
		<select class="form-control" name="car_use_flg">
			<?php foreach($youhi_list as $key=>$value){?>
			<option value="<?php echo $key?>"<?php if($params['car_use_flg'] == $key){echo " selected=\"selected\"";}?>><?php echo $value?></option>
			<?php }?>
		</select>
		</div>
	</div>
	
	<div class="form-group">
		<label class="col-sm-3 control-label" for="InputTextarea2">内容</label>
		<div class="col-sm-9">
			<textarea rows="5" class="form-control" name="contents"><?php echo $params['contents']?></textarea>
		</div>
	</div>

	<div><hr></div>


	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-8 col-xs-6">
			<button type="submit" class="btn btn-primary" name="toroku" value="1">登録</button>
		</div>
		<?php if(!$sinki_flg){ //新規作成時は削除ボタンなし?>
		<div class="col-sm-2 col-xs-6">
			<button type="submit" class="btn btn-default" name="delete" value="1" onclick='return confirm("削除してよろしいですか？");'>削除</button>
		</div>
		<?php }?>
	</div>
</form>
</div>
<br>
	</body>
</html>
