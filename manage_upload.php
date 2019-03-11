<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/alert.php';

$showform = true;
if (!$U["islogin"]) {
	add_alert('此功能需要驗證帳號，請<a href="' . $C["path"] . '/login/">登入</a>');
	$showform = false;
}

$is_ok = false;
$att_id = null;
$att_name = null;
if ($showform && isset($_POST["filename"]) && isset($_FILES["file"])) {
	if ($_FILES["file"]["error"][0] == 4) {
		add_alert('沒有檔案被上傳');
	} else if ($_FILES["file"]["error"][0] == 0) {
		$att_id = md5_file($_FILES["file"]["tmp_name"]);
		$att_name = ($_POST["filename"] != "" ? $_POST["filename"] : $_FILES["file"]["name"][0]);

		$sth = $G["db"]->prepare("SELECT * FROM `attachments` WHERE `att_id` = :att_id");
		$sth->bindValue(":att_id", $att_id);
		$sth->execute();
		$att = $sth->fetch(PDO::FETCH_ASSOC);
		if ($att === false) {
			$is_moved = move_uploaded_file($_FILES["file"]["tmp_name"], __DIR__ . "/attachments/" . $att_id);
			if (!$is_moved) {
				add_alert('檔案移動失敗');
			} else {
				$sth = $G["db"]->prepare("INSERT INTO `attachments` (`att_id`, `att_name`) VALUES (:att_id, :att_name)");
				$sth->bindValue(":att_id", $att_id);
				$sth->bindValue(":att_name", $att_name);
				$sth->execute();
				add_alert('上傳成功，5秒後返回上一頁，<a href="#" onclick=backpage();>立刻返回</a>。', 'success');
				$is_ok = true;
				$showform = false;
			}
		} else {
			add_alert('發現舊檔案，5秒後返回上一頁，<a href="#" onclick=backpage();>立刻返回</a>。', 'success');
			$att_name = $att['att_name'];
			$is_ok = true;
			$showform = false;
		}
	} else {
		add_alert('上傳失敗');
	}
}

if ($showform) {
	$sth = $G["db"]->prepare("SELECT * FROM `attachments` ORDER BY `att_time` DESC LIMIT 10");
	$sth->execute();
	$D['recentatt'] = $sth->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/上傳檔案</title>
</head>

<body>

<?php
require __DIR__ . '/resources/header.php';
show_alert();
if ($showform) {
	?>
	<div class="container">
		<h2>上傳檔案</h2>
		<form action="" method="post" enctype="multipart/form-data">
			<div class="form-group row">
				<label class="col-sm-2 col-form-label" for="file">選擇檔案：</label>
				<div class="col-sm-10">
					<input class="form-control-file" type="file" id="file" name="file" class="form-control-file"
						onchange="getfilename(this)" required>
					<label>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-2 col-form-label" for="filename">檔案名稱：</label>
				<div class="col-sm-10">
					<input class="form-control" type="text" id="filename" name="filename" size="30"
						pattern="<?=$C["FilenamePattern"]?>" title="<?=$C["FilenameTitle"]?>" required>
				</div>
			</div>
			<button class="btn btn-success" type="submit" name="action" value="upload"><i class="fa fa-upload"
					aria-hidden="true"></i> 上傳</button>
		</form>
		<div style="height: 20px;"></div>
		或是...
		<h3>選擇最近上傳的檔案</h3>
		<ul>
		<?php foreach ($D['recentatt'] as $att) {?>
			<li>
				<?=$att['att_time']?> <a href="#" onclick="backpage('<?=$att['att_id']?>', '<?=htmlentities($att['att_name'])?>')"><?=$att['att_name']?></a>
			</li>
		<?php }?>
		</ul>
	</div>

	<script type="text/javascript">
	function getfilename(e) {
		filename.value = e.files[0].name;
	}
	function backpage(att_id, att_name) {
		window.opener.morefile(att_id, att_name);
		window.close();
	}
	</script>
	<?php

}
if ($is_ok) {
	?>
	<script>
	setTimeout(() => {
		backpage('<?=$att_id?>', '<?=htmlentities($att_name)?>');
	}, 5000);
	</script>
	<?php
}
require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>
