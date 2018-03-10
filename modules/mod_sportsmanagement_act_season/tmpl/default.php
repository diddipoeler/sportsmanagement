<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$start = 1;
foreach ( $list as $row )
{

if ( $start == 1 )
{
?>    
<div class="row-">
<?PHP    
}    
$createroute = array("option" => "com_sportsmanagement",
	"view" => "ranking",
        "cfg_which_database" => 0,
        "s" => 0,
	"p" => $row->project_slug,
        "type" => 0,
        "r" => $row->roundcode,
        "from" => 0,
        "to" => 0,
        "division" => 0, );

$query = sportsmanagementHelperRoute::buildQuery( $createroute );
$link = JRoute::_( 'index.php?' . $query, false );
?>
<div class="col-sm-2">
<a href="<?PHP echo $link;  ?>" class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
<span>
<?PHP
echo JSMCountries::getCountryFlag( $row->country );
?>
</span>
<?PHP
echo JText::_( $row->name  );
?>
</a>
<!-- </button> -->
</div>
<?PHP
$start++;
if ( $start == 7 )
{
$start = 1;  
?>
</div>
<?PHP
}
    
}
?>
