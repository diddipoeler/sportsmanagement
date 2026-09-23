<?php
/**
 * Legacy SportsManagement club icons layout retained for Joomla 5/6 compatibility.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$pictureHeight = max(1, (int) $params->get('picture_height', 30));
?>
<style>
.img-height {
    width: auto;
    height: <?php echo $pictureHeight; ?>px;
}
</style>

<table id="clubicons<?php echo (int) $module->id; ?>" class="modjsmclubicons">
    <tbody>
        <tr>
        </tr>
    </tbody>
</table>
