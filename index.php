<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/data.php';

$data_offset = $_GET['offset'] ?? 0;
$date_limit = $_GET['limit'] ?? 10;
get_data($data_offset, $date_limit);
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require __DIR__ . '/resources/load_header.php';?>
    <title><?=$C["titlename"]?></title>
</head>

<body>
    <?php
	require __DIR__ . '/resources/header.php';
	?>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
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
                        </tr>
                        <?php
						foreach ($D['data'] as $data_id => $row) {
						?>
                        <tr <?=($row['date_end'] < date('Y-m-d') ? ' class="table-secondary"' : '')?>>
                            <td><?=$row['semester']?></td>
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
                        </tr>
                        <?php
						}
						?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php
require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>