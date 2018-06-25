<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_items.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage hitlist
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
?>




