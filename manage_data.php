<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/data.php';

$data_offset = $_GET['offset'] ?? 0;
$date_limit = $_GET['limit'] ?? 10;
get_data($data_offset, $date_limit);

$showform = true;
if (!$U["islogin"]) {
	?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    此功能需要驗證帳號，請<a href="<?=$C["path"]?>/login/">登入</a>
</div>
<?php
$showform = false;
	writelog(sprintf("[manage_studentinput] %s view no premission.", $U["ip"]));
}

function write_student_input($data) {
	$handle = fopen(__DIR__ . "/data/student-input.csv", "w");
	if ($handle === false) {
		writelog(sprintf("[manage_studentinput] %s %s read student.csv failed.", $U["data"]["account"], $U["ip"]));
		exit("取得student-input.csv錯誤");
	}
	foreach ($data as $row) {
		unset($row['index']);
		fputcsv($handle, $row);
	}
	fclose($handle);
}

if ($showform && (isset($_POST["moveup"]) || isset($_POST["movedown"]))) {
	$key = $_POST["moveup"] ?? $_POST["movedown"];
	$index = array_search($key, array_keys($D['student_input']));
	$index += (isset($_POST["moveup"]) ? -1 : 1);
	if ($index < 0 || $index >= count($D['student_input'])) {
		?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    無法執行此移動
</div>
<?php
} else {
		$otherkey = array_keys($D['student_input'])[$index];
		list($D['student_input'][$key]['index'], $D['student_input'][$otherkey]['index']) = [$D['student_input'][$otherkey]['index'], $D['student_input'][$key]['index']];
		function cmp($a, $b) {
			if ($a['index'] == $b['index']) {
				return 0;
			}
			return ($a['index'] < $b['index'] ? -1 : 1);
		}
		uasort($D['student_input'], 'cmp');
		write_student_input($D['student_input']);
		require __DIR__ . "/func/student_input.php";
	}
}

if ($showform && isset($_POST["delete"])) {
	writelog(sprintf("[manage_studentinput] %s %s delete %s successed.", $U["data"]["account"], $U["ip"], $_POST["delete"]));
	?>
<div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    已刪除 <?=$_POST["delete"]?>
</div>
<?php
unset($D['student_input'][$_POST["delete"]]);
	write_student_input($D['student_input']);
	require __DIR__ . "/func/student_input.php";
}

if ($showform && isset($_POST["new"])) {
	if (isset($D['student_input'][$_POST["column"]])) {
		?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    該欄位已存在
</div>
<?php
writelog(sprintf("[manage_studentinput] %s %s new %s failed. dup.", $U["data"]["account"], $U["ip"], $_POST["column"]));
	} else {
		$D['student_input'][] = [$_POST["type"], $_POST["column"], $_POST["text"]];
		write_student_input($D['student_input']);
		require __DIR__ . "/func/student_input.php";
		?>
<div class="alert alert-success alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    已新增 <?=$_POST["column"]?>
</div>
<?php
writelog(sprintf("[manage_studentinput] %s %s new %s successed.", $U["data"]["account"], $U["ip"], $_POST["column"]));
	}
}

if ($showform && in_array('student_input_has_authentication_column', $G['safety_check_student'])) {
	?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    設定錯誤：沒有任何供 驗證用 欄位，基於安全性下載功能已自動停用
</div>
<?php
}

if ($showform && in_array('student_input_authentication_exist_in_data', $G['safety_check_student'])) {
	foreach ($D['student_input'] as $row) {
		if (isset($row['notexist'])) {
			?>
<div class="alert alert-danger alert-dismissible" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
    設定錯誤：欄位 <?=$row[1]?> 不存在於學生資料
</div>
<?php
}
	}
}

?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require __DIR__ . '/resources/load_header.php';?>
    <title><?=$C["titlename"]?>/管理資料</title>
</head>

<body>
    <?php
	require __DIR__ . '/resources/header.php';
	if ($showform) {
	?>
    <div class="container-fluid">
        <h2>管理資料</h2>
        <form action="" method="post">
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>學期</th>
                        <th>獎學金名稱</th>
                        <th>申請資格</th>
                        <th>申請辦法及申請表</th>
                        <th>申請期限</th>
                        <th>獎學金金額</th>
                        <th>本校薦送名額</th>
                        <th>管理</th>
                    </tr>
                    <?php
					foreach ($D['data'] as $data_id => $row) {
					?>
                    <tr <?=($row['date_end'] < date('Y-m-d') ? ' class="table-secondary"' : '')?>>
                        <td><a class="anchor" id="<?=$data_id?>"></a><?=$row['semester']?></td>
                        <td><?=$data_id?> <?=$row['name']?></td>
                        <td>
                            <ul>
                                <?php
									foreach ($row['qualifications'] as $qualification) {
										?><li><?=$qualification?></li><?php
									}
									if (count($row['qualifications']) == 0) {
										?><li>無</li><?php
									}
									?>
                            </ul>
                        </td>
                        <td>
                            <ul>
                                <?php
									foreach ($row['attachments'] as $attachment) {
										?><li><?=$attachment['id']?> <?=$attachment['name']?></li><?php
									}
									if (count($row['attachments']) == 0) {
										?><li>無</li><?php
									}
									?>
                            </ul>
                        </td>
                        <td>
                            <?=$G["data_apply"][$row['apply']]?><br>
                            <?=$row['date_start']?>~<?=$row['date_end']?>
                        </td>
                        <td><?=$row['money']?></td>
                        <td><?=$row['quota']?></td>
                        <td>
                            <button type="submit" name="edit" value="<?=$data_id?>"
                                class="btn btn-info btn-sm">修改</button>
                        </td>
                    </tr>
                    <?php
					}
					?>
                </table>
            </div>
        </form>
        <h3>新增</h3>
        <form action="" method="post">
            <div class="row">
                <label class="col-sm-2 form-control-label">類型</label>
                <div class="col-sm-10">
                    <select class="form-control" name="type">
                        <?php
						foreach ($G["input_type"] as $key => $value) {
						?>
                        <option value="<?=$key?>"><?=$value?></option>
                        <?php
						}
						?>
                    </select>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 form-control-label">欄位</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="column" required>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 form-control-label">顯示文字</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="text" required>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-success" name="new"><i class="fa fa-plus"
                            aria-hidden="true"></i> 新增</button>
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