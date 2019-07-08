<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 * https://www.tutorialrepublic.com/codelab.php?topic=bootstrap&file=accordion
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

if ( $params->get("show_slider") )
{
$ausland = array();  
  
$zaehler = 0;  
foreach ( $list as $row )
{
$ausland[$row->country] = JSMCountries::getCountryName($row->country);  
$zaehler++;  
}  
$zaehler = 0;  
asort($ausland); 
?>  
<div class="panel-group" id="<?php echo $module->module; ?>-<?php echo $module->id.'-'.$module->id; ?>">
<?php        
foreach ( $ausland as $key => $value )
{     
if ( empty($zaehler) )  
{
$collapse = 'in'; 
$zaehler++;  	
}  
else
{
$collapse = ''; 	
}    
    
?>  
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#<?php echo $module->module; ?>-<?php echo $module->id.'-'.$module->id; ?>" href="#<?php echo $key; ?>"><?php echo JSMCountries::getCountryFlag($key).' '.$value; ?></a>
                </h4>
            </div>
<div id="<?php echo $key; ?>" class="panel-collapse collapse <?php echo $collapse; ?>">            
<div class="panel-body">            
<?php
foreach ( $list as $row ) if ($row->country == $key)
{
    
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
$link = Route::_( 'index.php?' . $query, false );  
?>                      
<div class="col-xl-2 col-lg-3 col-md-4 col-sm-4">
<a href="<?PHP echo $link;  ?>" class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
<span>
<?PHP
echo JSMCountries::getCountryFlag( $row->country );
?>
</span>
<?PHP
echo Text::_( $row->name  );
?>
</a>
<!-- </button> -->
</div>                
<?php
}                      
?>          
</div>
</div>            
</div>
<?php  
$zaehler++;   
}     
?>                      
</div>  
  
<?php  
}
else
{
$start = 1;
foreach ( $list as $row ) 
{

if ( $start == 1 )
{
?>    
<div class="row-fluid">
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
$link = Route::_( 'index.php?' . $query, false );
?>
<div class="col-sm-2">
<a href="<?PHP echo $link;  ?>" class="<?PHP echo $params->get('button_class'); ?>  btn-block" role="button">
<span>
<?PHP
echo JSMCountries::getCountryFlag( $row->country );
?>
</span>
<?PHP
echo Text::_( $row->name  );
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
}
?>
