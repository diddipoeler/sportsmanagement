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



defined( '_JEXEC' ) or die( 'Restricted access' );
if ( !$this->showediticon )
{
	JFactory::getApplication()->redirect( str_ireplace('layout=form','',JFactory::getURI()->toString()), JText::_('ALERTNOTAUTH') );
}
//load data for editor
require ( JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'html' . DS . 'editor.php' );

// load javascripts
$document = JFactory::getDocument();
// welche joomla version
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHtml::_('behavior.framework', true);
}
else
{
JHtml::_( 'behavior.mootools' );    
}
//$version = urlencode(JoomleagueHelper::getVersion());
$document->addScript(JURI::root().'components/com_sportsmanagement/assets/js/eventsediting.js?v=');
?>
<div style="overflow:auto;">
	<a name="jl_top" id="jl_top"></a>
	<!-- section header e.g. ranking, results etc. -->
	<table class="table">
		<tr>
			<td class="contentheading">
				<?php
				if ($this->roundid>0)
				{
					sportsmanagementHelperHtml::showMatchdaysTitle(JText::_('Round results'), $this->roundid, $this->config );
					if ($this->showediticon) //Needed to check if the user is still allowed to get into the match edit
					{
						$link = sportsmanagementHelperRoute::getResultsRoute( $this->project->id, $this->roundid );
						$imgTitle = JText::_('Exit Edit Mode');
						$desc = JHtml::image('media/com_sportsmanagement/jl_images/edit_exit.png', $imgTitle, array(' title' => $imgTitle));
						echo '&nbsp;';
						echo JHtml::link($link, $desc);
					}
				}
				?>
			</td>
			<td><?php echo sportsmanagementHelperHtml::getRoundSelectNavigation(TRUE); ?></td>
		</tr>
	</table>
	<form name="adminForm" id="adminForm" method="post" action="<?php echo JFactory::getURI()->toString(); ?>">
		<table class="<?php echo $this->config['table_class']; ?>" >
			<!-- Main START -->
			<?php
			if ( count( $this->matches ) > 0 )
			{
				$colspan=($this->project->allow_add_time) ? 15 : 14;
			?>
			<thead>
				<tr class="sectiontableheader">
					<th width="20" style="vertical-align: top; ">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->matches); ?>);" />
					</th>
					<th width="20" style="vertical-align: top; ">&nbsp;</th>
					<?php 
						if($this->project->project_type=='DIVISIONS_LEAGUE') {
							$colspan++;
					?>
					<th style="vertical-align: top; "><?php echo JText::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DIVISION'); ?></th>
					<?php 
						}
					?>
					
					
					
					
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th>
					<th style="text-align: center; vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT'); ?></th>
					<?php
					if ($this->project->allow_add_time)
					{
						?>
						<th style="text-align:center; vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_TYPE'); ?></th>
						<?php
					}
					?>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS'); ?></th>
					<th class="title" class="nowrap" style="vertical-align: top; "><?php echo JTEXT::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS'); ?></th>
					
					
				</tr>
			</thead>
			<!-- Start of the matches for the selected round -->
			<tbody>
			<?php
				$k = 0;
				$i = 0;
				foreach( $this->matches as $match )
				{
//					$this->config['class'] = $this->config['style_class2'];
//					if ( $k == 0 )
//					{
//						$this->config['class'] = $this->config['style_class1'];
//					}
					if ((isset($match->allowed)) && ($match->allowed))
					{
						$this->assignRef('game', $match);
						$this->assignRef('i', $i);
						echo $this->loadTemplate('row');
					}
					$k = 1 - $k;
					$i++;
				}
			}
			?>
			</tbody>
		</table>
		<br/>
		<input type='hidden' name='option' value='com_sportsmanagement' />
        <input type='hidden' name='layout' value='form_dfcday' />
		<input type='hidden' name='task' value='results.saveshort' />
		<input type='hidden' name='p' value='<?php echo $this->project->id; ?>' />
		<input type='hidden' name='r' value='<?php echo $this->roundid; ?>' />
		<input type='hidden' name='sel_r' value='<?php echo $this->roundid; ?>' />
		<input type='hidden' name='Itemid' value='<?php echo JRequest::getInt('Itemid', 1, 'get'); ?>' />

		<input type='hidden' name='boxchecked' value='0' id='boxchecked' />
		<input type='hidden' name='checkmycontainers' value='0' id='checkmycontainers' />
		<input type='hidden' name='save_data' value='1' class='button' />
		<!--
		<input type='submit' name='save' value='<?php echo JText::_('JSAVE' );?>' onclick="$('checkmycontainers').value=0; " />
		-->
		<input type='submit' name='save' value='<?php echo JText::_('JSAVE' );?>' />
		<?php echo JHtml::_('form.token'); ?>
		<!-- Main END -->
	</form>
</div>
<!-- matchdays pageNav -->
<br />
<div class='pagenav'>
<table width='96%' align='center' cellpadding='0' cellspacing='0'>
	<tr>
		<?php //echo JoomleaguePagination::pagenav($this->project); ?>
	</tr>
</table>
<!-- matchdays pageNav END -->
</div>

<table class="not-playing" width='96%' align='center' cellpadding='0' cellspacing='0'>
	<tr>
		<td style='text-align:center; '>
			<?php echo $this->showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project ); ?>
		</td>
	</tr>
</table>
