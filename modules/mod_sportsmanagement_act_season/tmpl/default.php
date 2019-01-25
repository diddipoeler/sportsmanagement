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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

//echo '<pre>'.print_r($list,true).'</pre>';

if ( $params->get("show_slider") )
{
$ausland = array();  
  
$zaehler = 0;  
foreach ( $list as $row )
{
$ausland[$row->country] = JSMCountries::getCountryName($row->country);  
if ( empty($zaehler) )  
{
// Define slides options
        $slidesOptions = array(
            "active" => "slide".$row->country."_id" // It is the ID of the active tab.
        );   
}
$zaehler++;  
}  
$zaehler = 0;  
?>  
<div class="panel-group" id="accordion">
<?php        
foreach ( $ausland as $key => $value )
{     
?>  
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $value; ?>"><?php echo JSMCountries::getCountryFlag($key).' '.$value; ?></a>
                </h4>
            </div>
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
if ( empty($zaehler) )  
{
//$collapse = 'in';  
}  
?>                      
            <div id="<?php echo $value; ?>" class="panel-collapse collapse <?php echo $collapse; ?>">
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
            </div>
<?php
}                      
?>                      
        </div>
<?php  
$zaehler++;   
}     
?>                      
    </div>  
  
<?php  
//echo '<pre>'.print_r($ausland,true).'</pre>';  
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
