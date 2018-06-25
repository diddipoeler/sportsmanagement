<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_rankingnav.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');
?>

<form name="adminForm" id="adminForm" method="post"	action="<?php echo $this->action; ?>">
    <table class="table">
        <tr>
            <?php
            echo "<td>" . JHtml::_('select.genericlist', $this->lists['type'], 'type', 'class="inputbox" size="1"', 'value', 'text', $this->type) . "</td>";
            echo "<td>" . JHtml::_('select.genericlist', $this->lists['frommatchday'], 'from', 'class="inputbox" size="1"', 'value', 'text', $this->from) . "</td>";
            echo "<td>" . JHtml::_('select.genericlist', $this->lists['tomatchday'], 'to', 'class="inputbox" size="1"', 'value', 'text', $this->to) . "</td>";
            ?>
            <td>
                <input type="submit" class="<?PHP echo $this->config['button_style']; ?>" name="reload View"
                       value="<?php echo JText::_('COM_SPORTSMANAGEMENT_RANKING_FILTER'); ?>">

                <input type="hidden" name="p" value="<?php echo sportsmanagementModelRanking::$projectid; ?>" />
                <input type="hidden" name="r" value="<?php echo sportsmanagementModelRanking::$round; ?>" />
                <input type="hidden" name="s" value="<?php echo sportsmanagementModelRanking::$season; ?>" />
                <input type="hidden" name="cfg_which_database" value="<?php echo sportsmanagementModelProject::$cfg_which_database; ?>" />	
            </td>
        </tr>
    </table>
<?php echo JHtml::_('form.token'); ?>
</form>
<br />

