<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );
if ( !defined('JSM_PATH') )	{	DEFINE( 'JSM_PATH','components/com_sportsmanagement' );}

require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'project.php' );
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'results.php');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php' );

if (!defined('_JLMATCHLISTSLIDERMODPATH')) { define('_JLMATCHLISTSLIDERMODPATH', dirname( __FILE__ ));}
if (!defined('_JLMATCHLISTSLIDERMODURL')) { define('_JLMATCHLISTSLIDERMODURL', JUri::base().'modules/mod_sportsmanagement_matchesslider/');}
require_once (_JLMATCHLISTSLIDERMODPATH.DS.'helper.php');
require_once (_JLMATCHLISTSLIDERMODPATH.DS.'connectors'.DS.'sportsmanagement.php');


JHTML::_('behavior.mootools');

$doc = JFactory::getDocument();
$doc->addScript( _JLMATCHLISTSLIDERMODURL.'assets/js/jquery.simplyscroll.js' );
//$doc->addStyleSheet(_JLMATCHLISTMODURL.'tmpl/'.$template.'/mod_sportsmanagement_matchesslider.css');
$doc->addStyleSheet(_JLMATCHLISTSLIDERMODURL.'assets/css/mod_sportsmanagement_matchesslider.css');


JHTML::_('behavior.tooltip');
$doc->addScriptDeclaration('
(function($) {
	$(function() { //on DOM ready
		$("#scroller").simplyScroll({
			customClass: \'custom\',
			direction: \'backwards\',
			pauseOnHover: false,
			frameRate: 20,
			speed: 2
		});
	});
})(jQuery);
  ');

//$mod = new MatchesSliderSportsmanagementConnector($params, $module->id, $match_id);
//$matches = $mod->getMatches();

$config = array();
$slidermatches = array();
$projectid = JRequest::getInt('p',0);
if ( !$projectid )
{
    foreach( $params->get('project') as $key => $value )
    {
        $projectid = $value;
        sportsmanagementModelProject::$projectid = $projectid;
        $matches = sportsmanagementModelResults::getResultsRows(0,0,$config,$params);
        //$slidermatches[] = $matches;
        $slidermatches = array_merge($matches);
    }
    
}    
else
{
sportsmanagementModelProject::$projectid = $projectid;
$matches = sportsmanagementModelResults::getResultsRows(0,0,$config,$params);
$slidermatches = array_merge($matches);
}

foreach( $slidermatches as $match )
{

switch ( $params->get('p_link_func') )
{
    case 'results':
    $link = sportsmanagementHelperRoute::getResultsRoute( $match->project_slug, $match->round_slug, 0 );
    break;
    case 'ranking':
    $link = sportsmanagementHelperRoute::getRankingRoute( $match->project_slug, $match->round_slug,null,null,0,0 );
    break;
    case 'resultsrank':
    $link = sportsmanagementHelperRoute::getResultsRankingRoute( $match->project_slug, $match->round_slug, 0  );
    break;
}

}



require(JModuleHelper::getLayoutPath('mod_sportsmanagement_matchesslider'));


?>
