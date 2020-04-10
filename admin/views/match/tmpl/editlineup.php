<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage match
 * @file       editlineup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

// welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('jquery.framework');
}


$params = $this->form->getFieldsets('params');

?>
<?php
//save and close
$close = Factory::getApplication()->input->getInt('close', 0);
if ($close == 1)
{
	?>
    <script>
        window.addEvent('domready', function () {
            $('cancel').onclick();
        });
    </script>
	<?php
}
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>" id='adminForm' method='post'
      style='display:inline' name='adminform'>
    <fieldset>
        <div class="fltrt">
            <button type="button"
                    onclick="jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo Text::_('JAPPLY'); ?></button>
            <button type="button"
                    onclick="$('close').value=1; jQuery('select.position-starters option').prop('selected', 'selected');jQuery('select.position-staff option').prop('selected', 'selected');Joomla.submitform('matches.saveroster', this.form);">
				<?php echo Text::_('JSAVE'); ?></button>

        </div>
        <div class="configuration">
			<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELU_TITLE', $this->teamname); ?>
        </div>
    </fieldset>
    <div class="clear"></div>
    <div id="lineup">
		<?php
		// welche joomla version
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Define tabs options for version of Joomla! 3.1
			$tabsOptionsJ31 = array(
				"active" => "panel1" // It is the ID of the active tab.
			);

			echo HTMLHelper::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptionsJ31);
			echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'));
			echo $this->loadTemplate('players');
			echo HTMLHelper::_('bootstrap.endTab');
			echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', Text::_('COM_SPORTSMANAGEMENT_TABS_SUBST'));
			echo $this->loadTemplate('substitutions');
			echo HTMLHelper::_('bootstrap.endTab');
			echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel3', Text::_('COM_SPORTSMANAGEMENT_TABS_STAFF'));
			echo $this->loadTemplate('staff');
			echo HTMLHelper::_('bootstrap.endTab');
			echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel4', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'));
			echo $this->loadTemplate('players_trikot_numbers');
			echo HTMLHelper::_('bootstrap.endTab');
			echo HTMLHelper::_('bootstrap.endTabSet');
		}
		else
		{
			// focus on players tab
			$startOffset = 1;
			echo HTMLHelper::_('tabs.start', 'tabs', array('startOffset' => $startOffset));
			echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYERS'), 'panel1');
			echo $this->loadTemplate('players');

			echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_SUBST'), 'panel2');
			echo $this->loadTemplate('substitutions');

			echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_STAFF'), 'panel3');
			echo $this->loadTemplate('staff');

			echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_PLAYER_TRIKOT_NUMBERS'), 'panel4');
			echo $this->loadTemplate('players_trikot_numbers');

			echo HTMLHelper::_('tabs.end');
		}
		?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="view" value=""/>
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>"/>
        <input type="hidden" name="close" id="close" value="0"/>
        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
        <input type="hidden" name="changes_check" value="0" id="changes_check"/>

        <input type="hidden" name="team" value="<?php echo $this->tid; ?>" id="team"/>
        <input type="hidden" name="positionscount" value="<?php echo count($this->positions); ?>" id="positioncount"/>


		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </div>
</form>
