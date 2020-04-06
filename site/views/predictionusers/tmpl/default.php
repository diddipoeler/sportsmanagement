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
 * @subpackage predictionusers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="<?php echo $this->divclasscontainer;?>" id="defaultpredictionusers">
<?php
    echo $this->loadTemplate('predictionheading');
if ($this->predictionMember->pmID > 0) {
    echo $this->loadTemplate('sectionheader');
    if (($this->predictionMember->show_profile) || ($this->allowedAdmin) || ($this->predictionMember->user_id==$this->actJoomlaUser->id)) {
        echo $this->loadTemplate('info');
          
        if ($this->config['show_flash_statistic_points']) {
            echo $this->loadTemplate('pointsflashchart');
        }
        if ($this->config['show_flash_statistic_ranks']) {
            echo $this->loadTemplate('rankflashchart');  
        }
    }
    else
    {
        echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_MEMBER_NO_PROFILE_SHOW');
    }
}
else
{
    ?><h3><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_EXISTING_MEMBER'); ?></h3><?php
}
  
    echo $this->loadTemplate('jsminfo');
?>
</div>
