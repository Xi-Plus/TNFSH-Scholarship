<?php
require __DIR__ . '/config/default_setting.php';
require __DIR__ . '/func/alert.php';

$att_id = $_GET['att_id'] ?? "";
$sth = $G["db"]->prepare("SELECT * FROM `attachments` WHERE `att_id` = :att_id");
$sth->bindValue(':att_id', $att_id);
$sth->execute();
$att = $sth->fetch(PDO::FETCH_ASSOC);
if ($att !== false) {
	$att_path = __DIR__ . "/attachments/" . $att["att_id"];
	if (file_exists($att_path)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $att["att_name"] . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('X-Robots-Tag: noindex');
		header('Content-Length: ' . filesize($att_path));
		readfile($att_path);
		exit;
	} else {
		add_alert('檔案遺失');
	}
} else {
	add_alert('找不到檔案');
}
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<?php require __DIR__ . '/resources/load_header.php';?>
	<title><?=$C["titlename"]?>/下載檔案</title>
</head>

<body>

<?php
require __DIR__ . '/resources/header.php';
show_alert();

require __DIR__ . '/resources/footer.php';
require __DIR__ . '/resources/load_footer.php';
?>
</body>

</html>
