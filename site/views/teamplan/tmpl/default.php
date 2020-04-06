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
 * @subpackage teamplan
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="<?php echo $this->divclasscontainer;?>" id="teamplan">
<?php
if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO ) {
    echo $this->loadTemplate('debug');
}
        
if (!empty($this->project->id) ) {
    echo $this->loadTemplate('projectheading');

    if ($this->config['show_sectionheader'] ) {
        echo $this->loadTemplate('sectionheader');
    }

    if ($this->config['show_plan_layout'] == 'plan_default' ) {
        echo $this->loadTemplate('plan');
    }
    else if($this->config['show_plan_layout'] == 'plan_sorted_by_date') {
        echo $this->loadTemplate('plan_sorted_by_date');
    }
      
}
else
{
    echo '<p>'.Text::_('COM_SPORTSMANAGEMENT_ERROR_PROJECTMODEL_PROJECT_IS_REQUIRED').'</p>';
}
echo $this->loadTemplate('jsminfo');
?>
</div>
