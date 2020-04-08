<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage editmatch
 * @file       editeventsbb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$params = $this->form->getFieldsets('params');

HTMLHelper::_('behavior.modal');
?>
<div id="gamesevents">
	<form method="post" id="adminForm">
	<?php
		// Welche joomla version
	if (version_compare(JVERSION, '3.0.0', 'ge'))
	{
		// Define tabs options for version of Joomla! 3.1
		$tabsOptionsJ31 = array(
			"active" => "panel1" // It is the ID of the active tab.
			);

		echo HTMLHelper::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptionsJ31);
		echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', Text::_($this->teams->team1));
		echo $this->loadTemplate('home');
		echo HTMLHelper::_('bootstrap.endTab');
		echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', Text::_($this->teams->team2));
		echo $this->loadTemplate('away');
		echo HTMLHelper::_('bootstrap.endTab');
		echo HTMLHelper::_('bootstrap.endTabSet');
	}

	else
	{
		echo HTMLHelper::_('tabs.start', 'tabs', array('useCookie' => 1));
		echo HTMLHelper::_('tabs.panel', Text::_($this->teams->team1), 'panel1');
		echo $this->loadTemplate('home');
		echo HTMLHelper::_('tabs.panel', Text::_($this->teams->team2), 'panel2');
		echo $this->loadTemplate('away');
		echo HTMLHelper::_('tabs.end');
	}
	?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="view" value="match" />
		<input type="hidden" name="option" value="" id="" />
		<input type="hidden" name="boxchecked"    value="0" />
	<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
<div style="clear: both"></div>
