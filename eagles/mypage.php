<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$member_id = isset($_REQUEST['member_id'])?$_REQUEST['member_id']:null;
if(empty($member_id)){
	echo "IDを指定してください ";
	exit;
}
$dbh = db_connection();

$member_obj = getmember($dbh, $member_id);
if(empty($member_obj)){
	echo "メンバーが存在しません。";
	exit;
}

//ヘッダーとイベント情報を配列で取得
$event_datas = getEventDatas($dbh);

//ヘッダーID取得
$event_ids=array();
foreach($event_datas as $data){
	$event_ids[] = $data['id'];
}

if(!empty($event_ids)){
	$event_id_list = implode(",",$event_ids);
	$sql = "SELECT * FROM $attend_table WHERE member_id=$member_id
	AND event_id IN ($event_id_list)";

	try {
		//SELECT
		$result = $dbh->query($sql);
		if (!$result) {
			print "error";
		}
	} catch (PDOException $e) {
		print "Exception";
		print $e->getMessage();
		exit;
	}
	$attend_result = $result->fetchAll(PDO::FETCH_ASSOC);
	$attend_datas = array();
	//event_idをキーに配列作り直し
	foreach($attend_result as $data){
		$attend_datas[$data['event_id']] = $data;
	}
}

//出欠のクラス
$status_class_arr = array(
		9=>"mikaitou",
		1=>"syusseki",
		2=>"kesseki",
		);
?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<style>
	.mikaitou {
		background: #e1cea3;
		color: #163;
		font-weight: bold;
		letter-spacing: 1px;
	}
	.syusseki {
		background: #a5d1f4;
		color: #163;
		font-weight: bold;
		letter-spacing: 1px;
	}
	.kesseki {
		background: #e2b2c0;
		color: #163;
		font-weight: bold;
		letter-spacing: 1px;
	}
	</style>
	<body>
	<div class="container">
	<?php include 'inc/navbar.php'?>
		<h3><?php echo $member_obj['name']?>さんの予定</h3>
	<script type="text/javascript">
