<?php

function get_data($offset, $limit) {
	global $C, $D, $G;

	$D['data'] = [];

	$sth = $G["db"]->prepare('SELECT * FROM `data` ORDER BY `data_date_end` DESC LIMIT :offset, :limit');
	$sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
	$sth->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
	$sth->execute();
	$all = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($all as $row) {
		$D['data'][$row['data_id']] = [];
		$D['data'][$row['data_id']]['semester'] = $row['data_semester'];
		$D['data'][$row['data_id']]['name'] = $row['data_name'];
		$D['data'][$row['data_id']]['apply'] = $row['data_apply'];
		$D['data'][$row['data_id']]['date_start'] = $row['data_date_start'];
		$D['data'][$row['data_id']]['date_end'] = $row['data_date_end'];
		$D['data'][$row['data_id']]['money'] = $row['data_money'];
		$D['data'][$row['data_id']]['quota'] = $row['data_quota'];
		$D['data'][$row['data_id']]['qualifications'] = [];
		$D['data'][$row['data_id']]['attachments'] = [];
	}

	// data_qualifications
	$sth = $G["db"]->prepare('SELECT `dq_data`, `qua_name` FROM (
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
		$D['data'][$row['dq_data']]['qualifications'][] = $row['qua_name'];
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
