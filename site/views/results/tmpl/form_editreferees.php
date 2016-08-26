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

defined('_JEXEC') or die('Restricted access');

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
//JHtml::_('jquery.framework');
}

/**
 


UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Januar' 
WHERE  r.name = '1.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Februar' 
WHERE  r.name = '2.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'März' 
WHERE  r.name = '3.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'April' 
WHERE  r.name = '4.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Mai' 
WHERE  r.name = '5.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Juni' 
WHERE  r.name = '6.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Juli' 
WHERE  r.name = '7.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'August' 
WHERE  r.name = '8.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'September' 
WHERE  r.name = '9.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Oktober' 
WHERE  r.name = '10.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'November' 
WHERE  r.name = '11.Spieltag'
and p.league_id = 165;

UPDATE j25_sportsmanagement_round as r
inner join j25_sportsmanagement_project as p on p.id = r.project_id
SET r.name =  'Dezember' 
WHERE  r.name = '12.Spieltag'
and p.league_id = 165;
  
*/
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
//$params = $this->form->getFieldsets('params');

$default_name_format = '';

$allreferees = sportsmanagementModelMatch::getRefereeRoster(0,$this->game->id);
		if (isset($allreferees))
		{
			foreach ($allreferees AS $referee) 
            {
				$inroster[] = $referee->value;
			}
		} 
		$projectreferees = sportsmanagementModelMatch::getProjectReferees($inroster,$this->project->id);

		if (count($projectreferees) > 0)
		{
			foreach ($projectreferees AS $referee)
			{
				$projectreferees2[]=JHtml::_('select.option',$referee->value,
				  sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format) .
				  ' - ('.strtolower(JText::_($referee->positionname)).')');
			}
		}
		$lists['team_referees'] = JHtml::_(	'select.genericlist',$projectreferees2,'roster[]',
											'style="font-size:12px;height:auto;min-width:15em;" ' .
											'class="inputbox" multiple="true" size="'.max(10,count($projectreferees2)).'"',
											'value','text');        

