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

defined('_JEXEC') or die('Restricted access');


if ((!isset($this->actJoomlaUser)) || ($this->actJoomlaUser->id==0))
{
	?><p><?php
	echo JText::_('You do not currently require rights in order to view the Entry Page for this Prediction Game.');
	?></p><p><?php
	echo JText::sprintf(	'If you already have an account of this site, you must first %1$1s[LogIn]%2$s by using the Joomla-LogIn-Page.',
							'<b><i><a href="index.php?option=com_user&view=login">',
							'</i></b></a>');
	?></p><p><?php
	echo JText::sprintf(	'If you are not registered on this website yet, you can do by clicking on %1$1s[Register]%2$s.',
							'<b><i><a href="index.php?option=com_user&view=register">',
							'</i></b></a>');
	?></p><?php
}
else
{
	if (!$this->isPredictionMember)
	{
		?><p><?php
		echo JText::_('To participate in this Prediction Game you need to be registered! Click the Button below to get registered with your Joomla-Account on this website.');
		?></p><form name='predictionRegisterForm' id='predictionRegisterForm' method='post' >
				<input type='submit' name='register'			value='<?php echo JText::_( 'Yes, I want to participate!' ) ; ?>' class='button' />
				<input type='hidden' name="prediction_id"		value='<?php echo $this->predictionGame->id; ?>' />
				<input type='hidden' name="user_id"				value='<?php echo $this->actJoomlaUser->id; ?>' />
				<input type='hidden' name="approved"			value='<?php echo ( $this->predictionGame->auto_approve ) ? '1' : '0'; ?>' />
				<input type='hidden' name='task' 				value='register' />
				<input type='hidden' name='option'				value='com_sportsmanagement' />
				<input type='hidden' name='controller'			value='predictionentry' />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form><p><?php
		echo JText::_( 'After approval you will be able to enter your first predictions!' );
		?></p><?php

		?><p><?php
		echo JText::sprintf('Good luck wishes... %1$s on %2$s',$this->config['ownername'],'<b>'.$this->websiteName.'</b>');
		?></p><?php
	} 
}
?><br />