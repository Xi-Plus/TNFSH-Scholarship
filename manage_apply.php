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
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'new_qc') {
	$sth = $G["db"]->prepare("INSERT INTO `qualification_category` (`qc_name`) VALUES (:qc_name)");
	$sth->bindValue(":qc_name", $_POST['qc_name']);
	$sth->execute();

	add_alert('已新增 ' . $_POST['qc_name'], 'success');
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'edit_qc') {
	$sth = $G["db"]->prepare("UPDATE `qualification_category` SET `qc_name` = :qc_name WHERE `qc_id` = :qc_id");
	$sth->bindValue(":qc_name", $_POST['qc_name']);
	$sth->bindValue(":qc_id", $_POST['qc_id']);
	$sth->execute();

	add_alert('已將 ' . $D['qualifications'][$_POST['qc_id']]['name'] . ' 改為 ' . $_POST['qc_name'], 'success');
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'delete_qc') {
	$sth = $G["db"]->prepare("DELETE FROM `qualification_category` WHERE `qc_id` = :qc_id");
	$sth->bindValue(":qc_id", $_POST['qc_id']);
	$res = $sth->execute();

	$qc_name = $D['qualifications'][$_POST['qc_id']]['name'];

	if ($res === false) {
		if ($sth->errorCode() == "23000") {
			add_alert('刪除 ' . $qc_name . ' 失敗，仍有申請資格使用此分類。', 'danger');
		} else {
			add_alert('刪除 ' . $qc_name . ' 失敗。', 'danger');
		}
	} else {
		add_alert('已刪除 ' . $qc_name, 'success');
		$D['qualifications'] = get_qualifications();
	}
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'new_qua') {
	$sth = $G["db"]->prepare("INSERT INTO `qualifications` (`qua_category`, `qua_name`) VALUES (:qua_category, :qua_name)");
	$sth->bindValue(":qua_category", $_POST['qua_category']);
	$sth->bindValue(":qua_name", $_POST['qua_name']);
	$sth->execute();

	add_alert(sprintf('已新增 %s-%s', $D['qualifications'][$_POST['qua_category']]['name'], $_POST['qua_name']), 'success');
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'edit_qua') {
	$old_qc_id = get_qcid_by_quaid($_POST['qua_id']);

	$sth = $G["db"]->prepare("UPDATE `qualifications` SET `qua_category` = :qua_category, `qua_name` = :qua_name WHERE `qua_id` = :qua_id");
	$sth->bindValue(":qua_category", $_POST['qua_category']);
	$sth->bindValue(":qua_name", $_POST['qua_name']);
	$sth->bindValue(":qua_id", $_POST['qua_id']);
	$sth->execute();

	add_alert(sprintf('已將 %s-%s 修改為 %s-%s',
		$D['qualifications'][$old_qc_id]['name'],
		$D['qualifications'][$old_qc_id]['list'][$_POST['qua_id']]['qua_name'],
		$D['qualifications'][$_POST['qua_category']]['name'],
		$_POST['qua_name']
	), 'success');
	$D['qualifications'] = get_qualifications();
}