var status_class_arr = <?php echo json_encode($status_class_arr)?>;
$(function(){
	$(".status_form").submit(function(){
	//alert($(this).serialize());
	var selecter = "attend_"+$(this).children('[name=event_id]').val();
	//alert($('#'+selecter).val());
	now_form = $(this);
	//"_(event_id)"が、共通クラス
	var com_selecter = "_"+$(this).children('[name=event_id]').val();
	$.post('js_attend.php', $(this).serialize(),
				function(data,status){
				if(status == 'success'){
						if('msg' in data){
							//エラーの場合、メッセージ
							alert(data['msg']);
						}else{
							//値を変える
							//$('#'+selecter).text(data['attend_text']);
							//ステータスで色を変える
							//alert('更新しました');
							$("[id$="+com_selecter+"]").hide();
							$("[id="+status_class_arr[data['attend_flg']]+com_selecter+"]").show();
							$("[id$="+com_selecter+"]").parent().parent().trigger('click');
							/*
							now_form.parent("td").removeClass();
							now_form.parent("td").addClass("table_cell_center");
							now_form.parent("td").addClass(status_class_arr[data['attend_flg']]);
							*/
						}
					}else{
						alert('err');
					}
				},'json');
		return false;
	});
	$("div.attend_mark").clickToggle(function() {
		$(this).next("form").show();
	}, function() {
		$(this).next("form").hide();
	});
});
</script>
		<?php if(count($event_datas)==0){ echo "<br>表示するイベントがありません";}else{?>
			<?php
			foreach($event_datas as $event_data){
				if(isset($attend_datas[$event_data['id']])){
					$attend_data = $attend_datas[$event_data['id']];
					$attend_flg = $attend_data['attend_flg'];
					$car_ok_flg = $attend_data['car_ok_flg'];
					//echo $attend_def_list[$attend_flg];
				}else{
					$attend_data = null;
					//メモのデフォルト表示
					$memo = "";
					if($member_obj['member_flg'] == 1){
						$memo .= $event_data['member_tpl'];
					}
					if($member_obj['coach_flg'] == 1){
						$memo .= $event_data['coach_tpl'];
					}
					$attend_data['memo'] = $memo;
					//デフォルト
					$attend_flg=9;
					$car_ok_flg=1;
				}
			?>
			<div class="panel panel-primary">
			<div class="panel-heading">
				<h4 class="panel-title"><a href="./event_detail.php?event_id=<?php echo $event_data['id']?>" role="button"><?php echo date('Y-m-d（D）',strtotime($event_data['date']))?></a></h4>
			</div>
			<div class="panel-body">
<!-- 
			<div class="row">
			<p class="col-sm-3"><a href="./admin_attend.php?event_id=<?php echo $event_data['id']?>" class="btn btn-info" role="button">出欠リスト</a></p>
			</div>
 -->
			<div class="row attend_mark">
			<p class="col-sm-12">
			<i id="mikaitou_<?php echo $event_data['id']?>" style="color:#f2cf01;font-size:1em;<?php if($attend_flg != 9):?>display:none;<?php endif;?>" class="fa fa-exclamation-triangle ">予定を入力してください</i>
			<i id="syusseki_<?php echo $event_data['id']?>" style="color:#56a764;font-size:1.5em; <?php if($attend_flg != 1):?>display:none;<?php endif;?>" class="fa fa-circle-o">出席</i>
			<i id="kesseki_<?php echo $event_data['id']?>" style="color:#d16b16;font-size:1.5em;<?php if($attend_flg != 2):?>display:none;<?php endif;?>" class="fa fa-close">欠席</i>
			</p>
			</div>

			<form class="status_form" method="post" action="" style="display:none;">
				<input type="hidden" name="event_id" value="<?php echo $event_data['id']?>">
				<input type="hidden" name="member_id" value="<?php echo $member_id?>">

				<div class="form-inline">

				<!-- 出欠 -->
				<div class="form-group ">
				<select class="form-control " name="attend_flg" style="width:100px;">
				<?php foreach($attend_def_list as $key=>$value){?>
				<option value="<?php echo $key?>"<?php if($attend_flg == $key){echo " selected=\"selected\"";}?>><?php echo $value?></option>
				<?php }?>
				</select>
				</div>

				<!-- 車 -->
				<?php if($event_data['car_use_flg'] == 1 && $member_obj['car_flg']){?>
				<div class="form-group ">
					<select class="form-control " name="car_ok_flg" style="width:100px;">
					<?php foreach($car_ok_flg_list as $key=>$value){?>
						<option value="<?php echo $key?>"<?php if($car_ok_flg == $key){echo " selected=\"selected\"";}?>><?php echo $value?></option>
					<?php }?>
					</select>
				</div>
				<?php }?>
				</div>

				<!-- メモ -->
				<div class="form-group ">
				<textarea style="width:100%" rows="5" class="form-control" name="memo" placeholder="遅刻、早退、その他特記事項があればご記入ください"><?php if(isset($attend_data['memo'])){echo $attend_data['memo'];}?></textarea>
				</div>

				<div class="form-group ">
				<input type="submit" class="btn btn-default " value="更新">
				</div>

			</form>

			<div class="row">
			<p class="col-sm-3 text-success"><span class="glyphicon glyphicon-time"></span><?php echo $event_data['meet_time']?>集合</p>
			<p class="col-sm-3 text-success"><span class="glyphicon glyphicon-map-marker"></span><?php echo $event_data['meet_place']?></p>
			<p class="col-sm-3 text-success"><span class="glyphicon glyphicon-cutlery"></span><?php echo $lunch_flg_list[$event_data['lunch_flg']]?></p>
			</div>
			<div class="row col-sm-12"><pre><?php echo $event_data['contents']?></pre></div>
            </div>

          </div>
			
			<?php }?>
		<?php }?>
	</div>
	</body>
</html>
