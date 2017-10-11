<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$event_id = isset($_REQUEST['event_id'])?$_REQUEST['event_id']:null;
$dbh = db_connection();


if(empty($event_id)){
	echo "event_idが指定されていません。";
	exit;
}else{
	$title = "詳細";
	$sql = "select *,DATE_FORMAT(meet_time, '%k:%i') AS meet_time from $event_table where id='$event_id'";
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
?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<body>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>予定詳細<a href="javascript:history.back()" class="pull-right btn btn-default">戻る</a></h3>
	<div class="panel panel-info">
	<div class="panel-heading">
	<h4 class="panel-title"><?php echo date('Y-m-d（D）',strtotime($params['date']))?></h4>
	</div>
	<div class="panel-body">
	<dl>
		<dt>▼日付</dt>
		<dd ><?php echo date('Y-m-d',strtotime($params['date']))?>
		</dd>
	</dl>
	<dl>
		<dt>▼集合時間</dt>
		<dd><?php echo $params['meet_time']?>
		</dd>
	</dl>
	<dl>
		<dt>▼集合場所</dt>
		<dd><?php echo $params['meet_place']?>
		</dd>
	</dl>
	<dl>
		<dt>▼弁当</dt>
		<dd><?php echo $lunch_flg_list[$params['lunch_flg']]?>
		</dd>
	</dl>
	<dl>
		<dt>▼コーチおにぎり</dt>
		<dd><?php echo $onigiri_flg_list[$params['onigiri_flg']]?>
		</dd>
		<dd>
		<?php 
		if($params['onigiri_flg'] == 1){
			$arr = json_decode($params['onigiri_syosai']);
			if(is_array($arr)){
				foreach($arr as $toban_id){
					echo "<dd>　".$onigiri_syosai_list[$toban_id]."　";
				}
			}
		}?>
		</dd>
	</dl>
	<dl>
		<dt>▼お茶当番</dt>
		<dd><?php echo $tea_flg_list[$params['tea_flg']];?></dd>
		<?php 
		if($params['tea_flg'] == 1){
			echo "<dd>　当番 : ".$toban_users[$params['tea_toban']]."</dd>";
			echo "<dd>　ピック : ".$toban_users[$params['tea_pick']]."</dd>";
		}?>
	</dl>

	<dl>
		<dt>▼車使用</dt>
		<dd><?php echo $youhi_list[$params['car_use_flg']]?></dd>
	</dl>

	<dl>
		<dt>▼内容</dt>
		<dd><pre><?php echo $params['contents']?></pre></dd>
	</dl>
	</div>
	</div>

</div>
<br>
	</body>
</html>
