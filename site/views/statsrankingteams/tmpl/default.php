<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage statsrankingteams
 */
defined('_JEXEC') or die('Restricted access');
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="">
    <?php 
    if ($this->config['show_sectionheader'] == 1) { 
        echo $this->loadTemplate('sectionheader');
    }
        
    echo $this->loadTemplate('projectheading');
    echo $this->loadTemplate('stats');
    echo $this->loadTemplate('jsminfo');    
    ?>
    
</div>
