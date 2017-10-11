<?php
include_once("inc/functions.php");
header("Content-Type: text/html; charset=UTF-8");
$dbh = db_connection();
//$member_list = getmemberlist($dbh);
$coach_list = getmemberlist($dbh,"coach_flg=1");
$sensyu_list = getmemberlist($dbh,"member_flg=1");
?>
<!DOCTYPE html>
<html lang="ja">
<?php include 'inc/header.php'?>
	<body>
	<div class="container">
	<?php include 'inc/navbar.php'?>
	<h3>メンバーリスト<?php if($admin_flg){?> <a href="./member_edit.php" class="btn btn-primary" role="button">追加</a><?php }?></h3>
	<h4>★コーチ</h4>
		<?php if(empty($coach_list)){ echo "<br>表示するメンバーがありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>No.</th><th>名前</th><th></th><th>詳細</th><?php if($admin_flg){?><th>ソート</th><?php }?></tr>
			<?php foreach($coach_list as $i=>$arr){?>
			  <tr>

			  <td><?php echo ++$i?></td>

			  <td><?php echo $arr['name']?></td>

			  <td><?php if($arr['car_flg'] == 1):?><i class="fa fa-car "></i><?php endif;?>
			  </td>

			  <td><a href="./mypage.php?member_id=<?php echo $arr['id']?>" class="btn btn-default" role="button">個人ページ</a></td>

			  <?php if($admin_flg){?> 
			  <td><?php echo $arr['sort']?>
			  <a href="member_edit.php?member_id=<?php echo $arr['id']?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
			  </td>
			  <?php }?>

			  </tr>
			<?php }?>
			</table>
		<?php }?>

	<h4>★選手</h4>
		<?php if(empty($sensyu_list)){ echo "<br>表示する選手がありません";}else{?>
			<table class="table table-striped table-hover">
			  <tr><th>No.</th><th>名前</th><th>詳細</th><?php if($admin_flg){?><th>ソート</th><?php }?></tr>
			<?php foreach($sensyu_list as $i=>$arr){?>
			  <tr>

			  <td><?php echo ++$i?></td>

			  <td><?php echo $arr['name']?></td>

			  <td><a href="./mypage.php?member_id=<?php echo $arr['id']?>" class="btn btn-default" role="button">個人ページ</a></td>

			  <?php if($admin_flg){?> 
			  <td><?php echo $arr['sort']?>
			  <a href="member_edit.php?member_id=<?php echo $arr['id']?>"><i class="fa fa-pencil" aria-hidden="true"></i></a>
			  </td>
			  <?php }?>

			  </tr>
			<?php }?>
			</table>
		<?php }?>
	</div>
	</body>
</html>
