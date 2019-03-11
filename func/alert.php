<?php

function add_alert($content, $type = "danger") {
	global $D;

	if (!isset($D['alert'])) {
		$D['alert'] = [];
	}

	ob_start();
	?>
	<div class="alert alert-<?=$type?> alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
				aria-hidden="true">&times;</span></button>
		<?=$content?>
	</div>
	<?php

	$D['alert'][] = ob_get_contents();
	ob_end_clean();
}

function show_alert() {
	global $D;

	if (isset($D['alert'])) {
		foreach ($D['alert'] as $alert) {
			echo $alert;
		}
	}
}