if ($showform && isset($_POST['action']) && $_POST['action'] === 'delete_qua') {
	$qc_id = get_qcid_by_quaid($_POST['qua_id']);

	$sth = $G["db"]->prepare("DELETE FROM `qualifications` WHERE `qua_id` = :qua_id");
	$sth->bindValue(":qua_id", $_POST['qua_id']);
	$res = $sth->execute();

	$qua_name = sprintf('%s-%s',
		$D['qualifications'][$qc_id]['name'],
		$D['qualifications'][$qc_id]['list'][$_POST['qua_id']]['qua_name']
	);

	if ($res === false) {
		if ($sth->errorCode() == "23000") {
			add_alert('刪除 ' . $qua_name . ' 失敗，仍有獎學金公告使用此申請資格。', 'danger');
		} else {
			add_alert('刪除 ' . $qua_name . ' 失敗。', 'danger');
		}
	} else {
		add_alert('已刪除 ' . $qua_name, 'success');
		$D['qualifications'] = get_qualifications();
	}
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/管理申請資格</title>
</head>

<body>
<?php

require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
	?>
<div class="container">
	<h2>管理申請資格</h2>
	<h3>新增</h3>
	<button type="button" class="btn btn-success" data-toggle="modal" data-target="#editCategory" data-qcaction="new_qc" data-qcid="0" data-qcname="">
		<i class="fa fa-plus"></i> 新增分類</button>
	<button type="button" class="btn btn-success" data-toggle="modal" data-target="#editQualification" data-quaaction="new_qua" data-quaid="<?=$qua_id?>" data-qcid="0" data-quaname="">
		<i class="fa fa-plus"></i>　新增資格</button>
	<h3>列表</h3>
	<div class="table-responsive">
		<table class="table">
			<tr>
				<th>分類</th>
				<th>資格</th>
			</tr>
			<?php foreach ($D['qualifications'] as $qc_id => $qc) {?>
			<tr>
				<td>
				<?=htmlentities($qc['name']);?>
					<button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#editCategory" data-qcaction="edit_qc" data-qcid="<?=$qc_id?>" data-qcname="<?=htmlentities($qc['name'])?>"><i class="fas fa-pencil-alt"></i></button>
					<?php if (count($qc['list']) == 0) {?>
						<button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteCategory" data-qcid="<?=$qc_id?>" data-qcname="<?=htmlentities($qc['name'])?>"><i class="fas fa-trash"></i></button>
					<?php }?>
				</td>
				<td>
					<ul>
					<?php foreach ($qc['list'] as $qua_id => $qua) {?>
						<li>
							<?=htmlentities($qua['qua_name'])?>
							<button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#editQualification" data-quaaction="edit_qua" data-quaid="<?=$qua_id?>" data-qcid="<?=$qc_id?>" data-quaname="<?=htmlentities($qua['qua_name'])?>"><i class="fas fa-pencil-alt"></i></button>
							<button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteQualification" data-quaid="<?=$qua_id?>" data-quaname="<?=htmlentities($qc['name'])?>-<?=htmlentities($qua['qua_name'])?>"><i class="fas fa-trash"></i></button>
						</li>
					<?php }?>
					</ul>
				</td>
			</tr>
			<?php }?>
		</table>
	</div>
</div>

<div class="modal fade" id="editCategory" tabindex="-1" role="dialog" aria-labelledby="editCategoryLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" id="eqc_action" type="hidden">
				<input name="qc_id" id="eqc_qc_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="editCategoryLabel">修改分類</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="edit-new-qc-name" class="col-form-label">新名稱</label>
						<input type="text" class="form-control" name="qc_name" id="eqc_qc_name" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
					<button type="submit" class="btn btn-primary" id="eqc_submit">修改</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal fade" id="deleteCategory" tabindex="-1" role="dialog" aria-labelledby="deleteCategoryLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" type="hidden" value="delete_qc">
				<input name="qc_id" id="dqc_qc_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteCategoryLabel">刪除分類</h5>
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

<div class="modal fade" id="editQualification" tabindex="-1" role="dialog" aria-labelledby="editQualificationLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" id="eq_action" type="hidden">
				<input name="qua_id" id="eq_qua_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="editQualificationLabel">修改申請資格</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="edit-new-qc-name" class="col-form-label">分類</label>
						<select class="form-control" name="qua_category" id="eq_qua_category" required>
						<?php foreach ($D['qualifications'] as $qc_id => $qc) {?>
							<option value="<?=$qc_id?>"><?=$qc['name']?></option>
						<?php }?>
						</select>
					</div>
					<div class="form-group">
						<label for="edit-new-qc-name" class="col-form-label">資格名稱</label>
						<input type="text" class="form-control" name="qua_name" id="eq_qua_name" required>
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

<div class="modal fade" id="deleteQualification" tabindex="-1" role="dialog" aria-labelledby="deleteQualificationLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form method="POST">
				<input name="action" type="hidden" value="delete_qua">
				<input name="qua_id" id="dqua_qua_id" type="hidden">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteQualificationLabel">刪除申請資格</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					提示：若仍有獎學金公告使用此申請資格，將無法刪除。
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
	$('#editCategory').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var qc_action = button.data('qcaction');
		var qc_id = button.data('qcid');
		var qc_name = button.data('qcname');
		var modal = $(this);
		if (qc_action === 'edit_qc') {
			modal.find('.modal-title').text('修改分類');
			modal.find('button#eqc_submit').text('修改');
		} else {
			modal.find('.modal-title').text('新增分類')
			modal.find('button#eqc_submit').text('新增');
		}
		modal.find('input#eqc_action').val(qc_action);
		modal.find('input#eqc_qc_id').val(qc_id);
		modal.find('input#eqc_qc_name').val(qc_name);
	});

	$('#deleteCategory').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var qc_id = button.data('qcid');
		var qc_name = button.data('qcname');
		var modal = $(this);
		modal.find('.modal-title').text('您真的要刪除分類 ' + qc_name + ' 嗎？')
		modal.find('input#dqc_qc_id').val(qc_id);
	});

	$('#editQualification').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var qua_action = button.data('quaaction');
		var qua_id = button.data('quaid');
		var qua_category = button.data('qcid');
		var qua_name = button.data('quaname');
		var modal = $(this);
		if (qua_action === 'edit_qua') {
			modal.find('.modal-title').text('修改申請資格');
			modal.find('button#eq_submit').text('修改');
		} else {
			modal.find('.modal-title').text('新增申請資格')
			modal.find('button#eq_submit').text('新增');
		}
		modal.find('input#eq_action').val(qua_action);
		modal.find('input#eq_qua_id').val(qua_id);
		modal.find('select#eq_qua_category').val(qua_category);
		modal.find('input#eq_qua_name').val(qua_name);
	});

	$('#deleteQualification').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var qua_id = button.data('quaid');
		var qua_name = button.data('quaname');
		var modal = $(this);
		modal.find('.modal-title').text('您真的要刪除申請資格 ' + qua_name + ' 嗎？')
		modal.find('input#dqua_qua_id').val(qua_id);
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
