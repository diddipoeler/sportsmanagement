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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

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

<?php
  foreach( $firstleagueoverview as $key => $value )
{
    ?>
    <td>
<?php
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = $value->season_id;
$routeparameter['p']                  = $value->id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);




//echo $value->name.' '.$link;
echo HTMLHelper::link($link, $value->name);
?>
</td>  
      
      <?php
  }
  
  ?>

    </tr>  
        </thead>
	

    </table>
    <br/>
	<?php

	?>
</div>
