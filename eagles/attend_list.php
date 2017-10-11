<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$dbh = db_connection();
$coach_list = getmemberlist($dbh,"coach_flg=1");
$sensyu_list = getmemberlist($dbh,"member_flg=1");
$coach_list = array_map('current', $coach_list->fetchAll(PDO::FETCH_GROUP));
$sensyu_list = array_map('current', $sensyu_list->fetchAll(PDO::FETCH_GROUP));

//イベント取得
$today = date('Y-m-d');
$event_sql = "SELECT id,date,car_use_flg FROM $event_table where date >= '$today' order by date";
try {
	//SELECT
	$result = $dbh->query($event_sql);
	if (!$result) {
		print "event data select error";
	}
	$datas = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	print "event select Exception";
	print $e->getMessage();
	exit;
}
$event_datas = array();
foreach($datas as $arr){
	$event_datas[$arr['id']]['date'] = $arr['date'];
	$event_datas[$arr['id']]['car_use_flg'] = $arr['car_use_flg'];
}
$event_coach_datas = array();
$event_member_datas = array();
foreach($event_datas as $event_id=>$event_data){
	//出席テーブルとメンバーテーブルのjoin
	$base_sql = "SELECT * FROM $attend_table AS at INNER JOIN $member_table AS me ON at.member_id=me.id
	WHERE at.event_id =$event_id
	[and_query]
	ORDER BY at.attend_flg,at.car_ok_flg desc,me.sort";

	//コーチ出欠 出席のみ
	//$sql = str_replace("[and_query]"," AND me.coach_flg = 1 AND at.attend_flg = 1",$base_sql);
	$sql = str_replace("[and_query]"," AND me.coach_flg = 1 ",$base_sql);

	try {
		//SELECT
		$result = $dbh->query($sql);
		if (!$result) {
			print "coach出欠 data select error";
		}
		$coach_datas = $result->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		print "コーチ出欠データ select Exception";
		print $e->getMessage();
		//exit;
	}
	$event_coach_datas[$event_id] = $coach_datas;
	//メンバー出欠
	//欠席のみ
	//$sql = str_replace("[and_query]"," AND me.member_flg = 1 AND at.attend_flg=2 ",$base_sql);
	//出席含む
	$sql = str_replace("[and_query]"," AND me.member_flg = 1 ",$base_sql);
	try {
		//SELECT
		$result = $dbh->query($sql);
		if (!$result) {
			print "event data select error";
		}
		$member_datas = $result->fetchAll(PDO::FETCH_ASSOC);

	} catch (PDOException $e) {
		print "メンバー出欠データ select Exception";
		print $e->getMessage();
		//exit;
	}
	//イベント情報取得
	//$event_datas = getEvent($dbh,$event_id);
	$event_member_datas[$event_id] = $member_datas;
}
?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<body>
	<script>
	//メモの開閉
	$(function(){
		$("i.memo_open").clickToggle(function() {
			$(".memo_open").removeClass("fa-minus-square");
			$(".memo_open").addClass("fa-plus-square");
			$(".memo").hide();
	}, function() {
		$(".memo_open").removeClass("fa-plus-square");
		$(".memo_open").addClass("fa-minus-square");
		$(".memo").show();	});
	});
	//出欠未入力者の開閉
	$(function(){
		$("i.pre_open").clickToggle(function() {
			$(".pre_open").removeClass("fa-plus-square");
			$(".pre_open").addClass("fa-minus-square");
			$(".pre").show();
	}, function() {
		$(".pre_open").removeClass("fa-minus-square");
		$(".pre_open").addClass("fa-plus-square");
		$(".pre").hide();	});
	});
	</script>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>出欠リスト</h3>

	<?php if(count($event_datas) == 0){echo "表示するイベント情報がありません";}?>
	<?php foreach($event_datas as $event_id=>$event_data){?>
	<?php 
	$coach_datas = isset($event_coach_datas[$event_id])?$event_coach_datas[$event_id]:array();
	$member_datas = isset($event_member_datas[$event_id])?$event_member_datas[$event_id]:array();
	//未入力ユーザ抽出
	$pre_coach_list = $coach_list;
	$pre_sensyu_list = $sensyu_list;
	foreach($coach_datas as $id=>$arr){
		unset($pre_coach_list[$arr['id']]);
	}
	foreach($member_datas as $id=>$arr){
		unset($pre_sensyu_list[$arr['id']]);
	}
	?>
	<div class="panel panel-primary">
	<div class="panel-heading">
	<h4 class="panel-title" style="float: left"><a href="./event_detail.php?event_id=<?php echo $event_id?>" role="button"><?php echo date('Y-m-d（D）',strtotime($event_data['date']))?></a></h4>
	<div style="text-align: right">Chk<i class="fa fa-plus-square pre_open" aria-hidden="true"></i></div>
	</div>
	<div class="panel-body">

	<div class="pre panel panel-danger small" style="display:none;">
	<div class="panel-heading small" style="height: 20px;padding:0">★未入力コーチ</div>
	<div class="panel-body">
	<?php 
	if(count($pre_coach_list) == 0){
		echo '未入力なし';
	}else{
		foreach($pre_coach_list as $id=>$arr){
			echo $arr['name'].", ";
		}
	}
	?>
	</div>
	<div class="panel-heading small" style="height: 20px;padding:0">★未入力メンバー</div>
	<div class="panel-body">
	<?php 
	if(count($pre_sensyu_list) == 0){
		echo "未入力なし";
	}else{
		foreach($pre_sensyu_list as $id=>$arr){
			echo $arr['name'].", ";
		}
	}
	?>
	</div>
	</div>

	<h4>★コーチ（<?php echo count($coach_datas)?>件）</h4>
		<?php if(empty($coach_datas)){ echo "情報がありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>名前</th><th nowrap>出欠</th><th>車</th><th>メモ<i class="fa fa-minus-square memo_open" aria-hidden="true"></i></th></tr>
			<?php foreach($coach_datas as $id=>$coach_data){?>
			  <tr>
			  <td nowrap><?php echo $coach_data['name']?></td>
			  <td><?php echo $attend_disp_list[$coach_data['attend_flg']];?></td>
			  <td nowrap><?php 
				if($coach_data['car_ok_flg'] == 1 && $event_data['car_use_flg'] == 1){
					echo '<i class="fa fa-car "></i>';
				}else{
					if($coach_data['car_flg'] == 1){
						echo "−";
					}
				}?></td>
			  <td><?php if(!empty($coach_data['memo'])){?><pre class="memo" ><?php echo $coach_data['memo']?></pre><?php }?></td>
			  </tr>
			<?php }?>
			</table>
		<?php }?>
	<h4>★メンバー（<?php echo count($member_datas)?>件）</h4>
		<?php if(empty($member_datas)){ echo "情報がありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>名前</th><th nowrap>出欠</th><th>メモ<i class="fa fa-minus-square memo_open" aria-hidden="true"></i></th></tr>
			<?php foreach($member_datas as $id=>$member_data){?>
			  <tr>
			  <td nowrap><?php echo $member_data['name']?></td>
			  <td nowrap><?php echo $attend_disp_list[$member_data['attend_flg']]?></td>
			  <td><?php if(!empty($member_data['memo'])){?><pre class="memo"><?php echo $member_data['memo']?></pre><?php }?></td>
			  </tr>
			<?php }?>
			</table>
		<?php }?>
	</div>
	</div>
	<?php }?>
	</div>
	</body>
</html>
