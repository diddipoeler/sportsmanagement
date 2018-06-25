<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_subst.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
?>
<!-- START of Substitutions -->
<?php
if ($this->config['show_substitutions']==1)
{
	if (!empty($this->substitutes))
	{
		?>
		<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'); ?></h2>	
		<table class="table table-responsive">
			<tr>
				<td class="list">
					<ul><?php
						foreach ($this->substitutes as $sub)
						{
							if ($sub->ptid == $this->match->projectteam1_id)
							{
								?><li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
							}
						}
						?></ul>
				</td>
				<td class="list">
					<ul><?php
						foreach ($this->substitutes as $sub)
						{
							if ($sub->ptid == $this->match->projectteam2_id)
							{
								?><li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
							}
						}
						?></ul>
				</td>
			</tr>
		</table>
		<?php
	}
}
?>
<!-- END of Substitutions -->
