<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_description.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage staff
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<!-- Person description START -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="staff">
<?php
    $description = "";
if (!empty($this->inprojectinfo->notes) ) {
    echo "<!-- Person Description -->";
    $description = $this->inprojectinfo->notes;
} else {
    if (!empty($this->person->notes) ) {
        echo "<!-- Team Staff Description -->";
        $description = $this->person->notes;
    }
}

if ($description != '' ) {
    ?>
        <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INFO'); ?></h2>
        <table width="96%" align="center" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
    &nbsp;
                </td>
            </tr>
            <tr>
                <td>
    <?php
    $description = HTMLHelper::_('content.prepare', $description);
    echo stripslashes($description);
    ?>
                </td>
            </tr>
        </table>
        <br /><br />
    <?php
}
?>
</div>
<!-- Person description END -->
