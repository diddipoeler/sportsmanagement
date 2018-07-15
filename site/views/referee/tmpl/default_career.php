<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_career.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage referee
 */

defined('_JEXEC') or die('Restricted access');

if (count($this->history) > 0)
{
	?>
<h2>

<?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER');	?></h2>
<!-- staff history START -->
<table class="<?php echo $this->config['career_table_class']; ?>">
	<tr>
		<td><br />
			<table class="<?php echo $this->config['career_table_class']; ?>">
				<tr class="sectiontableheader">
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
					<th class="td_l"><?php echo JText::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
				</tr>
					<?php
					$k=0;
					foreach ($this->history AS $station)
					{
					   $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $station->project_slug;
$routeparameter['pid'] = $this->referee->slug;
$link1 = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);

						?>
						<tr class="">
							<td class="td_l"><?php echo JHtml::link($link1,$station->project_name); ?></td>
							<td class="td_l"><?php echo $station->season_name; ?></td>
							<td class="td_l"><?php echo ($station->position_name ? JText::_($station->position_name) : ""); ?></td>
						</tr>
						<?php
						$k=(1-$k);
					}
					?>
				</table>
		</td>
	</tr>
</table>
<br />
<br />
<!-- staff history END -->

<?php
}
?>