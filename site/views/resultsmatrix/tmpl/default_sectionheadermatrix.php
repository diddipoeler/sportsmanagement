<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_sectionheadermatrix.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage resultsmatrix
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
?>

<!-- START: Contentheading -->
<table class="contentpaneopen" width="100%">
    <tr>
        <td class="contentheading">
            <?php
            echo Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE' );
            ?>
        </td>
    </tr>
</table>
<br />
    <!-- END: Contentheading -->