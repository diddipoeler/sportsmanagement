<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_colorlegend.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
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
    <div class="row table-responsive" id="colorlegend">
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
