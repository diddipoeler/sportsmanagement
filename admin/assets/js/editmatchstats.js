/**
* @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jQuery(document).ready(function(){
	// check row box when a value is updated
	$$('tr.statrow').each(function(row){
		row.getElements('.stat').each(function(stat){
			stat.addEvent('change', function(){
				row.getElement('.statcheck').setProperty('checked', 'true');
			});
		});
	});

	// check row box when a value is updated
	$$('tr.staffstatrow').each(function(row){
		row.getElements('.staffstat').each(function(stat){
			stat.addEvent('change', function(){
				row.getElement('.staffstatcheck').setProperty('checked', 'true');
			});
		});
	});
});