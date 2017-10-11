<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$dbh = db_connection();

//ヘッダーとイベント情報を配列で取得
$event_datas = getEventDatas($dbh);
?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<body>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>予定リスト</h3>
		<?php if(count($event_datas)==0){ echo "<br>表示する予定がありません";}else{?>
			<?php foreach($event_datas as $event_data){?>
			<div class="panel panel-primary">
			<div class="panel-heading">
			<h4 class="panel-title"><a href="./event_detail.php?event_id=<?php echo $event_data['id']?>" role="button"><?php echo date('Y-m-d（D）',strtotime($event_data['date']))?></a></h4>
			</div>
			<div class="panel-body">
			<!-- <div class="row">
			<p class="col-sm-3"><a href="./admin_attend.php?event_id=<?php echo $event_data['id']?>" class="btn btn-info" role="button">出欠リスト</a></p>
			</div>
			 -->
			<div class="row">
			<p class="col-sm-4 text-success"><span class="glyphicon glyphicon-time"></span><?php echo $event_data['meet_time']?>集合</p>
			<p class="col-sm-4 text-success"><span class="glyphicon glyphicon-map-marker"></span><?php echo $event_data['meet_place']?></p>
			<p class="col-sm-4 text-success"><span class="glyphicon glyphicon-cutlery"></span><?php echo $lunch_flg_list[$event_data['lunch_flg']]?></p>
			</div>
			★C:コーチ M:メンバー
			<table class="table table-striped table-hover">
			<tr>
			<th></th>
			<th>出席</th>
			<th>欠席</th>
			<th>未定</th>
			</tr>
				<tr>
				<?php $attend_data = $event_data['attend']?>
				<td>C</td>
				<td><?php echo $attend_data[1][1]?></td>
				<td><?php echo $attend_data[1][2]?></td>
				<td><?php echo $attend_data[1][9]?></td>
				</tr>
				<tr>
				<td>M</td>
				<td><?php echo $attend_data[0][1]?></td>
				<td><?php echo $attend_data[0][2]?></td>
				<td><?php echo $attend_data[0][9]?></td>
				</tr>
			</table>
			<div class="row">
			<div class="col-sm-12"><pre><?php echo $event_data['contents']?></pre></div>
			</div>
			</div>
			</div>
			<?php }?>
		<?php }?>
	</div>
	</body>
</html>
