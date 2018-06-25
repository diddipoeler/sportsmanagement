<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      default_info.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionentry
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