<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_explanation.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */
defined('_JEXEC') or die('Restricted access');

$config = &$this->tableconfig;

$columns = explode(",", $config['ordered_columns']);
$column_names = explode(',', $config['ordered_columns_names']);
?>

<br />
<table class="table">
    <tr class="explanation">
        <td>
            <?php
            //$d = 0;
            foreach ($columns as $k => $column) {
                if (empty($column_names[$k])) {
                    $column_names[$k] = '???';
                }
                $c = strtoupper(trim($column));
                $c = "COM_SPORTSMANAGEMENT_" . $c;
                echo "<td class=\"\">";
                echo $column_names[$k] . " = " . JText::_($c);
                echo "</td>";
                //$d=(1-$d);
            }
            ?>
        </td>
    </tr>
</table>