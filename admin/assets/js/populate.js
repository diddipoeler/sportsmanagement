/**
* @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
* @license	GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * javascript for populate function
 * 
 * 
 */

window.addEvent('domready', function(){
	$('buttonup').addEvent('click', function(){
		moveOptionUp('teamsorder');
	});
	$('buttondown').addEvent('click', function(){
		moveOptionDown('teamsorder');
	});
});

Joomla.submitbutton = function(pressbutton) {
	if (pressbutton == 'round.startpopulate') {
		$('teamsorder').getElements('option').each(function(el) {
			el.setProperty('selected', 'selected');
		});
		Joomla.submitform(pressbutton);
		return;
	}
	Joomla.submitform(pressbutton);
}