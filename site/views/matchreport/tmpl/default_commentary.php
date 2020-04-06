<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_commentary.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

?>
<!-- START of match commentary -->
<?php

if (!empty($this->matchcommentary)) {
    ?>
    <table class="table table-responsive" >
        <tr>
            <td class="contentheading">
                <?php
                echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY');
                ?>
            </td>
        </tr>
    </table>
  
<table class="table table-responsive" >
    <?php
    foreach ( $this->matchcommentary as $commentary )
    {
              
                echo $farbe = ($farbe == '<tr class="weiss">') ? '<tr class="grau">' : '<tr class="weiss">';
                ?>
              
              
       <td class="list">
        <dl>
            <?php echo $commentary->event_time; ?>
        </dl>
       </td>
                    <td class="list">
        <dl>
            <?php
                            echo HTMLHelper::image(Uri::root().'media/com_sportsmanagement/jl_images/discuss_active.gif', 'Kommentar', array(' title' => 'Kommentar'));
                            ?>
        </dl>
       </td>
       <td class="list">
        <dl>
            <?php echo $commentary->notes; ?>
        </dl>
       </td>
      </tr>
        <?php
    }
    ?>
</table>      
<?PHP  
}  

?>
