<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_results_all.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allprojectrounds
 */

defined('_JEXEC') or die('Restricted access');

?>

<!-- Main START -->
<div class="row-fluid" id="">

<!-- content -->
<?php

    ?>
    <table class="<?php echo $this->tableclass;?>">
        <tr>
            <td class="">
                <?php
                    //get the division name from the first team of the division
                    echo $this->content;
                ?>
            </td>
        </tr>
    </table>
  
    <?php

    ?>
<!-- all results END -->

</div>

