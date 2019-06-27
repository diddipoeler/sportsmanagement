<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_career.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage referee
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

if (count($this->history) > 0)
{
?>
<h2>
<?php 
echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER');
?>
</h2>
<!-- staff history START -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="referee_career">
<table class="<?php echo $this->config['career_table_class']; ?>">
	<tr>
		<td><br />
			<table class="<?php echo $this->config['career_table_class']; ?>">
				<tr class="sectiontableheader">
					<th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
					<th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
					<th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
				</tr>
					<?php
					$k=0;
					foreach ($this->history AS $station)
					{
					   $routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $station->project_slug;
$routeparameter['pid'] = $this->referee->slug;
$link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);

						?>
						<tr class="">
							<td class="td_l"><?php echo HTMLHelper::link($link1,$station->project_name); ?></td>
							<td class="td_l"><?php echo $station->season_name; ?></td>
							<td class="td_l"><?php echo ($station->position_name ? Text::_($station->position_name) : ""); ?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
		</td>
	</tr>
</table>
</div>	
<br />
<br />
<!-- staff history END -->

<?php
}
?>
