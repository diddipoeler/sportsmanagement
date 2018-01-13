<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafult_history_leagues.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */

defined('_JEXEC') or die('Restricted access'); 
?>

<h4>

<?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_OVERVIEW_SUMMARY'); ?>

</h4>


<table class="<?PHP echo $this->config['table_class']; ?>">

<thead>
<tr class="sectiontableheader">
<th class="" nowrap="" style="background:#BDBDBD;">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE' ); ?>
</th>
<th class="" nowrap="" style="background:#BDBDBD;">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES' ); ?>
</th>
<th class="" nowrap="" style="background:#BDBDBD;">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL' ); ?>
</th>

<th class="" nowrap="" style="background:#BDBDBD;">
<?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS' ); ?>
</th>

</tr>
</thead>

<?php
	$k=0;
	foreach ($this->leaguerankoverviewdetail as $league => $summary)
	{
	?>
	<tr
  class="<?php echo ($k == 0)? 'sectiontableentry1' : 'sectiontableentry2'; ?>">
	<td><?php echo $league; ?></td>
	<td><?php echo $summary->match; ?></td>
	
	<td><?php echo $summary->won; echo ' / '; ?>
	<?php echo $summary->draw; echo ' / '; ?>
	<?php echo $summary->loss; ?></td>
	
	<td><?php echo $summary->goalsfor; echo ' : '; ?>
	<?php echo $summary->goalsagain; ?></td>
	
	</tr>
	<?php
	$k = 1 - $k;
	}
	?>

</table>