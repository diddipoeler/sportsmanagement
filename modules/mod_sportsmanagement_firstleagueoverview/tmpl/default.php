<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_uefawertung
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>
<div class="">

			<?php
echo Text::_('MOD_SPORTSMANAGEMENT_UEFAWERTUNG_BERECHNUNG');			
			?>


	<?php

	?>

    <table class="<?php echo $params->get('table_class'); ?>">
        <thead>
  <tr>
  <td>
  </td>
<?php
  foreach( $seasonnames as $key => $value )
{
    ?>
    <td>
<?php
echo $value;
?>
</td>  
      
      <?php
  }
  
  ?>
     <td>
  </td>
    </tr>  
        </thead>
		<?php
foreach( $uefapoints as $key => $value )
{
?>    
<tr>
<td>
<?php
echo $value->team;
?>
</td>

  
 <?php
  foreach( $seasonnames as $key1 => $value1 )
{
    ?>
    <td>
<?php
echo $value->$value1;
?>
</td>  
      
      <?php
  }
  
  ?> 
  
  
  
  
  
<td>
<?php
echo $value->total;
?>
</td>
</tr>  
  
<?php    
}

		?>
    </table>
    <br/>
	<?php

	?>
</div>
