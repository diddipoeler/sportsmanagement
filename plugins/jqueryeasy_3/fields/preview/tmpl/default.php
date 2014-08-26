<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;
?>

<style>
	.demo {
		background-color: #EEEEEE;
	    float: left;
	    margin-left: 20px;
	    padding: 10px;
	}
	
	.toggler {
		width: 100%;
		height: 160px;
		position: relative;
	}
	
	#button {
		padding: .5em 1em;
		text-decoration: none;
	}
	
	#effect {
		width: 180px;
		height: 125px;
		padding: 0.4em;
		position: relative;
	}
	
	#effect div {
		margin: 0;
		padding: 0.4em;
		text-align: center;
	}
</style>
<script>
	jQuery(function() {
		jQuery( "#button" ).toggle(
			function() {
				jQuery( "#effect" ).animate({
					width: 360
				}, 1000 );
			},
			function() {
				jQuery( "#effect" ).animate({
					width: 180
				}, 1000 );
			}
		);
	});
</script>
<div class="demo">
	<div class="toggler">
		<div id="effect" class="ui-widget-content ui-corner-all">
			<div class="ui-widget-header ui-corner-all">Lorem Ipsum</div>
			<p style="padding-top: 10px">Etiam libero neque, luctus a, eleifend
				nec, semper at, lorem. Sed pede. Nulla lorem metus, adipiscing ut,
				luctus sed, hendrerit vitae, mi.
			</p>
		</div>
	</div>
	<button href="#" id="button" role="button" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
		<span class="ui-button-text"><?php echo JText::_('PLG_SYSTEM_JQUERYEASY_FIELD_PREVIEWBUTTON_LABEL') ?></span>
	</button>
</div>
