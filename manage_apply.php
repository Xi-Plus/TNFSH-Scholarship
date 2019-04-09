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
	$D['apply'] = get_apply();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'new_app') {
	$sth = $G["db"]->prepare("INSERT INTO `apply` (`app_name`) VALUES (:app_name)");
	$sth->bindValue(":app_name", $_POST['app_name']);
	$sth->execute();

	add_alert(sprintf('已新增 %s', htmlentities($_POST['app_name'])), 'success');
	$D['apply'] = get_apply();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'edit_app') {
	$sth = $G["db"]->prepare("UPDATE `apply` SET`app_name` = :app_name WHERE `app_id` = :app_id");
	$sth->bindValue(":app_name", $_POST['app_name']);
	$sth->bindValue(":app_id", $_POST['app_id']);
	$sth->execute();

	add_alert(sprintf('已將 %s 修改為 %s',
		htmlentities($D['apply'][$_POST['app_id']]),
		htmlentities($_POST['app_name'])
	), 'success');
	$D['apply'] = get_apply();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'delete_app') {
	$sth = $G["db"]->prepare("DELETE FROM `apply` WHERE `app_id` = :app_id");
	$sth->bindValue(":app_id", $_POST['app_id']);
	$res = $sth->execute();

	$app_name = htmlentities($D['apply'][$_POST['app_id']]);

	if ($res === false) {
		if ($sth->errorCode() == "23000") {
			add_alert('刪除 ' . $app_name . ' 失敗，仍有獎學金公告使用此申請單位。', 'danger');
		} else {
			add_alert('刪除 ' . $app_name . ' 失敗。', 'danger');
		}
	} else {
		add_alert('已刪除 ' . $app_name, 'success');
		$D['apply'] = get_apply();
	}
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/管理申請單位</title>
</head>

<body>
<?php

require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
	?>
<div class="container">
	<h2>管理申請單位</h2>
	<h3>新增</h3>
	<button type="button" class="btn btn-success" data-toggle="modal" data-target="#editApply" data-appaction="new_app" data-appid="<?=$app_id?>" data-appid="0" data-appname="">
		<i class="fa fa-plus"></i>　新增申請單位</button>
	<h3>列表</h3>
	<div class="table-responsive">
		<table class="table">
			<tr>
				<th>申請單位</th>
			</tr>
			<?php foreach ($D['apply'] as $app_id => $app_name) {?>
			<tr>
				<td>
				<?=htmlentities($app_name);?>
					<button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#editApply" data-appaction="edit_app" data-appid="<?=$app_id?>" data-appname="<?=htmlentities($app_name)?>"><i class="fas fa-pencil-alt"></i></button>
					<button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteApply" data-appid="<?=$app_id?>" data-appname="<?=htmlentities($app_name)?>"><i class="fas fa-trash"></i></button>
				</td>
			</tr>
			<?php }?>
		</table>
	</div>
</div>

<div class="modal fade" id="editApply" tabindex="-1" role="dialog" aria-labelledby="editApplyLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" id="eq_action" type="hidden">
				<input name="app_id" id="eq_app_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="editApplyLabel">修改申請單位</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="edit-new-app-name" class="col-form-label">申請單位名稱</label>
						<input type="text" class="form-control" name="app_name" id="eq_app_name" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary" id="eq_submit">修改</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="deleteApply" tabindex="-1" role="dialog" aria-labelledby="deleteApplyLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" type="hidden" value="delete_app">
				<input name="app_id" id="dapp_app_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteApplyLabel">刪除申請單位</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					提示：若仍有獎學金公告使用此申請單位，將無法刪除。
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
	$('#editApply').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var app_action = button.data('appaction');
		var app_id = button.data('appid');
		var app_name = button.data('appname');
		var modal = $(this);
		if (app_action === 'edit_app') {
			modal.find('.modal-title').text('修改申請單位');
			modal.find('button#eq_submit').text('修改');
		} else {
			modal.find('.modal-title').text('新增申請單位')
			modal.find('button#eq_submit').text('新增');
		}
		modal.find('input#eq_action').val(app_action);
		modal.find('input#eq_app_id').val(app_id);
		modal.find('input#eq_app_name').val(app_name);
	});

	$('#deleteApply').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var app_id = button.data('appid');
		var app_name = button.data('appname');
		var modal = $(this);
		modal.find('.modal-title').text('您真的要刪除申請單位 ' + app_name + ' 嗎？')
		modal.find('input#dapp_app_id').val(app_id);
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
