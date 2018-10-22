<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      mod_sportsmanagement_ajax_top_navigation_menu.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_ajax_top_navigation_menu
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
BaseDatabaseModel::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');


// Reference global application object
$app = Factory::getApplication();
// JInput object
$jinput = $app->input;

$document = Factory::getDocument();
/**
 * sprachdatei aus dem backend laden
 */
$langtag = Factory::getLanguage();

$lang = Factory::getLanguage();
$extension = 'com_sportsmanagement';
$base_dir = JPATH_ADMINISTRATOR;
$language_tag = $langtag->getTag();
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);



// get helper
require_once (dirname(__FILE__).DS.'helper.php');

HTMLHelper::_('behavior.tooltip');

$helper = new modSportsmanagementAjaxTopNavigationMenuHelper($params);

$points = $helper->getFederations();
$tab_points = array();


foreach( $points as $row )
{
$tab_points[] = $row->name;
    
}


$tab_points[] = 'NON';

$ende_if = false;
$league_assoc_id = 0;
$sub_assoc_parent_id = 0;
$sub_sub_assoc_parent_id = 0;

$project_id = $jinput->get('p', 0, 'INT'); 
$team_id = $jinput->get('tid', 0, 'INT'); 
$division_id = $jinput->get('division', 0, 'INT'); 

$helper->setProject( $project_id, $team_id, $division_id );
$league_id  = $helper->getLeagueId();
$country_id  = $helper->getProjectCountry($project_id);
$league_assoc_id  = $helper->getLeagueAssocId();
$sub_assoc_parent_id  = $helper->getAssocParentId($league_assoc_id);
$sub_sub_assoc_parent_id  = $helper->getAssocParentId($sub_assoc_parent_id);

if ( !empty($sub_sub_assoc_parent_id) && !$ende_if )
{
$assoc_id  = $sub_sub_assoc_parent_id;
$subassoc_id = $sub_assoc_parent_id;
$subsubassoc_id = $league_assoc_id;
$ende_if = true;
}

if ( !empty($sub_assoc_parent_id) && !$ende_if )
{
$assoc_id = $sub_assoc_parent_id;
$subassoc_id = $league_assoc_id;
$ende_if = true;
}    

if ( !empty($league_assoc_id)  && !$ende_if )
{
$assoc_id = $league_assoc_id;
$ende_if = true;
}

foreach( $points as $row )
{
$federationselect[$row->name] = $helper->getFederationSelect($row->name,$row->id);
?>
<script>
console.log('tabpoints = ' + '<?php echo $row->name;?>' );
</script>
<?php
}
$federationselect['NON'] = $helper->getFederationSelect('NON',0);

$country_federation = $helper->getCountryFederation($country_id);

if ( !$country_federation )
{
    $country_federation = 'NON';
}

?>
<script>
console.log('project_id = ' + '<?php echo $project_id;?>' );
console.log('country_id = ' + '<?php echo $country_id;?>' );
console.log('country_federation = ' + '<?php echo $country_federation;?>' );

console.log('league_id = ' + '<?php echo $league_id;?>' );
console.log('league_assoc_id = ' + '<?php echo $league_assoc_id;?>' );
console.log('sub_assoc_parent_id = ' + '<?php echo $sub_assoc_parent_id;?>' );
console.log('sub_sub_assoc_parent_id = ' + '<?php echo $sub_sub_assoc_parent_id;?>' );

console.log('assoc_id = ' + '<?php echo $assoc_id ;?>' );
console.log('subassoc_id = ' + '<?php echo $subassoc_id ;?>' );
console.log('subsubassoc_id = ' + '<?php echo $subsubassoc_id ;?>' );

