<?php

function count_page($data_offset, $data_limit, $diff, $all_count) {
	$offset = $data_offset + $diff * $data_limit;
	if ($offset < 0) {
		$offset = 0;
	}
	return $offset;
}
