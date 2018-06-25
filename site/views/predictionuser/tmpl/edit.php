<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die(JText::_('Restricted access'));
JHTML::_('behavior.tooltip');

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'allowedAdmin<br /><pre>~' . print_r($this->allowedAdmin,true) . '~</pre><br />';
echo 'predictionMember <br /><pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
echo 'form<br /><pre>~' . print_r($this->form,true) . '~</pre><br />';
}


if (!$this->showediticon)
{
	JFactory::getApplication()->redirect(str_ireplace('&layout=edit','',$uri->toString()),JText::_('ALERTNOTAUTH'));
}
$document = JFactory::getDocument();

$script =	'
				function submitbutton(pressbutton)
				{
					var form = document.adminForm;
					if (pressbutton=="cancel"){submitform(pressbutton); return;}
				}
			';

$document->addScriptDeclaration($script);
$document->addScript(JURI::root().'includes/js/joomla.javascript.js');

if (version_compare(JSM_JVERSION, '4', 'eq')) {
    $uri = JUri::getInstance();   
} else {
    $uri = JFactory::getURI();
}
?>
<form name='adminForm' id='adminForm' method='post' >
	<table class="table">
    <tr>
    <td class='sectiontableheader'><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_TITLE',$this->predictionGame->name); ?>
    </td>
    </tr>
    </table>
	<br />
	<?php /* approved show_profile fav_team champ_tipp slogan reminder receipt admintipp picture */ ?>
	<table class="table" >
		<tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NAME','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_NAME');
			?>
			<td colspan='2'><?php echo ($this->config['show_full_name']) ? $this->predictionMember->name : $this->predictionMember->username; ?></td>
		</tr>
		<tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REGDATE','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_REGDATE');
			?>
			<td colspan='2'><?php
				$regDate = substr($this->predictionMember->pmRegisterDate,0,10);
				$regTime = substr($this->predictionMember->pmRegisterDate,11,8);
				if ($this->allowedAdmin)
				{
					echo JText::sprintf(	'%1$s - %2$s',
											JHTML::calendar(sportsmanagementHelper::convertDate($regDate),'registerDate','date','%d-%m-%Y','size="10"'),
											'<input class="inputbox" type="text" name="registerTime" size="4" maxlength="5" value="'.$regTime.'" />');
				}
				else
				{
					echo '<input type="hidden" name="registerDate" value="'.sportsmanagementHelper::convertDate($regDate).'" />';
					echo '<input type="hidden" name="registerTime" value="'.$regTime.'" />';
					echo	$this->predictionMember->pmRegisterDate != '0000-00-00 00:00:00' ?
							JHTML::date($this->predictionMember->pmRegisterDate,JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REGDATE_FORMAT')) :
							JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_UNKNOWN');
				}
				?></td>
		</tr>
		<tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_APPROVED','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_APPROVED');
			?>
			<td colspan='2'><?php echo $this->lists['approvedForGame']; ?></td>
		</tr>
        
        <tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_GROUP','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_GROUP');
			?>
			<td colspan='2'>
            <?php 
            echo $this->lists['grouplist'];
            
            if (!$this->tippallowed)
            {
            echo '<br>';
            echo '<font size="2" color="red">'.JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NO_GROUP_CHANGE').'</font>';  
            } 
            ?>
            </td>
		</tr>
        
		<?php
		if ($this->config['allow_alias'])
		{
			?>
			<tr>
				<?php
				echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALIAS','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_ALIAS');
				?>
				<td colspan='2'>
					<input	class='inputbox' type='text' name='aliasName' size='<?php echo $this->config['input_alias_length']; ?>' maxlength='255'
							value='<?php echo strip_tags($this->predictionMember->aliasName); ?>' />
				</td>
			</tr>
			<?php
		}
		?>
		<?php
		if ($this->config['edit_slogan'])
		{
			?>
			<tr>
				<?php
				echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SLOGAN','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_SLOGAN');
				?>
				<td colspan='2'>
					<input	class='inputbox' type='text' name='slogan' size='<?php echo $this->config['input_slogan_length']; ?>' maxlength='255'
							value='<?php echo strip_tags($this->predictionMember->slogan); ?>' />
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SHOW_PROFILE','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_SHOW_PROFILE');
			?>
			<td colspan='2'><?php echo $this->lists['show_profile']; ?></td>
		</tr>
		<?php
		if ($this->config['edit_reminder'])
		{
			?>
			<tr>
				<?php
				echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REMINDER','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_REMINDER');
				?>
				<td colspan='2'><?php echo $this->lists['reminder']; ?></td>
			</tr>
			<?php
		}
		?>
		<?php
		if ($this->config['edit_receipt'])
		{
			?>
			<tr>
				<?php
				echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_RECEIPT','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_RECEIPT');
				?>
				<td colspan='2'><?php echo $this->lists['receipt']; ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<?php
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALLOW_ADMIN','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_ALLOW_ADMIN');
			?>
			<td colspan='2'><?php echo $this->lists['admintipp']; ?></td>
		</tr>
		<?php
		if ($this->config['edit_favteam'])
		{
			$rowspan = count($this->predictionProjectS);
			?>
			<tr>
				<?php
				echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_FAVTEAM','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_FAVTEAM',$rowspan);

				foreach ($this->predictionProjectS AS $predictionProject)
				{
					?><td width='10%' style='text-align:right; '><?php
						echo $this->lists['fav_team'][$predictionProject->project_id].'<br>';
						?><?php

					?><?php
						if ($rowspan > 1)
						{
							if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
							{
								echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_FAVTEAM_IN_PROJECT','<b>'.$predictionProjectSettings->name.'</b>');
							}
							else
							{
								echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_GETPROJECT_ERROR');
							}
						}
						else {echo '&nbsp;';}
						?></td></tr><tr><?php
				}
				?>
			</tr>
			<?php
		}
		?>
		<tr>
			<?php
			$rowspan = count($this->predictionProjectS);

			
			echo sportsmanagementModelPrediction::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_CHAMPION','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_CHAMPION',$rowspan);

				foreach ($this->predictionProjectS AS $predictionProject)
				{
				
//echo '<br /><pre>~' . print_r($predictionProject,true) . '~</pre><br />';
					?><td width='10%' style='text-align:right; '><?php
						
						
            echo $this->lists['champ_tipp_enabled'][$predictionProject->project_id];
            echo '<br>';
						if (!$this->tippallowed)
            {
            
            echo '<font size="2" color="red">'.JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NO_GROUP_CHANGE').'</font><br>';    
            } 
						?><?php

					?><?php
						if ($rowspan > 1)
						{
							if ($predictionProjectSettings = sportsmanagementModelPrediction::getPredictionProject($predictionProject->project_id))
							{
								echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_CHAMPION_IN_PROJECT','<b>'.$predictionProjectSettings->name.'</b>');
							}
							else
							{
								echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_GETPROJECT_ERROR');
							}
						}
						else {echo '&nbsp;';}
						?></td></tr><tr><?php
				}
				?>
		</tr>
		<?php
		if ($this->config['edit_avatar_upload'])
		{
		
		?>
		
		    <tr>
		    <td>
<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_AVATAR' );?>
			</legend>
			<table class="admintable">
					<?php foreach ($this->form->getFieldset('imageselect') as $field): ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>

			</td>
		</tr>
		

		
		<?php
		}		
		?>
		<tr>
			<td>&nbsp;</td>
			<td colspan='2'>
				<input	type='submit' name='saveInfo' value='<?php echo JText::_('JSAVE'); ?>'
						onClick="Joomla.submitform('predictionusers.savememberdata'); " />
				<input	type='submit' name='cancel' value='<?php echo JText::_('JPREV'); ?>'
						onClick="Joomla.submitform('predictionusers.cancel'); " />
			</td>
		</tr>
	</table>
	<input type='hidden' name='p' value='<?php echo $this->predictionGame->id; ?>' />
	<input type='hidden' name='prediction_id' value='<?php echo $this->predictionGame->id; ?>' />
	<input type='hidden' name='user_id' value='<?php echo $this->actJoomlaUser->id; ?>' />
	<input type='hidden' name='member_id' value='<?php echo $this->predictionMember->pmID; ?>' />
	<input type='hidden' name='option' value='com_sportsmanagement' />
	<input type='hidden' name='task' value='predictionusers.savememberdata' />
	<?php echo JHTML::_('form.token'); ?>
</form><br />