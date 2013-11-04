<?php
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
 * EditeventsBB view
 *
 * @package	Joomleague
 * @since 0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_( 'behavior.tooltip' );
JHTML::_( 'behavior.modal' );
?>
<div id="gamesevents">
	<form method="post" id="adminForm">
		<?php
		echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));
		echo JHTML::_('tabs.panel',JText::_($this->teams->team1), 'panel1');
		echo $this->loadTemplate('home');
		
		echo JHTML::_('tabs.panel',JText::_($this->teams->team2), 'panel2');
		echo $this->loadTemplate('away');
		
		echo JHTML::_('tabs.end');
		?>
		<input type="hidden" name="task" value="match.saveeventbb" />
		<input type="hidden" name="view" value="match" />
		<input type="hidden" name="option" value="com_joomleague" id="option" />
		<input type="hidden" name="boxchecked"	value="0" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
<div style="clear: both"></div>