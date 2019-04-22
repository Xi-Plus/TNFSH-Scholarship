<?php

function list_data($offset, $limit) {
	global $C, $D, $G;

	$apply = get_apply();

	$D['data'] = [];

	$sth = $G["db"]->prepare('SELECT * FROM `data` ORDER BY `data_date_end` DESC, `data_id` DESC LIMIT :offset, :limit');
	$sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
	$sth->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$D['data'][$row['data_id']] = [];
		$D['data'][$row['data_id']]['semester'] = $row['data_semester'];
		$D['data'][$row['data_id']]['name'] = $row['data_name'];
		$D['data'][$row['data_id']]['apply'] = $apply[$row['data_apply']];
		$D['data'][$row['data_id']]['apply_id'] = $row['data_apply'];
		$D['data'][$row['data_id']]['date_start'] = $row['data_date_start'];
		$D['data'][$row['data_id']]['date_end'] = $row['data_date_end'];
		$D['data'][$row['data_id']]['money'] = $row['data_money'];
		$D['data'][$row['data_id']]['quota'] = $row['data_quota'];
		$D['data'][$row['data_id']]['note'] = $row['data_note'];
		$D['data'][$row['data_id']]['qualifications'] = [];
		$D['data'][$row['data_id']]['qualification_args'] = [];
		$D['data'][$row['data_id']]['attachments'] = [];
	}

	// data_qualifications
	$sth = $G["db"]->prepare('SELECT `dq_data`, `dq_args`, `qua_id`, `qua_name` FROM (
		SELECT * FROM `data_qualifications` WHERE `dq_data` IN (
			' . implode(',', array_keys($D['data'])) . '
		)
	) `data_qualifications`
	LEFT JOIN `qualifications` ON `dq_qualification` = `qua_id`');
	$sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
	$sth->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$D['data'][$row['dq_data']]['qualifications'][$row['qua_id']] = $row['qua_name'];
		$D['data'][$row['dq_data']]['qualification_args'][$row['qua_id']] = json_decode($row['dq_args']);
	}

	// data_attachments
	$sth = $G["db"]->prepare('SELECT `da_data`, `da_attachment`, `att_name` FROM (
		SELECT * FROM `data_attachments` WHERE `da_data` IN (
			' . implode(',', array_keys($D['data'])) . '
		)
	) `data_attachments`
	LEFT JOIN `attachments` ON `da_attachment` = `att_id`');
	$sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
	$sth->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$D['data'][$row['da_data']]['attachments'][] = ['id' => $row['da_attachment'], 'name' => $row['att_name']];
	}
}

function get_data($data_id) {
	global $C, $G;

	$apply = get_apply();

	$result = [
		'semester' => '',
		'name' => '',
		'apply' => 1,
		'date_start' => '',
		'date_end' => '',
		'money' => '',
		'quota' => '',
		'note' => '',
		'qualifications' => [],
		'qualification_ids' => [],
		'attachments' => [],
	];

	$sth = $G["db"]->prepare('SELECT * FROM `data` WHERE `data_id` = :data_id');
	$sth->bindValue(':data_id', (int) $data_id, PDO::PARAM_INT);
	$sth->execute();
	$row = $sth->fetch(PDO::FETCH_ASSOC);

	if ($row === false) {
		return $result;
	}

	$result['semester'] = $row['data_semester'];
	$result['name'] = $row['data_name'];
	$result['apply_id'] = $row['data_apply'];
	$result['apply'] = $apply[$row['data_apply']];
	$result['date_start'] = $row['data_date_start'];
	$result['date_end'] = $row['data_date_end'];
	$result['money'] = $row['data_money'];
	$result['quota'] = $row['data_quota'];
	$result['note'] = $row['data_note'];
	$result['qualifications'] = [];
	$result['qualification_ids'] = [];
	$result['qualification_args'] = [];
	$result['attachments'] = [];

	// data_qualifications
	$sth = $G["db"]->prepare('SELECT `dq_data`, `dq_args`,  `qua_id`, `qua_name` FROM (
		SELECT * FROM `data_qualifications` WHERE `dq_data` = :data_id
	) `data_qualifications`
	LEFT JOIN `qualifications` ON `dq_qualification` = `qua_id`');
	$sth->bindValue(':data_id', (int) $data_id, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result['qualifications'][$row['qua_id']] = $row['qua_name'];
		$result['qualification_ids'][] = $row['qua_id'];
		$result['qualification_args'][$row['qua_id']] = json_decode($row['dq_args']);
	}

	// data_attachments
	$sth = $G["db"]->prepare('SELECT `da_data`, `da_attachment`, `att_name` FROM (
		SELECT * FROM `data_attachments` WHERE `da_data` = :data_id
	) `data_attachments`
	LEFT JOIN `attachments` ON `da_attachment` = `att_id`');
	$sth->bindValue(':data_id', (int) $data_id, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result['attachments'][] = ['att_id' => $row['da_attachment'], 'att_name' => $row['att_name']];
	}

	return $result;
}

function get_qualifications() {
	global $C, $G;

	$result = [];

	$sth = $G["db"]->prepare('SELECT * FROM `qualification_category` ORDER BY `qc_id`');
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result[$row['qc_id']] = [
			'name' => $row['qc_name'],
			'list' => [],
		];
	}

	$sth = $G["db"]->prepare('SELECT * FROM `qualifications` ORDER BY `qua_id` ASC');
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result[$row['qua_category']]['list'][$row['qua_id']] = $row;
	}

	return $result;
}

function get_qcid_by_quaid($qua_id) {
	global $C, $G;

	$sth = $G["db"]->prepare('SELECT * FROM `qualifications` WHERE `qua_id` = :qua_id');
	$sth->bindValue(':qua_id', (int) $qua_id, PDO::PARAM_INT);
	$sth->execute();
	$qua = $sth->fetch(PDO::FETCH_ASSOC);
	return $qua['qua_category'];
}

function get_apply() {
	global $C, $G;

	$result = [];

	$sth = $G["db"]->prepare('SELECT * FROM `apply` ORDER BY `app_id` ASC');
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result[$row['app_id']] = $row['app_name'];
	}

	return $result;
}

function get_account() {
	global $C, $G;

	$result = [];

	$sth = $G["db"]->prepare('SELECT * FROM `admin` ORDER BY `adm_account`');
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$result[$row['adm_account']] = $row;
	}

	return $result;
}
