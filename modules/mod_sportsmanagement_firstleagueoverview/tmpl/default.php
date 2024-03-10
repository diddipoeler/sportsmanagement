<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_firstleagueoverview
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
$start = 0;
$show = '';
//echo '<pre>'.print_r($firstleagueoverview,true).'</pre>';
?>
<div class="">

<div class="">
			<?php
echo Text::_('MOD_SPORTSMANAGEMENT_FIRSTLEAGUEOVERVIEW_DESCRIPTION');			
			?>
</div>



<?php
$zaehler = 0;  
echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabs', array('active' => 'show_league_0')); // Start tab set 
foreach ($federations as $key => $value)
{  
echo HTMLHelper::_('bootstrap.addTab', 'defaulttabs', 'show_league_'.$zaehler, Text::_($value->name).HTMLHelper::_('image',Uri::root() . $value->picture, $value->name, array(' title' => $value->name, ' width' => 50))    );  
  
foreach( $firstleagueoverview as $key2 => $value2 ) if ( $value->id == $value2->federation )
{
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
$routeparameter['s']                  = $value2->season_id;
$routeparameter['p']                  = $value2->id;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

//echo $value->name.' '.$link;
echo JSMCountries::getCountryFlag($value2->country).' '.HTMLHelper::link($link, $value2->name);    
    
    
}  
  
  
echo HTMLHelper::_('bootstrap.endTab');
$zaehler++;  
}  
  
  
  
  
  
  
?>  
  
  
