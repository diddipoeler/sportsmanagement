<?php 
/**
* @copyright	Copyright (C) 2007-2012 JoomLeague.net. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined('_JEXEC') or die(Text::_('Restricted access'));
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.tooltip');

//echo '<br /><pre>~' . print_r($this->allowedAdmin,true) . '~</pre><br />';
//echo 'predictionMember <br /><pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
if (version_compare(JSM_JVERSION, '4', 'eq')) {
    $uri = Uri::getInstance();   
} else {
    $uri = Factory::getURI();
}

if (!$this->showediticon)
{
	Factory::getApplication()->redirect(str_ireplace('&layout=edit','',$uri->toString()),Text::_('ALERTNOTAUTH'));
}
$document =& Factory::getDocument();

$script =	'
				function submitbutton(pressbutton)
				{
					var form = document.adminForm;
					if (pressbutton=="cancel"){submitform(pressbutton); return;}
				}
			';

$document->addScriptDeclaration($script);
$document->addScript(Uri::root().'includes/js/joomla.javascript.js');
?>
<form name='adminForm' id='adminForm' method='post' >
	<table width='100%'><tr><td class='sectiontableheader'><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_TITLE',$this->predictionGame->name); ?></td></tr></table>
	<br />
	<?php /* approved show_profile fav_team champ_tipp slogan reminder receipt admintipp picture */ ?>
	<table class='plinfo' cellpadding='4' cellspacing='1'>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_NAME','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_NAME');
			?>
			<td colspan='2'><?php echo ($this->config['show_full_name']) ? $this->predictionMember->name : $this->predictionMember->username; ?></td>
		</tr>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REGDATE','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_REGDATE');
			?>
			<td colspan='2'><?php
				$regDate = substr($this->predictionMember->pmRegisterDate,0,10);
				$regTime = substr($this->predictionMember->pmRegisterDate,11,5);
				if ($this->allowedAdmin)
				{
					echo Text::sprintf(	'%1$s - %2$s',
											HTMLHelper::calendar(JoomleagueHelper::convertDate($regDate),'registerDate','date','%d-%m-%Y','size="10"'),
											'<input class="inputbox" type="text" name="registerTime" size="4" maxlength="5" value="'.$regTime.'" />');
				}
				else
				{
					echo '<input type="hidden" name="registerDate" value="'.JoomleagueHelper::convertDate($regDate).'" />';
					echo '<input type="hidden" name="registerTime" value="'.$regTime.'" />';
					echo	$this->predictionMember->pmRegisterDate != '0000-00-00 00:00:00' ?
							HTMLHelper::date($this->predictionMember->pmRegisterDate,Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REGDATE_FORMAT')) :
							Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_UNKNOWN');
				}
				?></td>
		</tr>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_APPROVED','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_APPROVED');
			?>
			<td colspan='2'><?php echo $this->lists['approvedForGame']; ?></td>
		</tr>
		<?php
		if ($this->config['allow_alias'])
		{
			?>
			<tr>
				<?php
				echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALIAS','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_ALIAS');
				?>
				<td colspan='2'>
					<input	class='inputbox' type='text' name='aliasName' size='60' maxlength='255'
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
				echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SLOGAN','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_SLOGAN');
				?>
				<td colspan='2'>
					<input	class='inputbox' type='text' name='slogan' size='60' maxlength='255'
							value='<?php echo strip_tags($this->predictionMember->slogan); ?>' />
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_SHOW_PROFILE','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_SHOW_PROFILE');
			?>
			<td colspan='2'><?php echo $this->lists['show_profile']; ?></td>
		</tr>
		<?php
		if ($this->config['edit_reminder'])
		{
			?>
			<tr>
				<?php
				echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_REMINDER','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_REMINDER');
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
				echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_RECEIPT','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_RECEIPT');
				?>
				<td colspan='2'><?php echo $this->lists['receipt']; ?></td>
			</tr>
			<?php
		}
		?>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_ALLOW_ADMIN','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_ALLOW_ADMIN');
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
				echo $this->model->echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_FAVTEAM','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_FAVTEAM',$rowspan);

				foreach ($this->predictionProjectS AS $predictionProject)
				{
					?><td width='10%' style='text-align:right; '><?php
						echo $this->lists['fav_team'][$predictionProject->project_id];
						?></td><?php

					?><td><?php
						if ($rowspan > 1)
						{
							if ($predictionProjectSettings = $this->model->getPredictionProject($predictionProject->project_id))
							{
								echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_FAVTEAM_IN_PROJECT','<b>'.$predictionProjectSettings->name.'</b>');
							}
							else
							{
								echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_GETPROJECT_ERROR');
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

			
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_CHAMPION','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_CHAMPION',$rowspan);

				foreach ($this->predictionProjectS AS $predictionProject)
				{
				
//echo '<br /><pre>~' . print_r($predictionProject,true) . '~</pre><br />';
					?><td width='10%' style='text-align:right; '><?php
						//echo $this->lists['champ_tipp'][$predictionProject->project_id];
						
            echo $this->lists['champ_tipp_enabled'][$predictionProject->project_id];
						
						?></td><?php

					?><td><?php
						if ($rowspan > 1)
						{
							if ($predictionProjectSettings = $this->model->getPredictionProject($predictionProject->project_id))
							{
								echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_USERS_CHAMPION_IN_PROJECT','<b>'.$predictionProjectSettings->name.'</b>');
							}
							else
							{
								echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_GETPROJECT_ERROR');
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
		// echo 'imageselect <pre>'.print_r($this->imageselect ,true).'</pre><br>';
		// /media/com_sportsmanagement/jl_images/spinner.gif 
		?>
		<tr>
			<?php
			echo JoomleagueModelPredictionUsers::echoLabelTD('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_AVATAR','COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_LABEL_HELP_AVATAR');
			?>
			<td colspan='2'>
				<img	class='imagepreview' src='<?PHP echo $this->predictionMember->picture  ; ?>' name='picture_preview'
						id='picture_preview' border='3' alt='<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_AVATAR_PREVIEW'); ?>' title='<?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_EDIT_AVATAR_PREVIEW'); ?>' /><br /><?php echo $this->imageselect; ?>

<fieldset class="adminform">
			<legend><?php echo Text::_('JL_PRED_USERS_EDIT_LABEL_AVATAR' );?>
			</legend>
			<table class="admintable">
					<?php foreach ($this->form->getFieldset('picture') as $field): ?>
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
				<input	type='submit' name='saveInfo' value='<?php echo Text::_('JSAVE'); ?>'
						onClick="javascript:submitbutton('save'); " />
				<input	type='submit' name='cancel' value='<?php echo Text::_('JPREV'); ?>'
						onClick="javascript:submitbutton('cancel'); " />
			</td>
		</tr>
	</table>
	<input type='hidden' name='p'				value='<?php echo $this->predictionGame->id; ?>' />
	<input type='hidden' name='prediction_id'	value='<?php echo $this->predictionGame->id; ?>' />
	<input type='hidden' name='user_id'			value='<?php echo $this->actJoomlaUser->id; ?>' />
	<input type='hidden' name='member_id'		value='<?php echo $this->predictionMember->pmID; ?>' />
	<input type='hidden' name='option'			value='com_sportsmanagement' />
	
	<input type='hidden' name='task' 			value='predictionusers.savememberdata' />
	<?php echo HTMLHelper::_('form.token'); ?>
</form><br />