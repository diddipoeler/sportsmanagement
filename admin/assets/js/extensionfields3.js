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
			var closest = $(this).closest('.control-group');
			closest.addClass('control-group-info');			
			if ($(this).hasClass('info')) {
				closest.addClass('alert alert-info');
			}
			if ($(this).hasClass('success')) {
				closest.addClass('alert alert-success');
			}		
			if ($(this).hasClass('warning')) {
				closest.addClass('alert alert-warning');
			}			
			if ($(this).hasClass('error')) {
				closest.addClass('alert alert-error');
			}			
		});
	}
});
