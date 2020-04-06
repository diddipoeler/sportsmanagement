<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @file       deafult_projectinfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->divclassrow;?>" id="projectinfo">
<h4>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_PROJECTINFO'); ?>
</h4>

<table class="<?PHP echo $this->config['table_class']; ?>">
    <tr>
        <td align="left">
            <span class="<?PHP echo $this->config['label_class_teams']; ?>">
                <?php
                if ($this->projectinfo) {
                    echo $this->projectinfo;
                } else {
                    ?>
                    <div class="alert alert-warning" role="alert">
                    <?PHP
                    echo Text::_('COM_SPORTSMANAGEMENT_NO_RANKING_PROJECTINFO');
                    ?>
                    </div>
                    <?PHP
                }
                    ?>
            </span>
        </td>
    </tr>
</table>
</div>
