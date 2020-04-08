<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       deafult_explanation.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
$config = &$this->tableconfig;

$columns = explode(",", $config['ordered_columns']);
$column_names = explode(',', $config['ordered_columns_names']);
?>
<div class="<?php echo $this->divclassrow;?>" id="explanation">
<br />
<table class="table">
	<tr class="explanation">
		<td>
			<?php
			// $d = 0;
			foreach ($columns as $k => $column)
			{
				if (empty($column_names[$k]))
				{
					$column_names[$k] = '???';
				}

				$c = strtoupper(trim($column));
				$c = "COM_SPORTSMANAGEMENT_" . $c;
				echo "<td class=\"\">";
				echo $column_names[$k] . " = " . Text::_($c);
				echo "</td>";

				// $d=(1-$d);
			}
			?>
		</td>
	</tr>
</table>
</div>
