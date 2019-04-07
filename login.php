<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/data.php';
require __DIR__ . '/func/alert.php';

$showform = true;
if ($_GET['action'] === 'login') {
	if ($U['islogin']) {
		add_alert('已經登入了', 'info');
		$showform = false;
	} else if (isset($_POST['account'])) {
		$sth = $G['db']->prepare('SELECT * FROM `admin` WHERE `adm_account` = :adm_account');
		$sth->bindValue(":adm_account", $_POST['account']);
		$sth->execute();
		$account = $sth->fetch(PDO::FETCH_ASSOC);
		$login_ok = false;
		if ($account !== false) {
			if ($account['adm_password'] === '') {
				$fp = @fsockopen($C['webmail'], 110, $errno, $errstr, 10);
				if (!$fp) {
					add_alert("連接伺服器發生錯誤: $errstr ($errno)", 'danger');
				} else {
					$user_id = $_POST['account'];
					$user_passwd = $_POST['password'];
					fgets($fp, 128);
					fputs($fp, "USER $user_id\n");
					fgets($fp, 128);
					fputs($fp, "PASS $user_passwd\n");
					if (!feof($fp)) {
						$res = fgets($fp, 128);
						if (substr($res, 0, 14) == '+OK Logged in.') {
							$login_ok = true;
						}
					}
					fputs($fp, "QUIT\n");
					fclose($fp);
				}
			} else {
				if (password_verify($_POST['password'], $account['adm_password'])) {
					$login_ok = true;
				}
			}
		}

		if ($login_ok) {
			$cookie = md5(uniqid(rand(), true));
			$sth = $G['db']->prepare('INSERT INTO `login_session` (`ls_account`, `ls_cookie`) VALUES (:ls_account, :ls_cookie)');
			$sth->bindValue(":ls_account", $_POST['account']);
			$sth->bindValue(":ls_cookie", $cookie);
			$sth->execute();
			setcookie($C['cookiename'], $cookie, time() + $C['cookieexpire'], $C['path']);

			$U['data'] = $account;
			$U['islogin'] = true;
			$showform = false;

			add_alert('登入成功', 'success');
		} else {
			add_alert('登入失敗', 'danger');
		}
	}
} else if ($_GET['action'] === 'logout') {
	if (isset($_COOKIE[$C['cookiename']])) {
		$sth = $G['db']->prepare('DELETE FROM `login_session` WHERE `ls_cookie` = :ls_cookie');
		$sth->bindValue(":ls_cookie", $_COOKIE[$C['cookiename']]);
		$sth->execute();
		setcookie($C['cookiename'], '', time(), $C['path']);
	}
	$U['islogin'] = false;
	$showform = false;

	add_alert('已登出', 'success');
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C['titlename']?>/<?=($_GET['action'] === 'login' ? '登入' : '登出')?></title>
</head>

<body>
<?php

require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
	?>
<div class="container">
	<h2>登入</h2>
	<form action="" method="post">
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 帳號</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="account" required>
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-hashtag" aria-hidden="true"></i> 密碼</label>
			<div class="col-sm-10">
				<input class="form-control" type="password" name="password" required>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-10 offset-sm-2">
				<button type="submit" class="btn btn-success" name="action" value="new"><i class="fa fa-sign-in" aria-hidden="true"></i> 登入</button>
			</div>
		</div>
	</form>
</div>

<?php
}
require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>
