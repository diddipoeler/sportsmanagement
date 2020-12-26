/*
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

jQuery(document).ready(function($) {
	
	if ($('.syw_header').length) {
		$('.syw_header').each(function() {
			$(this).closest('.control-group').addClass('control-group-header');
		});
	}
	
	if ($('.syw_help').length) {
		$('.syw_help').each(function() {
			$(this).closest('.control-group').addClass('control-group-help');
		});
	}
	
	if ($('.syw_info').length) {
		$('.syw_info').each(function() {
			$(this).closest('.control-group').addClass('control-group-info');
		});
	}
	
	//$('.tab-pane[id|=\'attrib\'] .control-label label[class=\'hasPopover\']').each(function() {
	//$('.tab-pane .control-label label[class=\'hasPopover\']').each(function() {
		//$(this).prepend('<i class="icon-info"></i>');
	//});
});
