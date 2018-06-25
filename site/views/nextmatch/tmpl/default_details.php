<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_details.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

?>

<!-- START of match details -->
<h4><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DETAILS'); ?></h4>
<table class="table">
	<!-- Prev Match-->
	<?php
	if ($this->match->old_match_id > 0)
	{
		?>
	<tr>
		<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_OLD_MATCH' ); ?></span>
		<span><?php 
		$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['mid'] = $this->match->old_match_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
        echo JHTML :: link($link,$this->oldmatchtext); 
        ?></span></td>
	</tr>
	<?php
	}
	?>
	<!-- Next Match-->
	<?php
	if ($this->match->new_match_id > 0)
	{
		?>
	<tr>
		<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_NEW_MATCH' ); ?></span>
		<span>
        <?php 
        echo JHTML :: link(sportsmanagementHelperRoute::getNextMatchRoute($this->project->id,$this->match->new_match_id,JFactory::getApplication()->input->getInt('cfg_which_database',0) ),$this->newmatchtext);
        ?>
        </span></td>
	</tr>
	<?php
	}
	?>

	<!-- Date -->
	<?php
	if ( $this->config['show_match_date'] )
	{
		if ($this->match->match_date > 0): ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_DATE' ); ?></span>
					<span><?php echo JHtml::date($this->match->match_date, JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE' ) ); ?></span>
				</td>
			</tr>
			<?php endif;
	} ?>

	<!-- Time -->
	<?php
	if ( $this->config['show_match_time'] )
	{
		if ($this->match->match_date > 0): ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_TIME' ); ?></span>
					<span><?php echo sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project); ?></span>
				</td>
			</tr>
			<?php endif;
	} ?>

	<!-- present -->
	<?php
	if ( $this->config['show_time_present'] )
	{
		if ($this->match->time_present > 0): ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PRESENT' ); ?></span>
					<span><?php echo $this->match->time_present; ?></span></td>
			</tr>
			<?php endif;
	} ?>

	<!-- match number -->
	<?php
	if ( $this->config['show_match_number'] )
	{
		if ($this->match->match_number > 0): ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_NUMBER' ); ?></span>
				<span><?php echo $this->match->match_number; ?></span></td>
			</tr>
		<?php endif;
	} ?>

	<!-- match canceled -->
	<?php if ($this->match->cancel > 0): ?>
	<tr>
		<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_CANCEL_REASON' ); ?></span>
		<span><?php echo $this->match->cancel_reason; ?></span></td>
	</tr>
	<?php endif; ?>

	<tr>
		<td colspan="3">&nbsp;</td>
	</tr>


	<!-- playground -->
	<?php
	if ( $this->config['show_match_playground'] )
    {
		if ($this->match->playground_id > 0): ?>
			<?php 
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['pgid'] = $this->match->playground_slug;
$playground_link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);
            ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND' ); ?></span>
					<span>
                    <?php
                    if ( isset($this->playground->name) )
                    { 
                    echo JHtml::link ($playground_link, $this->playground->name);
                    }
                    else
                    {
                    echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND_NO_ASSIGN' );    
                    } 
                    ?>
                    </span>
				</td>
			</tr>
		<?php endif;
	} ?>

	<!-- referee -->
	<?php
	if ( $this->config['show_match_referees'] )
    {

    	
		if (!empty($this->referees)): ?>
			<?php $html = array(); ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_REFEREE' ); ?></span>
				<?php foreach ($this->referees AS $ref): ?> 
				<?php 
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['pid'] = $ref->person_id;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);
				?>
				<?php $html[] = JHtml::link ($link, sportsmanagementHelper::formatName(null, $ref->firstname, $ref->nickname, $ref->lastname, $this->config["name_format"])) .' ('.$ref->position_name.')'; ?>
				<?php endforeach;?> <span><?php echo implode('</span>, <span>', $html); ?></span>
				</td>
			</tr>
		<?php endif;
	} ?>

</table>

<br />



