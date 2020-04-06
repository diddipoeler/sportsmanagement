<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       deafult_description.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage teaminfo
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

    <?php
    // Show team-description if defined.
    if (!isset($this->team->notes) ) {
        $description = "";
    }
    else
    {
        $description = $this->team->notes;
    }

    if(trim($description != "") ) {
        ?>
      <div class="<?php echo $this->divclassrow;?> table-responsive" id="teamdescription">
        <br />
        <table class="table">
         <tr class="sectiontableheader">
       <td>
        <?php
        echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION');
        ?>
          </td>
         </tr>
        </table>

        <table class="table">
         <tr>
          <td>
        <?php
        $description = HTMLHelper::_('content.prepare', $description);
        echo stripslashes($description);
        ?>
          </td>
         </tr>
        </table>
                </div>
        <?php
    }
    ?>
    <br />