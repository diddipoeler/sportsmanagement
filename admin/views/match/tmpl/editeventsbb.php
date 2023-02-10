<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editeventsbb.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$params = $this->form->getFieldsets('params');

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{

}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('behavior.modal');
}
?>
<div id="gamesevents">
    <form method="post" id="adminForm" class="form-validate">
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
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="view" value="match"/>
        <input type="hidden" name="option" value="" id=""/>
        <input type="hidden" name="boxchecked" value="0"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<div style="clear: both"></div>
