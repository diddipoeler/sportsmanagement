<script type="text/javascript" language="JavaScript">
	hide<?php echo $params->get( 'moduleclass_sfx' ); ?>=1;
	function ticker<?php echo $params->get( 'moduleclass_sfx' ); ?>() {
	    if(hide<?php echo $params->get( 'moduleclass_sfx' ); ?>><?php echo $results; ?>) {
	    	var id_hide = (hide<?php echo $params->get( 'moduleclass_sfx' ) ?>-1)+'<?php echo $params->get( 'moduleclass_sfx' ); ?>';
			if(document.getElementById(id_hide)) {
				document.getElementById(id_hide).style.display="none";
			}
			hide<?php echo $params->get( 'moduleclass_sfx' ) ?>=1;
	    }
		var id_hide = (hide<?php echo $params->get( 'moduleclass_sfx' ) ?>-1)+'<?php echo $params->get( 'moduleclass_sfx' ); ?>';
		var id_show = (hide<?php echo $params->get( 'moduleclass_sfx' ) ?>)+'<?php echo $params->get( 'moduleclass_sfx' ); ?>';
		if(document.getElementById(id_hide)) {
			document.getElementById(id_hide).style.display="none";
		}
		if(document.getElementById(id_show)) {
			document.getElementById(id_show).style.display="block";
		}
	    hide<?php echo $params->get( 'moduleclass_sfx' ); ?>++;
	}

	window.setTimeout("ticker<?php echo $params->get( 'moduleclass_sfx' ); ?>()",100);
	window.setInterval("ticker<?php echo $params->get( 'moduleclass_sfx' ); ?>()", <?php echo $tickerpause ?>000);
</script>
