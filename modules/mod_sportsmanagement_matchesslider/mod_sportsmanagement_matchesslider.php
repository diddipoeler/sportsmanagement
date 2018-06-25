<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.00
 * @file      mod_sportsmanagement_matchesslider.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage mod_sportsmanagement_matchesslider
 */ 

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
DEFINE( 'JSM_PATH','components/com_sportsmanagement' );
}

//if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
//{
//DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
//}

/**
 * soll die externe datenbank genutzt werden ?
 */
if ( JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) )
{
$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;    
}
else
{
$module->picture_server = JURI::root();    
}

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'results.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );

if (!defined('_JLMATCHLISTSLIDERMODPATH')) { define('_JLMATCHLISTSLIDERMODPATH', dirname( __FILE__ ));}
if (!defined('_JLMATCHLISTSLIDERMODURL')) { define('_JLMATCHLISTSLIDERMODURL', JURI::base().'modules/'.$module->module.'/');}
require_once (_JLMATCHLISTSLIDERMODPATH.DS.'helper.php');
require_once (_JLMATCHLISTSLIDERMODPATH.DS.'connectors'.DS.'sportsmanagement.php');

// welche joomla version ?
if(version_compare(JVERSION,'3.0.0','ge')) 
{
JHTML::_('behavior.framework', true);
}
else
{
JHTML::_('behavior.mootools');
}

$app = JFactory::getApplication();
$jinput = $app->input;
$doc = JFactory::getDocument();
$doc->addScript( _JLMATCHLISTSLIDERMODURL.'assets/js/jquery.simplyscroll.js' );
//$doc->addStyleSheet(_JLMATCHLISTMODURL.'tmpl/'.$template.'/mod_sportsmanagement_matchesslider.css');
$doc->addStyleSheet(_JLMATCHLISTSLIDERMODURL.'assets/css/'.$module->module.'.css');


JHTML::_('behavior.tooltip');
//$doc->addScriptDeclaration('
//(function($) {
//	$(function() { //on DOM ready
//		$("#scroller").simplyScroll({
//			customClass: \'custom\',
//			direction: \'backwards\',
//			pauseOnHover: false,
//			frameRate: 20,
//			speed: 2
//		});
//	});
//})(jQuery);
//  ');

//$mod = new MatchesSliderSportsmanagementConnector($params, $module->id, $match_id);
//$matches = $mod->getMatches();

$config = array();
$slidermatches = array();

$cfg_which_database = $jinput->getInt('cfg_which_database',0);
$s = $jinput->getInt('s',0);
$projectid = $jinput->getInt('p',0);

if ( !$projectid )
{
$cfg_which_database = $params->get('cfg_which_database');
$s = $params->get('s');
    
    foreach( $params->get('project') as $key => $value )
    {
        //$projectid = $value;
        sportsmanagementModelProject::$projectid = (int)$value;
        sportsmanagementModelProject::$cfg_which_database = $cfg_which_database;
        sportsmanagementModelResults::$projectid = $projectid;
        sportsmanagementModelResults::$cfg_which_database = $cfg_which_database;
        $matches = sportsmanagementModelResults::getResultsRows(0,0,$config,$params,$cfg_which_database);
        //$slidermatches[] = $matches;
        $slidermatches = array_merge($matches);
    }
    
}    
else
{
sportsmanagementModelProject::$projectid = $projectid;
sportsmanagementModelProject::$cfg_which_database = $cfg_which_database;
sportsmanagementModelResults::$projectid = $projectid;
sportsmanagementModelResults::$cfg_which_database = $cfg_which_database;
$matches = sportsmanagementModelResults::getResultsRows(0,0,$config,$params,$cfg_which_database);
$slidermatches = array_merge($matches);
}

foreach( $slidermatches as $match )
{
$routeparameter = array();
$routeparameter['cfg_which_database'] = $cfg_which_database;
$routeparameter['s'] = $s;
$routeparameter['p'] = $match->project_slug;

switch ( $params->get('p_link_func') )
{
case 'results':
$routeparameter['r'] = $match->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
break;
case 'ranking':
$routeparameter = array();
$routeparameter['cfg_which_database'] = $cfg_which_database;
$routeparameter['s'] = $s;
$routeparameter['p'] = $match->project_slug;
$routeparameter['type'] = 0;
$routeparameter['r'] = $match->round_slug;
$routeparameter['from'] = 0;
$routeparameter['to'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);
break;
case 'resultsrank':
$routeparameter = array();
$routeparameter['cfg_which_database'] = $cfg_which_database;
$routeparameter['s'] = $s;
$routeparameter['p'] = $match->project_slug;
$routeparameter['r'] = $match->round_slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = '';
$routeparameter['layout'] = '';
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking',$routeparameter);
break;
}

}



?>
<div class="<?php echo $params->get('moduleclass_sfx'); ?>" id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module));
?>
</div>