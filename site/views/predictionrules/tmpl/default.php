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

defined('_JEXEC') or die('Restricted access');

$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'predictionheading' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'backbutton' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'footer' . DS . 'tmpl' );

?><div class='joomleague'><?php

	echo $this->loadTemplate('predictionheading');
	echo $this->loadTemplate('sectionheader');
	echo $this->loadTemplate('info');

	echo '<div>';
		//backbutton
		echo $this->loadTemplate('backbutton');
		// footer
		echo $this->loadTemplate('footer');
	echo '</div>';

?></div>