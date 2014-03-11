<?php
	if ($use_local_jquery) {
	?>
		<script type="text/javascript" src="/modules/mod_joomleague_liveticker/js/jquery-1.2.3.pack.js"></script>
	<?php
	}
	if ($use_css) {
	?>
		<link rel="stylesheet" href="/modules/mod_joomleague_liveticker/css/<?php echo $use_css; ?>" type="text/css" />
	<?php
	}
?>
<script type="text/javascript" src="/modules/mod_joomleague_liveticker/js/turtushout.js"></script>
<div id="turtushout-warning"><?php echo JText::_( '!Warning! JavaScript must be enabled for proper operation.' ); ?></div>
<?php

$display_add_box = 0;

	if ($display_add_box)
    {
	?>
		<form name='turtushout-form' id='turtushout-form' style='display:none;'>
			<?php
				if($userId) {
					if($display_welcome) { ?>
						Hi, you logged in as <?php echo $name; ?><br/>
			<?php
					}
				} else {
					if ($display_username) { ?>
						<label><?php echo JText::_('Name') ?></label>
						<input class="inputbox" type="text" name="created_by_alias" size="<?php echo $size;?>"><br/>
			<?php
					}
				}
			?>

			<?php if ($display_title) { ?>
				<label><?php echo JText::_('Title') ?></label>
				<input class="inputbox" type="text" name="title" size="<?php echo $size;?>"><br/>
			<?php } ?>
				<label><?php echo JText::_('Text') ?></label>
				<textarea class="inputbox" name="text" rows="<?php echo $rows;?>" cols="<?php echo $cols; ?>"></textarea>
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_('Submit') ?>" />

		</form>

	<?php
	}
?>
<div id="turtushout-status" style='display:none;'></div>

<div id="turtushout-shout">
	<?php echo $list_html; ?>
</div>

<script>
	var turtushout_update_timeout = <?php echo $update_timeout * 1000; ?>;
	var turtushout_server_url = '<?php echo JURI::root(); ?>';
</script>
