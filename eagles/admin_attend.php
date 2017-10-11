<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$event_id = isset($_REQUEST['event_id'])?$_REQUEST['event_id']:null;
if(is_null($event_id)){
	echo "event_idが指定されていません";
	exit;
}
$dbh = db_connection();
//$member_list = getmemberlist($dbh);

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
$event_data = getEvent($dbh,$event_id);

?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<body>
	<script>
	$(function(){
		$("i.memo_open").clickToggle(function() {
		$(".memo").show();
	    console.log("ok");
	}, function() {
		$(".memo").hide();
	    console.log("ng");
	});
	});
	</script>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>出欠リスト<a href="javascript:history.back()" class="pull-right btn btn-default">戻る</a></h3>
	<div class="panel panel-info">
	<div class="panel-heading">
	<h4 class="panel-title"><a href="./event_detail.php?event_id=<?php echo $event_data['id']?>" role="button"><?php echo date('Y-m-d（D）',strtotime($event_data['date']))?></a></h4>
	</div>
	<div class="panel-body">
	<h4>★コーチ（<?php echo count($coach_datas)?>件）</h4>
		<?php if(empty($coach_datas)){ echo "情報がありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>名前</th><th nowrap>出欠</th><th>車</th><th>メモ<i class="fa fa-plus-square memo_open" aria-hidden="true"></i></th></tr>
			<?php foreach($coach_datas as $id=>$coach_data){?>
			  <tr>
			  <td nowrap><?php echo $coach_data['name']?></td>
			  <td><?php echo $attend_disp_list[$coach_data['attend_flg']];?></td>
			  <td nowrap><?php 
				if($coach_data['car_ok_flg'] == 1){
					echo '<i class="fa fa-car "></i>';
				}else{
					if($coach_data['car_flg'] == 1){
						echo "−";
					}
				}?></td>
			  <td><?php if(!empty($coach_data['memo'])){?><pre class="memo" style="display: none;"><?php echo $coach_data['memo']?></pre><?php }?></td>
			  </tr>
			<?php }?>
			</table>
		<?php }?>
	<h4>★メンバー（<?php echo count($member_datas)?>件）</h4>
		<?php if(empty($member_datas)){ echo "情報がありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>名前</th><th nowrap>出欠</th><th>メモ<i class="fa fa-plus-square memo_open" aria-hidden="true"></i></th></tr>
			<?php foreach($member_datas as $id=>$member_data){?>
			  <tr>
			  <td nowrap><?php echo $member_data['name']?></td>
			  <td nowrap><?php echo $attend_disp_list[$member_data['attend_flg']]?></td>
			  <td><?php if(!empty($member_data['memo'])){?><pre class="memo" style="display: none;"><?php echo $member_data['memo']?></pre><?php }?></td>
			  </tr>
			<?php }?>
			</table>
		<?php }?>
	</div>
	</div>
	</div>
	</body>
</html>
