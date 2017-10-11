<?php
global $iniParam;
//adminユーザ
$admin_usr = $iniParam['admin_usr'];
//adminパスワード
$admin_pswd = $iniParam['admin_pswd'];

if(!$admin_flg){
	if(isset($_REQUEST['lg_username']) && isset($_REQUEST['lg_password'])){
		if($_REQUEST['lg_username'] == $admin_usr && $_REQUEST['lg_password']==$admin_pswd){
			setcookie('admin_ok','1',time()+60*60*24);
			header("Location: " . $_SERVER['PHP_SELF']);
			//			setcookie('password',$_POST['password'],time()+60*60*24*7);
		}
	}
	echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
HTML;
	include 'inc/header.php';
	echo <<<HTML
<body>
<div class="container">
HTML;
	include 'inc/navbar.php';

	echo <<<HTML
	<h3>管理者ログイン</h3>
	<!-- Main Form -->
	<div class="login-form-1">
		<form id="login-form" class="text-left">
			<div class="login-form-main-message"></div>
			<div class="main-login-form">
				<div class="login-group">
					<div class="form-group">
						<label for="lg_username" class="sr-only">Username</label>
						<input type="text" class="form-control" id="lg_username" name="lg_username" placeholder="username">
					</div>
					<div class="form-group">
						<label for="lg_password" class="sr-only">Password</label>
						<input type="password" class="form-control" id="lg_password" name="lg_password" placeholder="password">
					</div>
				</div>
				<button type="submit" class="btn btn-primary" role="button">ログイン</button>
			</div>
		</form>
	</div>
	<!-- end:Main Form -->
</div>
</body>
</html>
HTML;
	exit;
}
