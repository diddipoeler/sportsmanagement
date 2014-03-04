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
?>

<?php
$this->_addPath( 'template', JPATH_COMPONENT . DS .'views' . DS . 'predictionheading' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'backbutton' . DS . 'tmpl' );
$this->_addPath( 'template', JPATH_COMPONENT . DS . 'views' . DS . 'footer' . DS . 'tmpl' );

?>
<div class='joomleague'>
<?php

echo $this->loadTemplate('predictionheading');
echo $this->loadTemplate('sectionheader');

echo $this->loadTemplate('ranking');

if ($this->config['show_all_user_google_map'])
{
echo $this->loadTemplate('maps');
}

//if ($this->config['show_matchday_pagenav']){echo $this->loadTemplate('matchday_nav');}

if ($this->config['show_help']){echo $this->loadTemplate('show_help');}

// echo '<div>';
// echo $this->pagination->getListFooter();
// echo '</div>';

echo '<div>';
//backbutton
echo $this->loadTemplate('backbutton');
// footer
echo $this->loadTemplate('footer');
echo '</div>';
	?>
</div>
