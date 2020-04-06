<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_events.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<!-- START of match events -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport">
<h2>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS');
?>
</h2>		

<?php
if ($this->config['show_timeline'] && !$this->config['show_timeline_under_results'] ) {
    echo $this->loadTemplate('timeline');
}
?>
<table class="table " >
    <?php
    foreach ( $this->eventtypes as $event )
    {
        ?>
      <tr>
       <td colspan="2" class="eventid">
        <?php echo HTMLHelper::_('image', $event->icon, Text::_($event->icon), null) . Text::_($event->name); ?>
      </td>
     </tr>
     <tr>
      <td class="list">
       <dl>
        <?php echo $this->showEvents($event->id, $this->match->projectteam1_id); ?>
       </dl>
      </td>
      <td class="list">
       <dl>
        <?php echo $this->showEvents($event->id, $this->match->projectteam2_id); ?>
       </dl>
      </td>
     </tr>
        <?php
    }
    ?>
</table>
<!-- END of match events -->
</div>
<br />
