<?php

if (!isset($_COOKIE[$C['cookiename']])) {
	$U['islogin'] = false;
} else {
	$sth = $G['db']->prepare('SELECT * FROM `login_session` WHERE `ls_cookie` = :ls_cookie');
	$sth->bindValue(":ls_cookie", $_COOKIE[$C['cookiename']]);
	$sth->execute();
	$cookie = $sth->fetch(PDO::FETCH_ASSOC);
	if ($cookie === false) {
		$U['islogin'] = false;
	} else {
		$sth = $G['db']->prepare('SELECT * FROM `admin` WHERE `adm_account` = :adm_account');
		$sth->bindValue(":adm_account", $cookie['ls_account']);
		$sth->execute();
		$U['data'] = $sth->fetch(PDO::FETCH_ASSOC);
		$U['islogin'] = true;
	}
}
