<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_youtube.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;



?>

<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_YOUTUBE'); ?></h4>
<div class="panel-group" id="showyoutube">    

<div class="panel panel-default">
<div class="panel-heading">
<h4 class="panel-title">
<a data-toggle="collapse" data-parent="#showyoutube" href="#countall"><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_YOUTUBE_SHOW'); ?></a>
</h4>
</div>  
<div id="countall" class="panel-collapse collapse">
<div class="panel-body">

  
</div>
</div>
</div>
