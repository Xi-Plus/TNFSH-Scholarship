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

$C["DBhost"] = 'localhost';
$C["DBuser"] = '';
$C["DBpass"] = '';
$C["DBname"] = 'scholarship';

$C["CAPTCHAuselogin"] = false;
$C["CAPTCHAusestudent"] = false;
$C["CAPTCHAsitekey"] = '';
$C["CAPTCHAsecretkey"] = '';

$C["PasswordSecurityEnabled"] = true;
$C["PasswordSecurityMinLength"] = 4;
$C["PasswordSecurityCannotBePopular"] = 100000;
$C["PasswordSecurityPopularPasswordFile"] = 'path_to_your_file.txt';
$G["PasswordSecurityText"] = [
	"password_match_username" => "密碼與帳號相同",
	"password_too_short" => "密碼太短，至少要" . $C["PasswordSecurityMinLength"] . "個字",
	"password_is_popular" => "密碼在常見密碼列表中前" . $C["PasswordSecurityCannotBePopular"] . "位",
];

$C["FilenameReserved"] = '\/:*?"<>|';
$C["FilenamePattern"] = '[^';
foreach (str_split($C["FilenameReserved"]) as $char) {
	$C["FilenamePattern"] .= '\x'.sprintf("%x", ord($char));
}
$C["FilenamePattern"] .= '\x00-\x1f\x7f]+';
$C["FilenameTitle"] = "不可包含控制字元和以下字元: ".htmlentities(implode(" ", str_split($C["FilenameReserved"])));

date_default_timezone_set("Asia/Taipei");

// require "func/check_login.php";
// session_start();

include __DIR__ . '/local_setting.php';

$G["db"] = new PDO('mysql:host=' . $C["DBhost"] . ';dbname=' . $C["DBname"] . ';charset=utf8', $C["DBuser"], $C["DBpass"]);

require __DIR__ . '/../func/check_login.php';
