<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/data.php';
require __DIR__ . '/func/page.php';

$data_offset = $_GET['offset'] ?? 0;
$data_limit = $_GET['limit'] ?? $C['pagelimitdefalut'];
if ($data_limit < $C['pagelimitmin'] || $data_limit > $C['pagelimitmax']) {
	$data_limit = $C['pagelimitdefalut'];
}
$search = $_GET['search'] ?? '';
$search = trim($search);

if ($search) {
	$searcharr = preg_replace('/\s+/', ' ', $search);
	$searcharr = explode(' ', $searcharr);
} else {
	$searcharr = [];
}

$all_count = count_data($searcharr);
$max_page = ceil($all_count / $data_limit);

$current_page = ceil($data_offset / $data_limit) + 1;

if ($current_page > $max_page) {
	$current_page = $max_page;
}

list_data($data_offset, $data_limit, $searcharr);
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
        <form class="form-inline" method="GET">
            <div class="form-group">
                <label class="my-1 mr-2" for="searchbox">搜尋名稱、金額、備註：</label>
                <input type="text" id="searchbox" name="search" class="form-control" placeholder="以空格隔開作OR搜尋" size="40" value="<?=htmlspecialchars($search)?>">
                <button type="submit" class="btn btn-primary">搜尋</button>
            </div>
        </form>
        共 <?=$all_count?> 筆結果
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>學期</th>
                            <th>獎學金名稱</th>
                            <th>申請資格</th>
                            <th>申請辦法及申請表</th>
                            <th>申請期限<br>本校薦送名額</th>
                            <th>獎學金金額</th>
                            <th>備註</th>
                            <?php if ($U["islogin"]) {?>
                            <th>管理</th>
                            <?php }?>
                        </tr>
                        <?php foreach ($D['data'] as $data_id => $row) {?>
                        <tr <?=($row['date_end'] < date('Y-m-d') ? ' class="table-secondary"' : '')?>>
                            <td><?=htmlentities($row['semester'])?></td>
                            <td><?=htmlentities($row['name'])?></td>
                            <td>
                                <ul>
								<?php foreach ($row['qualifications'] as $qua_id => $qualification) {?>
									<li><?=htmlentities(vsprintf($qualification, $row['qualification_args'][$qua_id]))?></li>
								<?php }?>
								<?php if (count($row['qualifications']) == 0) {?>
									<li>無</li>
								<?php }?>
                                </ul>
                            </td>
                            <td>
                                <ul>
                                    <?php foreach ($row['attachments'] as $attachment) {?>
									<li>
										<a href="<?=$C["path"]?>/download/<?=$attachment['id']?>"><?=$attachment['name']?></a>
									</li>
									<?php }?>
									<?php if (count($row['attachments']) == 0) {?>
									<li>無</li>
									<?php }?>
                                </ul>
                            </td>
                            <td>
                                <?=$row['apply']?><br>
                                <?=$row['date_start']?>~<?=$row['date_end']?>
                                <?php if ($row['quota'] !== '') {?>
                                    <br>名額：<?=htmlentities($row['quota'])?>
                                <?php }?>
                            </td>
                            <td><?=htmlentities($row['money'])?></td>
                            <td><?=nl2br(htmlentities($row['note']))?></td>
                            <?php if ($U["islogin"]) {?>
                            <td>
                                <a href="<?=$C["path"]?>/manage/data/edit/<?=$data_id?>">修改</a><br>
                                <a href="<?=$C["path"]?>/manage/data/new/<?=$data_id?>">複製</a>
                            </td>
                            <?php }?>
                        </tr>
                        <?php }?>
                    </table>
                </div>
            </div>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <li class="page-item <?=($current_page <= 1 ? 'disabled' : '')?>">
                    <a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=0&limit=<?=htmlspecialchars($data_limit)?>">第一頁</a>
                </li>
                <li class="page-item <?=($current_page <= 1 ? 'disabled' : '')?>">
                    <?php $offset = count_page($data_offset, $data_limit, -1, $all_count);?>
                    <a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=<?=htmlspecialchars($offset)?>&limit=<?=htmlspecialchars($data_limit)?>">上一頁</a>
                </li>
                <?php for ($diff = -$C['pagenext']; $diff <= -1; $diff++) {
                    $page = $current_page + $diff;
                    if ($page <= 0) {
                        continue;
                    }
                    $offset = count_page($data_offset, $data_limit, $diff, $all_count);
                    ?>
                    <li class="page-item"><a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=<?=htmlspecialchars($offset)?>&limit=<?=htmlspecialchars($data_limit)?>"><?=$page?></a></li>
                <?php }?>
                <li class="page-item active"><a class="page-link" href="#"><?=$current_page?></a></li>
                <?php for ($diff = 1; $diff <= $C['pagenext']; $diff++) {
                    $page = $current_page + $diff;
                    if ($page > $max_page) {
                        continue;
                    }
                    $offset = count_page($data_offset, $data_limit, $diff, $all_count);
                    ?>
                    <li class="page-item"><a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=<?=htmlspecialchars($offset)?>&limit=<?=htmlspecialchars($data_limit)?>"><?=$page?></a></li>
                <?php }?>
                <li class="page-item <?=($current_page >= $max_page ? 'disabled' : '')?>">
                    <?php $offset = count_page($data_offset, $data_limit, 1, $all_count);?>
                    <a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=<?=htmlspecialchars($offset)?>&limit=<?=htmlspecialchars($data_limit)?>">下一頁</a>
                </li>
                <li class="page-item <?=($current_page >= $max_page ? 'disabled' : '')?>">
                    <?php $offset = ($max_page - 1) * $data_limit;?>
                    <a class="page-link" href="?search=<?=htmlspecialchars($search)?>&offset=<?=htmlspecialchars($offset)?>&limit=<?=htmlspecialchars($data_limit)?>">最末頁</a>
                </li>
            </ul>
        </nav>
    </div>

<?php
require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
require __DIR__ . '/resources/gtag.php';
?>
</body>

</html>
