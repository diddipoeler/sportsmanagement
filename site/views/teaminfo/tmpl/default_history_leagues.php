<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
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
<?php
if ( $this->overallconfig['use_table_or_bootstrap'] )
{
?>
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
	}
	?>

</table>
<?php
}
else
{	
?>
<div class="container-fluid">
<div class="row-fluid">
<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3" style="background:#BDBDBD;"><?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE' ); ?></div>    
<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3" style="background:#BDBDBD;"><?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES' ); ?></div>    
<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3" style="background:#BDBDBD;"><?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL' ); ?></div>    
<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3" style="background:#BDBDBD;"><?PHP echo JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS' ); ?></div>
</div>
<?php

	foreach ($this->leaguerankoverviewdetail as $league => $summary)
	{
	?>
<div class="row-fluid">
  
	<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"><?php echo $league; ?></div>
	<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"><?php echo $summary->match; ?></div>
	
	<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"><?php echo $summary->won; echo ' / '; ?>
	<?php echo $summary->draw; echo ' / '; ?>
	<?php echo $summary->loss; ?></div>
	
	<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"><?php echo $summary->goalsfor; echo ' : '; ?>
	<?php echo $summary->goalsagain; ?></div>
</div>	

	<?php

	}
	?>

</div>
<?php
} 
?> 
