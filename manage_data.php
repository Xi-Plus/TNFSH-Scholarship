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
	if (isset($_POST['submitaction'])) {
		$action = $_POST['submitaction'];
	} else {
		$action = $_GET['action'];
	}
	if ($action === 'editandback') {
		$action = 'edit';
	} else if ($action === 'newandback') {
		$action = 'new';
	}
	if (!in_array($action, ['edit', 'new', 'delete'])) {
		$action = 'new';
	}
	$data_id = $_GET['data_id'] ?? 0;
	$actionname = ['edit' => '修改', 'new' => '新增', 'delete' => '刪除'][$action];
	$back = in_array($_POST['submitaction'], ['editandback', 'newandback', 'delete']);
	$D['apply'] = get_apply();
	$D['data'] = get_data($data_id);
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['submitaction']) && $action === 'edit') {
	$sth = $G["db"]->prepare("UPDATE `data` SET
		`data_semester` = :semester,
		`data_name` = :name,
		`data_apply` = :apply,
		`data_date_start` = :date_start,
		`data_date_end` = :date_end,
		`data_money` = :money,
		`data_quota` = :quota,
		`data_note` = :note
		WHERE `data_id` = :data_id");
	$sth->bindValue(":semester", $_POST['semester']);
	$sth->bindValue(":name", $_POST['name']);
	$sth->bindValue(":apply", $_POST['apply']);
	$sth->bindValue(":date_start", $_POST['date_start']);
	$sth->bindValue(":date_end", $_POST['date_end']);
	$sth->bindValue(":money", $_POST['money']);
	$sth->bindValue(":quota", $_POST['quota']);
	$sth->bindValue(":note", $_POST['note']);
	$sth->bindValue(":data_id", $data_id);
	$sth->execute();

	$sth = $G["db"]->prepare("DELETE FROM `data_qualifications` WHERE `dq_data` = :dq_data");
	$sth->bindValue(":dq_data", $data_id);
	$sth->execute();

	foreach ($_POST['qualifications'] as $qualification) {
		$qualification_args = $_POST['qualifications_args'][$qualification] ?? [];

		$sth = $G["db"]->prepare("INSERT INTO `data_qualifications` (`dq_data`, `dq_qualification`, `dq_args`) VALUES (:dq_data, :dq_qualification, :dq_args)");
		$sth->bindValue(":dq_data", $data_id);
		$sth->bindValue(":dq_qualification", $qualification);
		$sth->bindValue(":dq_args", json_encode($qualification_args));
		$sth->execute();
	}

	$sth = $G["db"]->prepare("DELETE FROM `data_attachments` WHERE `da_data` = :da_data");
	$sth->bindValue(":da_data", $data_id);
	$sth->execute();

	foreach ($_POST['attachments'] as $attachment) {
		$sth = $G["db"]->prepare("INSERT INTO `data_attachments` (`da_data`, `da_attachment`) VALUES (:da_data, :da_attachment)");
		$sth->bindValue(":da_data", $data_id);
		$sth->bindValue(":da_attachment", $attachment);
		$sth->execute();
	}

	add_alert('資料已保存' . ($back ? '，正在返回列表' : ''), 'success');
	$D['data'] = get_data($data_id);
}

if ($showform && isset($_POST['submitaction']) && $action === 'new') {
	$sth = $G["db"]->prepare("INSERT INTO `data` (`data_semester`, `data_name`, `data_apply`, `data_date_start`, `data_date_end`, `data_money`, `data_quota`, `data_note`) VALUES (:semester, :name, :apply, :date_start, :date_end, :money, :quota, :note)");
	$sth->bindValue(":semester", $_POST['semester']);
	$sth->bindValue(":name", $_POST['name']);
	$sth->bindValue(":apply", $_POST['apply']);
	$sth->bindValue(":date_start", $_POST['date_start']);
	$sth->bindValue(":date_end", $_POST['date_end']);
	$sth->bindValue(":money", $_POST['money']);
	$sth->bindValue(":quota", $_POST['quota']);
	$sth->bindValue(":note", $_POST['note']);
	$sth->execute();

	$data_id = $G["db"]->lastInsertId();

	foreach ($_POST['qualifications'] as $qualification) {
		$qualification_args = $_POST['qualifications_args'][$qualification] ?? [];

		$sth = $G["db"]->prepare("INSERT INTO `data_qualifications` (`dq_data`, `dq_qualification`, `dq_args`) VALUES (:dq_data, :dq_qualification, :dq_args)");
		$sth->bindValue(":dq_data", $data_id);
		$sth->bindValue(":dq_qualification", $qualification);
		$sth->bindValue(":dq_args", json_encode($qualification_args));
		$sth->execute();
	}

	foreach ($_POST['attachments'] as $attachment) {
		$sth = $G["db"]->prepare("INSERT INTO `data_attachments` (`da_data`, `da_attachment`) VALUES (:da_data, :da_attachment)");
		$sth->bindValue(":da_data", $data_id);
		$sth->bindValue(":da_attachment", $attachment);
		$sth->execute();
	}

	add_alert('資料已新增' . ($back ? '，正在返回列表' : ''), 'success');
	$D['data'] = get_data($data_id);
}

if ($showform && isset($_POST['submitaction']) && $action === 'delete') {
	# Cascade delete `data_qualifications` and `data_attachments`
	$sth = $G["db"]->prepare("DELETE FROM `data` WHERE `data_id` = :data_id");
	$sth->bindValue(":data_id", $data_id);
	$sth->execute();

	add_alert('資料已刪除，正在返回列表', 'success');
	$showform = false;
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/<?=$actionname?>資料</title>
	<style>
	.qualification-args {
		width: 100px;
	}
	</style>
</head>

<body>

<?php
require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
	?>
	<div class="container">
		<h2><?=$actionname?>資料</h2>
		<form action="" method="post">
			<input type="hidden" name="data_id" value="<?=$data_id?>">
			<div class="row">
				<label class="col-sm-2 form-control-label">學期</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="semester" value="<?=$D['data']['semester']?>">
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">獎學金名稱</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="name" value="<?=$D['data']['name']?>" required>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">申請資格</label>
				<div class="col-sm-10">
					<ul>
						<?php foreach ($D['qualifications'] as $qc_id => $qc) {?>
						<li>
							<?=htmlentities($qc['name'])?>
							<?php foreach ($qc['list'] as $qua_id => $qua) {?>
							<label>
								<input type="checkbox" name="qualifications[]" value="<?=$qua_id?>"
									<?=(in_array($qua_id, $D['data']['qualification_ids']) ? 'checked' : '')?>>
								<?=htmlentities(vsprintf($qua['qua_name'], array_fill(0, substr_count($qua['qua_name'], '%s'), '%s')))?>
								<?php for ($i = 1; $i <= substr_count($qua['qua_name'], '%s'); $i++) {?>
									<input class="qualification-args" type="text" name="qualifications_args[<?=$qua_id?>][]" placeholder="參數<?=$i?>" value="<?=$D['data']['qualification_args'][$qua_id][$i - 1]?>">
								<?php }?>
							</label>
							<?php }?>
							</li>
						<?php }?>
					</ul>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">申請辦法及申請表</label>
				<div class="col-sm-10">
					<ul id="attlist">
						<li style="display: none;">
							<input type="hidden" name="attachments[]">
							<a href="<?=$C["path"]?>/download/0"></a>
							<button type="button" class="btn btn-danger btn-sm" onclick="removefile(this)"><i class="fa fa-trash-o" aria-hidden="true"></i> 移除</button>
						</li>
						<?php foreach ($D['data']['attachments'] as $att) {?>
						<li>
							<input type="hidden" name="attachments[]" value="<?=$att['att_id']?>">
							<a href="<?=$C["path"]?>/download/<?=$att['att_id']?>"><?=$att['att_name']?></a>&nbsp;&nbsp;&nbsp;
							<button type="button" class="btn btn-danger btn-sm" onclick="removefile(this)">移除</button>
						</li>
						<?php }?>
						<li>
							<a href="<?=$C["path"]?>/manage/upload" target="_blank">新增檔案</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">申請單位</label>
				<div class="col-sm-10">
					<?php foreach ($D['apply'] as $app_id => $app_name) {?>
					<label>
						<input type="radio" name="apply" value="<?=$app_id?>"
							<?=($D['data']['apply_id'] == $app_id ? 'checked' : '')?> required><?=$app_name?>
					</label>
					<?php }?>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">申請期限</label>
				<div class="col-sm-10">
					<input class="form-control" type="date" name="date_start" value="<?=$D['data']['date_start']?>"
						required>
					<input class="form-control" type="date" name="date_end" value="<?=$D['data']['date_end']?>"
						required>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">獎學金金額</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="money" value="<?=$D['data']['money']?>" required>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">本校薦送名額</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" name="quota"
						value="<?=$D['data']['quota']?>">
				</div>
			</div>
			<div class="row">
				<label class="col-sm-2 form-control-label">備註</label>
				<div class="col-sm-10">
					<textarea class="form-control" name="note" rows="3"><?=$D['data']['note']?></textarea>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-10 offset-sm-2">
					<?php if ($action === 'edit') {?>
						<button type="submit" class="btn btn-success" name="submitaction" value="editandback">修改並回到列表</button>
						<button type="submit" class="btn btn-success" name="submitaction" value="edit">修改</button>
						<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteData">刪除</button>
					<?php } else {?>
						<button type="submit" class="btn btn-success" name="submitaction" value="newandback">新增並回到列表</button>
						<button type="submit" class="btn btn-success" name="submitaction" value="new">新增後繼續新增下一筆</button>
					<?php }?>
				</div>
			</div>
		</form>
	</div>


<div class="modal fade" id="deleteData" tabindex="-1" role="dialog" aria-labelledby="deleteDataLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteDataLabel">確認刪除資料？</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					此動作無法復原。
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-danger" name="submitaction" value="delete">刪除</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
function morefile(att_id, att_name) {
	var temp = attlist.children[0].cloneNode(true);
	temp.style.display = "";
	temp.children[0].value = att_id;
	temp.children[1].href = '<?=$C["path"]?>/download/' + att_id;
	temp.children[1].innerText = att_name;
	attlist.insertBefore(temp, attlist.lastElementChild)
}
function removefile(e) {
	e.parentElement.remove();
}
</script>

<?php
}

if ($back) {
	?>
	<script>
	setTimeout(() => {
		document.location = '<?=$C["path"]?>/';
	}, 1000);
	</script>
	<?php
}

require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>
