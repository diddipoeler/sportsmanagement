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

defined('_JEXEC') or die(JText::_('Restricted access'));

if ( $this->show_debug_info )
{
$visible = 'text';
}
else
{
$visible = 'hidden';
}

?><p><?php
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_01');
	?></p><p><?php
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_02');
	?></p><p><?php
	echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_03',$this->config['ownername'],'<b>'.$this->websiteName.'</b>');
	?></p><p>&nbsp;</p><p><?php
	if ($this->isNotApprovedMember==1)
	{
		echo '<span class="button">'.JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_04').'</span>';
		?></p><p><?php
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_05');
		?></p><?php
	}
	else
	{
		echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_06');
		?>
    </p>
    <?php
			// <form name='predictionRegisterForm' id='predictionRegisterForm' method='post' >
      // <form name="adminForm" id="adminForm" method="post" action="index.php"> 
      ?>
      
      <form name='predictionRegisterForm' id='predictionRegisterForm' method='post' >
			<input type='submit' name='register'		value='<?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_07') ; ?>' class='button' />
			<input type='<?php echo $visible; ?>' name='prediction_id'	value='<?php echo $this->predictionGame->id; ?>' />
			<input type='<?php echo $visible; ?>' name='user_id'			value='<?php echo $this->actJoomlaUser->id; ?>' />
			<input type='<?php echo $visible; ?>' name='approved'		value='<?php echo ( $this->predictionGame->auto_approve ) ? '1' : '0'; ?>' />
			<input type='<?php echo $visible; ?>' name='task' 			value='predictionentry.register' />
			<input type='<?php echo $visible; ?>' name='option'			value='com_sportsmanagement' />
			
			<?php echo JHTML::_('form.token'); ?>
		</form><?php
	}
	?></p><br />