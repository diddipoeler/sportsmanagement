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
 * @subpackage matrix
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer;?>" id="matrix" >
<?php
if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO ) {
    echo $this->loadTemplate('debug');
}
echo $this->loadTemplate('projectheading');

if ($this->config['show_matrix'] ) {
  
    if ($this->config['show_sectionheader'] ) {
        echo $this->loadTemplate('sectionheader');
    }

    if(isset($this->divisions) && count($this->divisions) > 1) {
        echo $this->loadTemplate('matrix_division').'<br />';
    }
    else
    {
        if (isset($this->config['show_matrix_russia']) ) {
            if ($this->config['show_matrix_russia'] ) {
                echo $this->loadTemplate('matrix_russia');
            } 
            else
            {
                echo $this->loadTemplate('matrix');
            }
        }
        else
        {
            echo $this->loadTemplate('matrix');
        }
    }
  
}

if ($this->config['show_help'] ) {
    echo $this->loadTemplate('hint');
}

echo $this->loadTemplate('jsminfo');
?>
</div>
