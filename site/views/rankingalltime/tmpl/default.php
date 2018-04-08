<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rankingalltime
 */

defined('_JEXEC') or die('Restricted access');


if ( $this->show_debug_info )
{
echo 'this->config<br /><pre>~' . print_r($this->config,true) . '~</pre><br />';    
echo 'this->tableconfig<br /><pre>~' . print_r($this->tableconfig,true) . '~</pre><br />';
}   
 
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view

//$templatesToLoad = array(
//    'projectheading',
//    'backbutton',
//    'footer');
//sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
	<?php

echo $this->loadTemplate('projectheading');
echo $this->loadTemplate('ranking');

?>
</div>