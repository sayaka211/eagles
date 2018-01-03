<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$member_id = isset($_REQUEST['member_id'])?$_REQUEST['member_id']:null;
$dbh = db_connection();

//エラーメッセージ初期化
$err_msg = null;
//新規登録か
$sinki_flg = 0;
//初期値設定
$params = array(
		'id'=>null,
		'name'=>'',
		'car_flg'=>0,
		'coach_flg'=>0,
		'member_flg'=>0,
		'sort'=>1,
		);
if(empty($member_id)){
	//新規登録
	$sinki_flg = 1;
	$title = "登録";
}else{
	$title = "編集";
	$sql = "select * from $member_table where id='$member_id'";
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
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	//該当のIDが存在しない
	if(empty($result)){
		echo "指定したmember_idは存在しません。member_id:".$member_id;
		exit;
	}else{
		$params = $result;
	}
}
//登録する
if(isset($_REQUEST['toroku'])){
	//画面情報取得
	//$params = $_REQUEST;

	$sql="INSERT INTO $member_table (name,car_flg,coach_flg,member_flg,sort)
	VALUES(:name,:car_flg,:coach_flg,:member_flg,:sort)";

	if(!empty($member_id)){
		$sql="UPDATE $member_table set name=:name,car_flg=:car_flg,coach_flg=:coach_flg,
		member_flg=:member_flg,sort=:sort WHERE id=:id";
	}

	$stmt=$dbh->prepare($sql);
	if(!empty($member_id)){
		$datas['id'] = $member_id;
	}
	foreach($_REQUEST as $key=>$value){
		$params[$key] = $value;
	}
	if(empty($params['name'])){
		$err_msg .= "名前は必須です<br>";
	}
	if(empty($params['coach_flg']) && empty($params['member_flg'])){
		//メンバーでもコーチでもない場合はエラー
		$err_msg .= "選手かコーチを選択してください<br>";
	}
	if(!empty($err_msg)){
		goto ERR_DISP;
	}

	$datas['name'] = $params['name'];
	$datas['car_flg'] = (int)$params['car_flg'];
	$datas['coach_flg'] = (int)$params['coach_flg'];
	$datas['member_flg'] = (int)$params['member_flg'];
	$datas['sort'] = (int)$params['sort'];

	$stmt->execute($datas);

	//新規登録の場合member_idを取得
	if(!empty($member_id)){
		$member_id = $dbh->lastInsertId('id');
	}

	header( "Location: ./members.php" ) ;
	exit;

}
//削除する
if(isset($_REQUEST['delete'])){
	$sql = "delete from $member_table where id = :member_id";
	$stmt = $dbh->prepare($sql);
	$flag = $stmt->execute(array('member_id' => $member_id));
	if (!$flag){
		print('memberデータの削除に失敗しました<br>');
		exit;
	}
	//出席者情報も削除
	$sql = "delete from $attend_table where member_id = :member_id";
	$stmt = $dbh->prepare($sql);
	$flag = $stmt->execute(array('member_id' => $member_id));
	if (!$flag){
		print('memberデータの削除に失敗しました<br>');
		exit;
	}

	header( "Location: ./members.php" ) ;
	exit;

}
//エラーの場合、エラーとともに再表示
ERR_DISP:

?>
<!DOCTYPE html>
<html lang="ja">
	<?php include 'inc/header.php'?>
	<body>
	<script type="text/javascript">
	$(function(){
		//コーチ選択時
		$("[name='coach_flg']").change(function(){
			var checked = $("[name='coach_flg']").prop('checked');
			if(checked){
				$("#for_coach_fieldset").attr("disabled",false);
				$("[name='member_flg']").attr('checked',false);
			}else{
				$("#for_coach_fieldset").attr("disabled",true);
			}
		});
		//メンバー選択時
		$("[name='member_flg']").change(function(){
			var checked = $("[name='member_flg']").prop('checked');
			if(checked){
				$("#for_coach_fieldset").attr("disabled",true);
				$("[name='coach_flg']").attr('checked',false);
			}else{
				$("#for_coach_fieldset").attr("disabled",false);
			}
		});
	})
	</script>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>メンバー<?php echo $title?><a href="./members.php"  class="pull-right btn btn-default">一覧に戻る</a></h3>
	<?php if(!empty($err_msg)){?>
	<div class="alert alert-danger" role="alert">
	<?php echo $err_msg;?>
	</div>
	<?php }?>

	<form class="well form-horizontal" action="#" method="POST">
	<?php if(!empty($member_id)){?>
	<input type="hidden" name="member_id" value="<?php echo $member_id?>">
	<?php }?>
	<div class="form-group required">
		<label class="col-sm-3 control-label">名前</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="name" value="<?php echo $params['name']?>">
		</div>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">選手</label>
		<label class="col-sm-3">
			<input type="checkbox" name="member_flg" value="1" <?php if($params['member_flg']){?>checked="checked"<?php }?>>
		</label>
	</div>

	<div class="form-group">
		<label class="col-sm-3 control-label">コーチ</label>
		<label class="col-sm-3">
			<input type="checkbox" name="coach_flg" value="1" <?php if($params['coach_flg']){?>checked="checked"<?php }?>>
		</label>
	</div>
<fieldset id="for_coach_fieldset" <?php if(!$params['coach_flg']){?>disabled<?php }?>>

	<div class="form-group">
		<label class="col-sm-3 control-label">車</label>
		<div class="col-sm-2">
		<select class="form-control" name="car_flg">
			<?php foreach($kahi_list as $key=>$value){?>
			<option value="<?php echo $key?>"<?php if($params['car_flg'] == $key){echo " selected=\"selected\"";}?>><?php echo $value?></option>
			<?php }?>
		</select>
		</div>
	</div>
</fieldset>

	<div class="form-group">
		<label class="col-sm-3 control-label">ソート</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="sort" value="<?php echo $params['sort']?>">
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
