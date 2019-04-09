<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/data.php';
require __DIR__ . '/func/alert.php';

$showform = true;
if (!$U["islogin"]) {
	add_alert('此功能需要驗證帳號，請<a href="' . $C["path"] . '/login/">登入</a>');
	$showform = false;
}

if ($showform) {
	$D['account'] = get_account();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'new') {
	if (isset($D['account'][$_POST['account']])) {
		add_alert('已有帳號' . htmlentities($_POST['account']), 'danger');
	} else if ($_POST['account'] === '' || $_POST['name'] === '') {
		add_alert('帳號、密碼、姓名不可為空', 'danger');
	} else if (!isset($_POST['usewebmail']) && $_POST['password'] === '') {
		add_alert('請勾選使用Web Mail或是輸入密碼', 'danger');
	} else {
		$sth = $G['db']->prepare('INSERT INTO `admin` (`adm_account`, `adm_password`, `adm_name`) VALUES (:adm_account, :adm_password, :adm_name)');
		$sth->bindValue(':adm_account', $_POST['account']);
		$sth->bindValue(':adm_name', $_POST['name']);
		if (isset($_POST['usewebmail'])) {
			$sth->bindValue(':adm_password', '');
		} else {
			$sth->bindValue(':adm_password', password_hash($_POST['password'], PASSWORD_DEFAULT));
		}
		$sth->execute();
		$D['account'][$_POST['account']] = ['adm_account' => $_POST['account'], 'adm_name' => $_POST['name']];

		add_alert('已新增' . htmlentities($_POST['account']), 'success');
	}
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'edit') {
	if (!isset($D['account'][$_POST['account']])) {
		add_alert('找不到帳號' . htmlentities($_POST['account']), 'danger');
	} else {
		if (isset($_POST['usewebmail'])) {
			if ($D['account'][$_POST['account']]['adm_password'] !== '') {
				$sth = $G["db"]->prepare("UPDATE `admin` SET `adm_password` = '' WHERE `adm_account` = :adm_account");
				$sth->bindValue(":adm_account", $_POST['account']);
				$sth->execute();

				add_alert('已將' . htmlentities($_POST['account']) . '的驗證方式改為Web Mail', 'success');
			}
		} else {
			if ($_POST['password'] !== '') {

				$sth = $G["db"]->prepare("UPDATE `admin` SET `adm_password` = :adm_password WHERE `adm_account` = :adm_account");
				$sth->bindValue(':adm_password', password_hash($_POST['password'], PASSWORD_DEFAULT));
				$sth->bindValue(":adm_account", $_POST['account']);
				$sth->execute();

				add_alert('已修改' . htmlentities($_POST['account']) . '的密碼', 'success');
			}
		}
	}
	if ($_POST['name'] !== '') {
		$sth = $G["db"]->prepare("UPDATE `admin` SET `adm_name` = :adm_name WHERE `adm_account` = :adm_account");
		$sth->bindValue(":adm_name", $_POST['name']);
		$sth->bindValue(":adm_account", $_POST['account']);
		$sth->execute();

		add_alert('已修改' . htmlentities($_POST['account']) . '的姓名', 'success');
	}
}

if ($showform && isset($_POST['delete'])) {
	if ($_POST['delete'] == $U['data']['account']) {
		add_alert('你無法刪除自己的帳號', 'danger');
	} else if (isset($D['account'][$_POST['delete']])) {
		$sth = $G['db']->prepare('DELETE FROM `admin` WHERE `adm_account` = :adm_account');
		$sth->bindValue(':adm_account', $_POST['delete']);
		$sth->execute();
		unset($D['account'][$_POST['delete']]);
		add_alert('已刪除帳號' . htmlentities($_POST['delete']), 'success');
	}
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/管理帳號</title>
</head>

<body>
<?php

require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
?>
<div class="container">
	<h2>管理帳號</h2>
	<form action='' method="post">
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>帳號</th>
					<th>姓名</th>
					<th>刪除</th>
				</tr>
				<?php
				foreach ($D['account'] as $account) {
				?>
				<tr>
					<td><?=htmlentities($account['adm_account'])?></td>
					<td><?=htmlentities($account['adm_name'])?></td>
					<td>
						<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteAccount" data-account="<?=htmlentities($account['adm_account'])?>" data-admname="<?=htmlentities($account['adm_name'])?>"><i class="fas fa-trash"></i> 刪除</button>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
		</div>
	</form>
	<h3>新增/修改</h3>
	<form action='' method="post">
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 帳號</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="account" placeholder="必填">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-hashtag" aria-hidden="true"></i> 密碼</label>
			<div class="col-sm-10">
				<div class="form-check form-check-inline">
					<input class="form-check-input" type="checkbox" name="usewebmail" id="usewebmail">
					<label class="form-check-label" for="usewebmail">
						使用學校Web Mail驗證（勾選此項則無需輸入密碼）
					</label>
				</div>
				<input class="form-control" type="password" name="password" placeholder="新增時必填，不修改留空">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-header" aria-hidden="true"></i> 姓名</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="name" placeholder="新增時必填，不修改留空" autocomplete="name">
			</div>
		</div>
		<div class="row">
			<div class="col-sm-10 offset-sm-2">
				<button type="submit" class="btn btn-success" name="action" value="new"><i class="fa fa-plus" aria-hidden="true"></i> 新增</button>
				<button type="submit" class="btn btn-success" name="action" value="edit"><i class="fas fa-pencil-alt"></i> 修改</button>
			</div>
		</div>
	</form>
</div>

<div class="modal fade" id="deleteAccount" tabindex="-1" role="dialog" aria-labelledby="deleteAccountLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="delete" id="dadm_account" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteAccountLabel">刪除管理員</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary">刪除</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
window.onload = function(){
	$('#deleteAccount').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var account = button.data('account');
		var name = button.data('admname');
		var modal = $(this);
		modal.find('.modal-title').text('您真的要刪除管理員 ' + account + ' (' + name + ') 嗎？')
		modal.find('input#dadm_account').val(account);
	});
}
</script>

<?php
}
require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>