// projekt positionen                                                    
  		$selectpositions[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION'));
		if ($projectpositions = sportsmanagementModelMatch::getProjectPositionsOptions(0, 3,$this->project->id))
		{
			$selectpositions = array_merge($selectpositions,$projectpositions);
		}
		$lists['projectpositions'] = JHtml::_('select.genericlist',$selectpositions,'project_position_id','class="inputbox" size="1"','value','text');
        

		$squad = array();
		if (!$projectpositions)
		{
			JError::raiseWarning(440,'<br />'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS').'<br /><br />');
			return;
		}

		// generate selection list for each position
		foreach ($projectpositions AS $key => $pos)
		{
			// get referees assigned to this position
			$squad[$key] = sportsmanagementModelMatch::getRefereeRoster($pos->value,$this->game->id);
		}
		if (count($squad) > 0)
		{
			foreach ($squad AS $key => $referees)
			{
				$temp[$key] = array();
				if (isset($referees))
				{
					foreach ($referees AS $referee)
					{
						$temp[$key][]=JHtml::_('select.option',$referee->value,
						  sportsmanagementHelper::formatName(null, $referee->firstname, $referee->nickname, $referee->lastname, $default_name_format));
					}
				}
                
				$lists['team_referees'.$key]=JHtml::_(	'select.genericlist',$temp[$key],'position'.$key.'[]',
														' style="font-size:12px;height:auto;min-width:15em;" '.
														'class="position-starters" multiple="true" ',
														'value','text');
                                     
                                                        
			}
		}  

        
//echo 'sportsmanagementViewMatch _displayEditReferees project_id<br><pre>'.print_r($this->project->id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees project_id<br><pre>'.print_r($allreferees,true).'</pre>';

//echo 'sportsmanagementViewMatch _displayEditReferees project_id<br><pre>'.print_r($this->project_id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees item->id<br><pre>'.print_r($this->item->id,true).'</pre>';
//echo 'sportsmanagementViewMatch _displayEditReferees lists<br><pre>'.print_r($this->lists,true).'</pre>';

?>
<div id="helloworld">
  <h1>Hello <?php //echo $displayData['name']; ?>!</h1>
</div>
<?php

?>
<div id="lineup">
<!--
	<form  action="<?php echo JRoute::_('index.php?option=com_sportsmanagement');?>" id='edit-referee-<?php echo $this->game->id;?>' method='post' style='display:inline' name='adminform' >
-->    
	<fieldset>
		<div class="fltrt">
			<!--
            <button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');document.getElementById('edit-referee-<?php echo $this->game->id;?>').submit();">
				<?php echo JText::_('JAPPLY');?></button> -->
            
            <button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');document.getElementById('edit-referee-<?php echo $this->game->id;?>').submit();">
				<?php echo JText::_('JAPPLY');?></button>
                    
			<button type="button" onclick="jQuery('select.position-starters option').prop('selected', 'selected');document.getElementById('edit-referee-<?php echo $this->game->id;?>').submit();">
				<?php echo JText::_('JSAVE');?></button>
			<!--
            <button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
				<?php echo JText::_('JCANCEL');?></button>
            -->  
            <button type="button" class="btn btn-blue" data-dismiss="modal"><?php echo JText::_('JCANCEL');?></button>  
		</div>
		<div class="configuration" >
			<?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'); ?>
		</div>
	</fieldset>
	<div class="clear"></div>
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_DESCR'); ?></legend>
			<table class='table'>
			<thead>
				<tr>
					<th>
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_REFS'); ?>
					</th>
					<th>
					<?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_ASSIGNED'); ?>
					</th>					
				</tr>
			</thead>			
				<tr>
					<td style="text-align:center; ">
						<?php
						// echo select list of non assigned players from team roster
						echo $lists['team_referees'];
						?>
					</td>
					<td style="text-align:center; vertical-align:top; ">
						<table>
							<?php
							foreach ($this->positions AS $key => $pos)
							{
								?>
								<tr>
									<td style='text-align:center; vertical-align:middle; '>
										<!-- left / right buttons -->
										<br />
										
                                        
                                        <input id="moveright" type="button" value="Move Right" onclick="move_list_items('roster','position<?php echo $key;?>');" />
                                        <input id="moveleft" type="button" value="Move Left" onclick="move_list_items('position<?php echo $key;?>','roster');" />
                                        
									</td>
									<td>
										<!-- player affected to this position -->
										<b><?php echo JText::_($pos->text); ?></b><br />
										<?php echo $lists['team_referees'.$key];?>
									</td>
									<td style='text-align:center; vertical-align:middle; '>
										<!-- up/down buttons -->
										<br />
										<input	type="button" id="moveup-<?php echo $key;?>" class="inputbox move-up"
												value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_UP'); ?>" /><br />
										<input	type="button" id="movedown-<?php echo $key;?>" class="inputbox move-down"
												value="<?php echo JText::_('JGLOBAL_DOWN'); ?>" />
									</td>
								</tr>
								<?php
							}
							?>
						</table>
					</td>
				</tr>
			</table>
		</fieldset>
		<br/>
		<br/>
        
        <input type='hidden' name='option' value='com_sportsmanagement' />
        <input type='hidden' name='view' value='results' />
        <input type='hidden' name='cfg_which_database' value='<?php echo sportsmanagementModelProject::$cfg_which_database; ?>' />
        <input type='hidden' name='s' value='<?php echo sportsmanagementModelProject::$seasonid; ?>' />
        <input type='hidden' name='p' value='<?php echo sportsmanagementModelResults::$projectid; ?>' />
        <input type='hidden' name='r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>' />
        <input type='hidden' name='divisionid' value='<?php echo sportsmanagementModelResults::$divisionid; ?>' />
        <input type='hidden' name='mode' value='<?php echo sportsmanagementModelResults::$mode; ?>' />
        <input type='hidden' name='order' value='<?php echo sportsmanagementModelResults::$order; ?>' />
        <input type='hidden' name='layout' value='form' />
        <input type='hidden' name='task' value='results.saveReferees' />
		<input type='hidden' name='sel_r' value='<?php echo sportsmanagementModelProject::$roundslug; ?>' />
		<input type='hidden' name='Itemid' value='<?php echo JRequest::getInt('Itemid', 1, 'get'); ?>' />
		<input type='hidden' name='boxchecked' value='0' id='boxchecked' />
		<input type='hidden' name='checkmycontainers' value='0' id='checkmycontainers' />
		<input type='hidden' name='save_data' value='1' class='button' />
<!--                
		<input type="hidden" name="task" value="results.saveReferees" />
		<input type="hidden" name="view" value="results" />
		<input type="hidden" name="cfg_which_database" value="0" />
		<input type="hidden" name="s" value="0" />
		<input type="hidden" name="p" value="<?php echo $this->project->id; ?>" />
		<input type="hidden" name="r" value="0" />
        
        <input type="hidden" name="division" value="0" />
        <input type="hidden" name="moder" value="0" />
        <input type="hidden" name="order" value="0" />
        <input type="hidden" name="layout" value="<?php echo $this->layout; ?>" />

        <input type="hidden" name="component" value="com_sportsmanagement" />
        -->
		<?php //echo JHtml::_('form.token')."\n"; ?>
<!--	</form> -->
</div>
