<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deaful_pagnavt.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');
?>
<!-- matchdays pageNav -->
<br />
<table class="table">
    <tr>
        <td>
            <?php
            if (!empty($this->rounds)) {
                $pageNavigation = "<div class='pagenav'>";
                $pageNavigation .= "</div>";
                echo $pageNavigation;
            }
            ?>
        </td>
    </tr>
</table>
<!-- matchdays pageNav END -->