<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @file       deafult_colorlegend.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');
?>
<!-- colors legend START -->
<?php
if (!isset($this->tableconfig['show_colors_legend'])) {
    $this->tableconfig['show_colors_legend'] = 1;
}
if ($this->tableconfig['show_colors_legend']) {
    ?>
    <br />
    <div class="<?php echo $this->divclassrow;?> table-responsive" id="colorlegend">
        <table class="table">
            <tr>
                <?php
                sportsmanagementHelper::showColorsLegend($this->colors, $this->divisions);
                ?>
            </tr>
        </table>
    </div>
    <?php
}
?>
<!-- colors legend END -->
