<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- START of match details -->
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DETAILS'); ?></h2>
<table
	width="98%" align="center" border="0" cellpadding="0" cellspacing="0">
	<!-- Prev Match-->
	<?php
	if ($this->match->old_match_id > 0)
	{
		?>
	<tr>
		<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_OLD_MATCH' ); ?></span>
		<span><?php echo JHTML :: link(sportsmanagementHelperRoute::getMatchReportRoute( $this->project->id, 
		$this->match->old_match_id ),
		$this->oldmatchtext); ?></span></td>
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
		<span><?php echo JHTML :: link(sportsmanagementHelperRoute::getNextMatchRoute( $this->project->id, 
		$this->match->new_match_id ),
		$this->newmatchtext);?></span></td>
	</tr>
	<?php
	}
	?>

	<!-- Date -->
	<?php
	if ($this->config['show_match_date'] == 1)
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
	if ($this->config['show_match_time'] == 1)
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
	if ($this->config['show_time_present'] == 1)
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
	if ($this->config['show_match_number'] == 1)
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
	if ($this->config['show_match_playground'] == 1)
    {
		if ($this->match->playground_id > 0): ?>
			<?php $playground_link = sportsmanagementHelperRoute::getPlaygroundRoute( $this->project->id, $this->match->playground_id);?>
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
	if ($this->config['show_match_referees'] == 1)
    {
		if (!empty($this->referees)): ?>
			<?php $html = array(); ?>
			<tr>
				<td colspan="3"><span class=""><?php echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_REFEREE' ); ?></span>
				<?php foreach ($this->referees AS $ref): ?> <?php $referee_link = sportsmanagementHelperRoute::getRefereeRoute($this->project->id, $ref->person_id);?>
				<?php $html[] = JHtml::link ($referee_link, sportsmanagementHelper::formatName(null, $ref->firstname, $ref->nickname, $ref->lastname, $this->config["name_format"])) .' ('.$ref->position_name.')'; ?>
				<?php endforeach;?> <span><?php echo implode('</span>, <span>', $html); ?></span>
				</td>
			</tr>
		<?php endif;
	} ?>

</table>

<br />