</script>
<?php
// Build the script.
$script = array();
$script[] = "\n";       
$script[] = "jQuery(document).ready(function ($){";
foreach( $points as $row )
{
// regionalverband
$script[] = "$('#jlamtopfederation".$row->name.$module->id."').change(function(){";
$script[] = "var value = $('#jlamtopfederation".$row->name.$module->id."').val();";
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getcountryassoc&country=' + value;";
$script[] = "console.log('country value = ' + value );";
$script[] = "console.log('country url = ' + url );";
$script[] = "$.ajax({";
$script[] = "url: url,";
$script[] = "dataType: 'json',";
$script[] = "type : 'POST'";
$script[] = "}).done(function(data) {";
$script[] = "$('#jlamtopassoc".$row->name.$module->id." option').each(function() {";
$script[] = "jQuery('select#jlamtopassoc".$row->name.$module->id." option').remove();";
$script[] = "console.log(data);";
$script[] = "});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";
$script[] = "							jQuery('#jlamtopassoc".$row->name.$module->id."').append(option);";
$script[] = "						});";
$script[] = "						$('#jlamtopassoc".$row->name.$module->id."').trigger('liszt:updated');";
$script[] = "					});";

$script[] = "var value = $('#jlamtopfederation".$row->name.$module->id."').val();";
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getAssocLeagueSelect&country=' + value;";
$script[] = "$.ajax({";
$script[] = "url: url,";
$script[] = "dataType: 'json',";
$script[] = "type : 'POST'";
$script[] = "}).done(function(data) {";
$script[] = "$('#jlamtopleagues".$row->name.$module->id." option').each(function() {";
$script[] = "jQuery('select#jlamtopleagues".$row->name.$module->id." option').remove();";
$script[] = "console.log(data);";
$script[] = "});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";
$script[] = "							jQuery('#jlamtopleagues".$row->name.$module->id."').append(option);";
$script[] = "						});";
$script[] = "						$('#jlamtopleagues".$row->name.$module->id."').trigger('liszt:updated');";
$script[] = "					});";



$script[] = "});";

// landesverband
$script[] = "$('#jlamtopassoc".$row->name.$module->id."').change(function(){";
$script[] = "var value = $('#jlamtopassoc".$row->name.$module->id."').val();";
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCountrySubAssocSelect&assoc_id=' + value;";
$script[] = "console.log('assoc_id value = ' + value );";
$script[] = "console.log('assoc_id url = ' + url );";
$script[] = "$.ajax({";
$script[] = "url: url,";
$script[] = "dataType: 'json',";
$script[] = "type : 'POST'";
$script[] = "}).done(function(data) {";
$script[] = "$('#jlamtopsubassoc".$row->name.$module->id." option').each(function() {";
$script[] = "jQuery('select#jlamtopsubassoc".$row->name.$module->id." option').remove();";
$script[] = "console.log(data);";
$script[] = "});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";
$script[] = "							jQuery('#jlamtopsubassoc".$row->name.$module->id."').append(option);";
$script[] = "						});";
$script[] = "						$('#jlamtopsubassoc".$row->name.$module->id."').trigger('liszt:updated');";
$script[] = "					});";
$script[] = "});";

// kreisverband
$script[] = "$('#jlamtopsubassoc".$row->name.$module->id."').change(function(){";
$script[] = "var value = $('#jlamtopsubassoc".$row->name.$module->id."').val();";
$script[] = "var url = 'index.php?option=com_sportsmanagement&format=json&tmpl=component&task=ajax.getCountrySubSubAssocSelect&subassoc_id=' + value;";
$script[] = "console.log('subassoc_id value = ' + value );";
$script[] = "console.log('subassoc_id url = ' + url );";
$script[] = "$.ajax({";
$script[] = "url: url,";
$script[] = "dataType: 'json',";
$script[] = "type : 'POST'";
$script[] = "}).done(function(data) {";
$script[] = "$('#jlamtopsubsubassoc".$row->name.$module->id." option').each(function() {";
$script[] = "jQuery('select#jlamtopsubsubassoc".$row->name.$module->id." option').remove();";
$script[] = "console.log(data);";
$script[] = "});";
$script[] = "";
$script[] = "						$.each(data, function (i, val) {";
$script[] = "							var option = $('<option>');";
$script[] = "							option.text(val.text).val(val.value);";
$script[] = "							jQuery('#jlamtopsubsubassoc".$row->name.$module->id."').append(option);";
$script[] = "						});";
$script[] = "						$('#jlamtopsubsubassoc".$row->name.$module->id."').trigger('liszt:updated');";
$script[] = "					});";
$script[] = "});";

}
$script[] = "});";     
    
// Add the script to the document head.
Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

// regionalverband
if ( $country_id )
{
$countryassocselect[$country_federation]['assocs'] = $helper->getCountryAssocSelect($country_id);
$leagueselect[$country_federation]['leagues'] = $helper->getAssocLeagueSelect($country_id,$assoc_id );
}
// landesverband
if ( $assoc_id )
{
$countrysubassocselect[$country_federation]['assocs'] = $helper->getCountrySubAssocSelect($assoc_id);
$leagueselect[$country_federation]['leagues'] = $helper->getAssocLeagueSelect($country_id,$assoc_id);
}
// kreisverband
if ( $subassoc_id )
{
$countrysubsubassocselect[$country_federation]['subassocs'] = $helper->getCountrySubSubAssocSelect($subassoc_id);
$leagueselect[$country_federation]['leagues'] = $helper->getAssocLeagueSelect($country_id,$subassoc_id);
}

if ( $subsubassoc_id )
{
$countrysubsubsubassocselect[$country_federation]['subsubassocs'] = $helper->getCountrySubSubAssocSelect($subsubassoc_id);
$leagueselect[$country_federation]['leagues'] = $helper->getAssocLeagueSelect($country_id,$subsubassoc_id);
}

if ( $league_id )
{
$projectselect[$country_federation]['projects']	= $helper->getProjectSelect($league_id);
}

if ( $project_id )
{
$helper->setProject($project_id,$team_id,$division_id);
$divisionsselect[$country_federation]['divisions'] = $helper->getDivisionSelect($project_id);
$projectselect[$country_federation]['teams'] = $helper->getTeamSelect($project_id);
}







  




      
if (!defined('JLTOPAM_MODULESCRIPTLOADED')) 
{
/*
if(version_compare(JVERSION,'3.0.0','ge')) 
{    
	$document->addScript( Uri::base().'modules/'.$module->module.'/js/'.$module->module.'.js' );
}    
else
{
    $document->addScript( Uri::base().'modules/'.$module->module.'/js/'.$module->module.'_2.js' );
}
*/	
    //	$document->addScriptDeclaration(';
//    var ajaxmenu_baseurl=\''. Uri::base() . '\';
//      ');
	$document->addStyleSheet(Uri::base().'modules/'.$module->module.'/css/'.$module->module.'.css');
	$document->addStyleSheet(Uri::base().'modules/'.$module->module.'/css/mod_sportsmanagement_ajax_top_navigation_tabs_sliders.css');
	define('JLTOPAM_MODULESCRIPTLOADED', 1);
}

if(version_compare(JVERSION,'3.0.0','ge')) 
{    
$layout = 'default';
}    
else
{
$layout = 'default_2';
}	

?>           
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(ModuleHelper::getLayoutPath($module->module,$layout));
?>
</div>
