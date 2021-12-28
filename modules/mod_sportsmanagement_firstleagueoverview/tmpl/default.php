<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_firstleagueoverview
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;



//echo '<pre>'.print_r($firstleagueoverview,true).'</pre>';





?>
<div class="">

			<?php
echo Text::_('MOD_SPORTSMANAGEMENT_FIRSTLEAGUEOVERVIEW_DESCRIPTION');			
			?>


	<?php

	?>

    <table class="<?php echo $params->get('table_class'); ?>">
        <thead>
  <tr>
  <td>
  </td>
<?php
  foreach( $firstleagueoverview as $key => $value )
{
    ?>
    <td>
<?php
echo $value->name;
?>
</td>  
      
      <?php
  }
  
  ?>
     <td>
  </td>
    </tr>  
        </thead>
	

    </table>
    <br/>
	<?php

	?>
</div>
