<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deaful_pagnavt.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<!-- matchdays pageNav -->
<div class="row" id="pagnav">
    <br/>
    <table class="table">
        <tr>
            <td>
				<?php
				if (!empty($this->rounds))
				{
					$pageNavigation = "<div class='pagenav'>";
					$pageNavigation .= "</div>";
					echo $pageNavigation;
				}
				?>
            </td>
        </tr>
    </table>
</div>
<!-- matchdays pageNav END -->
