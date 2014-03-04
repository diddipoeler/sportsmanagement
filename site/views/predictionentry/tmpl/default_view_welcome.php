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

?><p><?php
	echo JText::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_WELCOME_INFO_01');
	?></p><p><?php
		echo JText::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_WELCOME_INFO_02',$this->config['ownername'],'<b>' . $this->websiteName . '</b>');
	?></p><hr><br />