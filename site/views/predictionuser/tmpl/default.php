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

defined( '_JEXEC' ) or die( 'Restricted access' );

if ( $this->show_debug_info )
{
echo 'this->predictionMember<br /><pre>~' . print_r($this->predictionMember,true) . '~</pre><br />';
}

$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'predictionheading' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'backbutton' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'footer' . DS . 'tmpl' );

?><div class='joomleague'><?php
	echo $this->loadTemplate('predictionheading');
	if ($this->predictionMember->pmID > 0)
	{
		echo $this->loadTemplate('sectionheader');
		if (($this->predictionMember->show_profile) || ($this->allowedAdmin) || ($this->predictionMember->user_id==$this->actJoomlaUser->id))
		{
			echo $this->loadTemplate('info');
			
			if ($this->config['show_flash_statistic_points']){
				echo $this->loadTemplate('pointsflashchart');
			}
			if ($this->config['show_flash_statistic_ranks']){
				echo $this->loadTemplate('rankflashchart');	
			}
		}
		else
		{
			echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_MEMBER_NO_PROFILE_SHOW');
		}
	}
	else
	{
		?><h3><?php echo JText::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_EXISTING_MEMBER'); ?></h3><?php
	}
	echo '<div>';
		//backbutton
		echo $this->loadTemplate('backbutton');
		// footer
		echo $this->loadTemplate('footer');
	echo '</div>';
?></div>