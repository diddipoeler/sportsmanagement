<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default2.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 */

defined('_JEXEC') or die('Restricted access');

?>

<table id="clubicons<?php echo $module->id;?>" cellpadding="0" cellspacing="0"
    class="modjsmclubicons">
    <tr>
<?php
        $cnt = 0;
        $after = 0;
        $rest = count($data);
foreach($data->ranking AS $k => $ficken)
{
    $val = $data->teams[$k];
    $append = ($params->get('teamlink', 0) == 5 AND $params->get('newwindow', 0) == 1) ?
                ' target="_blank"': '';
    echo '<td class="modjlclubicons">';
    if (!empty($val['link'])) { echo '<a href="'.$val['link'].'"'.$append.'>';
    }
    echo $val['logo'];
    if (!empty($val['link'])) { echo '</a>';
    }
    echo '</td>';
    $cnt++;
    $modulo = intval($cnt%$params->get('iconsperrow', 20));
    if ($modulo == 0) {
        echo '</tr><tr>';
        $rest = $rest - $params->get('iconsperrow', 20);
        if ($rest < $params->get('iconsperrow', 20)) {
            $before = floor(($params->get('iconsperrow', 20)-$rest) / 2);
            $after = $params->get('iconsperrow', 20) - $before - $rest;
            for ($x=0;$x < $before;$x++) { echo '<td>&nbsp;</td>';
            }
        }
    }
    if ($cnt == count($data) && $after > 0) {
        for ($x=0;$x < $after;$x++) { echo '<td>&nbsp;</td>';
        }
    }

}
?>
    </tr>
</table>
