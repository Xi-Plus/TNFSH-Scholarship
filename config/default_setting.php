<?php

$C = [];
$D = [];
$G = [];
$U = [];

$C["domain"] = 'https://example.com';
$C["path"] = '/scholarship';
$C["sitename"] = '獎學金公告';
$C["titlename"] = '獎學金公告';

$C['cookiename'] = 'scholarship';
$C['cookieexpire'] = 86400 * 30;

$C['webmail'] = '';

$C['pagelimitdefalut'] = 10;
$C['pagelimitmin'] = 1;
$C['pagelimitmax'] = 100;
$C['pagenext'] = 3;

$C["DBhost"] = 'localhost';
$C["DBuser"] = '';
$C["DBpass"] = '';
$C["DBname"] = 'scholarship';

$C["CAPTCHAuselogin"] = false;
$C["CAPTCHAusestudent"] = false;
$C["CAPTCHAsitekey"] = '';
$C["CAPTCHAsecretkey"] = '';

$C["FilenameReserved"] = '\/:*?"<>|';
$C["FilenamePattern"] = '[^';
foreach (str_split($C["FilenameReserved"]) as $char) {
	$C["FilenamePattern"] .= '\x' . sprintf("%x", ord($char));
}
$C["FilenamePattern"] .= '\x00-\x1f\x7f]+';
$C["FilenameTitle"] = "不可包含控制字元和以下字元: " . htmlentities(implode(" ", str_split($C["FilenameReserved"])));

date_default_timezone_set("Asia/Taipei");

// require "func/check_login.php";
// session_start();

include __DIR__ . '/local_setting.php';

$G["db"] = new PDO('mysql:host=' . $C["DBhost"] . ';dbname=' . $C["DBname"] . ';charset=utf8', $C["DBuser"], $C["DBpass"]);

require __DIR__ . '/../func/check_login.php';
