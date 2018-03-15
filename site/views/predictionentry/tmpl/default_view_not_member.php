<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined('_JEXEC') or die(JText::_('Restricted access'));

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
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
      
      <form name='predictionRegisterForm' id='predictionRegisterForm' method='post' action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&task=predictionentry.register'); ?>" >
			<input type='submit' name='register' value='<?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_07') ; ?>' class='button' />
			<input type='<?php echo $visible; ?>' name='prediction_id' value='<?php echo $this->predictionGame->id; ?>' />
			<input type='<?php echo $visible; ?>' name='user_id' value='<?php echo $this->actJoomlaUser->id; ?>' />
			<input type='<?php echo $visible; ?>' name='approved' value='<?php echo ( $this->predictionGame->auto_approve ) ? '1' : '0'; ?>' />
			<input type='<?php echo $visible; ?>' name='task' value='predictionentry.register' />
			<input type='<?php echo $visible; ?>' name='option'	value='com_sportsmanagement' />
			
			<?php echo JHTML::_('form.token'); ?>
		</form><?php
	}
	?></p><br />