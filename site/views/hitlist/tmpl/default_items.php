<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage hitlist
 * @file       default_items.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

?>

<?PHP
foreach ($this->model_hits as $key => $values)
{
?>
<table class="<?php echo $this->tableclass;?>">
<tr class="">
<th class="" colspan="2"><?php echo $key;?></th>
</tr>
<?PHP
foreach ($values as $row)
	{
?>
<tr class="">
<td class=""><?php echo $row->name;?></td>
<td class=""><?php echo $row->hits;?></td>
</tr>  
<?PHP
}
?>
</table>
<?PHP
}
