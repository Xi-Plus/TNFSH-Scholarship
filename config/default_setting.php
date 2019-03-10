<?php

$C["domain"] = 'https://example.com';
$C["path"] = '/scholarship';
$C["sitename"] = '獎學金公告';
$C["titlename"] = '獎學金公告';

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

$G["data_apply"] = [
	1 => "向註冊組申請",
	2 => "自行申請",
];

date_default_timezone_set("Asia/Taipei");

// require "func/check_login.php";
// session_start();

include __DIR__ . '/local_setting.php';

$G["db"] = new PDO('mysql:host=' . $C["DBhost"] . ';dbname=' . $C["DBname"] . ';charset=utf8', $C["DBuser"], $C["DBpass"]);
