<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_hint.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="row" id="hint">
    <table class="<?PHP echo $this->config['table_class']; ?>">
        <tr>
            <td align="left">
                <span class="<?PHP echo $this->config['label_class_teams']; ?>">
                    <?php echo JText :: _('COM_SPORTSMANAGEMENT_RANKING_HINT'); ?>
                </span>
            </td>
        </tr>
    </table>
</div> 
