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

defined('_JEXEC') or die('Restricted access');
//require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'sportsmanagement.php');
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
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

//require_once(JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php');  
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'route.php');  
require_once(JPATH_SITE.DS.JSM_PATH.DS.'helpers'.DS.'countries.php');

require_once(dirname(__FILE__).DS.'helper.php');

// Reference global application object
$app = JFactory::getApplication();
$document = JFactory::getDocument();
$show_debug_info = JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) ;

if (! defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE'))
{
DEFINE( 'COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE',JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database' ) );
}

if (JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_dbprefix' ))
{
$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;    
}
else
{
if ( COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE || JRequest::getInt( 'cfg_which_database', 0 ) )
{
$module->picture_server = JComponentHelper::getParams('com_sportsmanagement')->get( 'cfg_which_database_server' ) ;
}
else
{
$module->picture_server = JURI::root() ;
}
}

//add css file
//$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');

$mode = $params->def("mode");
$results = $params->get('limit');
$limit = $params->get('limit');
$refresh = $params->def("refresh");
$minute = $params->def("minute");
$height = $params->def("height");
$width  = $params->def("width");

$futuremessage = htmlentities(trim(JText::_($params->get('futuremessage'))), ENT_COMPAT , 'UTF-8');
// Prevent that result is null when either $players or $crew is null by casting each to an array.
//$persons = array_merge((array)$players, (array)$crew);
$clubs = modSportsmanagementClubBirthdayHelper::getClubs($limit);
if(count($clubs)>1)   $clubs = modSportsmanagementClubBirthdayHelper::jl_birthday_sort($clubs,$params->def("sort_order"));

if ( $show_debug_info )
{
$my_text = 'clubs <pre>'.print_r($clubs,true).'</pre>';
$my_text .= 'params <pre>'.print_r($params,true).'</pre>';    
sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,$module->module,__LINE__,$my_text);
}

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' attribs<br><pre>'.print_r($attribs,true).'</pre>'),'Notice');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' module<br><pre>'.print_r($module,true).'</pre>'),'Notice');


$k=0;
$counter=0;

//echo 'mode -> '.$mode.'<br>';
//echo 'refresh -> '.$refresh.'<br>';
//echo 'minute -> '.$minute.'<br>';

$layout = isset($attribs['layout'])?$attribs['layout']:'default';

if(count($clubs) > 0) 
{

if (count($clubs)<$results)
{
$results=count($clubs);
}
        
$tickerpause = $params->def("tickerpause");
$scrollspeed = $params->def("scrollspeed");
$scrollpause = $params->def("scrollpause");

/*
$javascript = "\n";
$javascript .= "	window.addEvent('domready', function() {"."\n";
$javascript .= "	var opt".$params->get( 'moduleclass_sfx' )." = {"."\n";
$javascript .= "	  duration: 3000,"."\n";
$javascript .= "	  delay:".$tickerpause."000,"."\n";
$javascript .= "	  auto:true,"."\n";
$javascript .= "	  direction: 'v',"."\n";
$javascript .= "	  onMouseEnter: function(){this.stop();},"."\n";
$javascript .= "	  onMouseLeave: function(){this.play();}"."\n";
$javascript .= "	};"."\n";
$javascript .= "	var scroller".$params->get( 'moduleclass_sfx' )." = new QScroller('qscroller".$params->get( 'moduleclass_sfx' )."',opt".$params->get( 'moduleclass_sfx' ).");"."\n";
$javascript .= "	scroller".$params->get( 'moduleclass_sfx' ).".load();"."\n";
$javascript .= "	});"."\n";
$javascript .= "\n";
$document->addScriptDeclaration( $javascript );
*/    

//$attribs = array('layout'=>'default');;
//$layout = isset($attribs['layout'])?$attribs['layout']:'default';
    
	switch ($mode)
	{
		case 'B':
        $layout = isset($attribs['layout'])?$attribs['layout']:'default';
            //$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;
        case 'T':
        $layout = isset($attribs['layout'])?$attribs['layout']:'default';
			include(dirname(__FILE__).DS.'js'.DS.'ticker.js');
            $document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;
		case 'V':
        
        $layout = isset($attribs['layout'])?$attribs['layout']:'wowslider';
        //$layout = isset($attribs['layout'])?$attribs['layout']:'default';

$html_li = '';
    $html_ahref = '';
    $id = 0;
    foreach ($clubs AS $club) 
    {
    $club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
    $thispic = "";
    $whenmessage = "";
    $birthdaytext2 = "";
	$flag = $params->get('show_club_flag')? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";
	$text = $club->name;
    $usedname = $flag.$text;
	$club_link = "";
    $club_link = sportsmanagementHelperRoute::getClubInfoRoute($club->project_id,$club->id);
	$showname = JHTML::link( $club_link, $usedname );
    
    if ($params->get('show_picture')==1) 
        {
			if (file_exists(JPATH_BASE.'/'.$club->picture)&&$club->picture!='') 
            {
				$thispic = $club->picture;
			}
			elseif (file_exists(JPATH_BASE.'/'.$club->default_picture)&&$club->default_picture!='') 
            {
				$thispic = $club->default_picture;
			}
		}
    
    switch ($club->days_to_birthday) 
        {
			case 0: $whenmessage = $params->get('todaymessage');break;
			case 1: $whenmessage = $params->get('tomorrowmessage');break;
			default: $whenmessage = str_replace('%DAYS_TO%', $club->days_to_birthday, trim($futuremessage));break;
		}
        
        if ( $club->founded != '0000-00-00' )
        {
            $birthdaytext2 = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT , 'UTF-8');
            $dayformat = htmlentities(trim($params->get('dayformat')));
		    $birthdayformat = htmlentities(trim($params->get('birthdayformat')));
		    $birthdaytext2 = str_replace('%WHEN%', $whenmessage, $birthdaytext2);
            $birthdaytext2 = str_replace('%AGE%', $club->age, $birthdaytext2);
            $birthdaytext2 = str_replace('%DATE%', strftime($dayformat, strtotime($club->year.'-'.$club->daymonth)), $birthdaytext2);
    		$birthdaytext2 = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
        }
        else
        {
            $birthdaytext2 = htmlentities(trim(JText::_($params->get('birthdaytextyear'))), ENT_COMPAT , 'UTF-8');
            $birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
        }
            
            $birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
		$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
		$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);
            
            $text .= '<br> '.$birthdaytext2;
    $html_li .= '<li><a href="'.$club_link.'"><img src="'.$thispic.'" alt="'.$text.'" title="'.$text.'" id="wows1_'.$id.'" /></a></li>';    
    //$html_li .= '<li><img src="'.$thispic.'" alt="" title="" id="wows1_'.$id.'" /></li>';
    $id++;      
    $html_ahref .= '<a href="#" title=""><img src="'.$thispic.'" alt=""    />'.$id.'</a>';    
        
          
    }    

        //$wowslider_style = "basic_linear";
        //$wowslider_style = "squares";
        //$wowslider_style = "fade";
        $wowslider_style = $params->def("wowsliderstyle");
        
			//include(dirname(__FILE__).DS.'js'.DS.'qscrollerv.js');
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscrollerv.js');
			//$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/jquery.simplyscroll.js');
            $document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/wowslider/'.$wowslider_style.'/style.css');
            
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/wowslider.js');
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/'.$wowslider_style.'.js');
            
            
//$javascript = '
//// init main object
//// jQuery(document).ready - conflicted with some scripts
//// Transition time = 2.4s = 20/10
//// SlideShow delay = 6.5s = 23/10
//jQuery(\'#wowslider-container\').wowSlider({
//	effect:"carousel_basic", 
//	prev:"", 
//	next:"", 
//	duration: 23*100, 
//	delay:20*100, 
//	width:830,
//	height:360,
//	autoPlay:true,
//	autoPlayVideo:false,
//	playPause:false,
//	stopOnHover:false,
//	loop:false,
//	bullets:1,
//	caption: true, 
//	captionEffect:"fade",
//	controls:true,
//	responsive:1,
//	fullScreen:false,
//	gestures: 2,
//	onBeforeStep:0,
//	images:0
//});
//';
//$javascript .= "\n";
//$document->addScriptDeclaration( $javascript );




			break;
		case 'L':
        $layout = isset($attribs['layout'])?$attribs['layout']:'default';
			//include(dirname(__FILE__).DS.'js'.DS.'qscrollerh.js');
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscrollerh.js');
			//$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/qscroller.js');
            //$document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/wowslider.js');
            $document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_club_birthday/css/mod_sportsmanagement_club_birthday.css');
			break;
            
       case 'J':
       $container = 'slider'.$module->id.'_container';
        $layout = isset($attribs['layout'])?$attribs['layout']:'jssor';
        $document->addScript(JURI::base().'modules/mod_sportsmanagement_club_birthday/js/jssor.slider.mini.js');

$html_li = '';
    $html_ahref = '';
    $id = 0;
    foreach ($clubs AS $club) 
    {
    $club->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');
    $thispic = "";
    $whenmessage = "";
    $birthdaytext2 = "";
	$flag = $params->get('show_club_flag')? JSMCountries::getCountryFlag($club->country) . "&nbsp;" : "";
	$text = '';
    $usedname = $flag.$text;
	$club_link = "";
    $club_link = sportsmanagementHelperRoute::getClubInfoRoute($club->project_id,$club->id);
	$showname = JHTML::link( $club_link, $usedname );
    
    if ($params->get('show_picture')==1) 
        {
			if (file_exists(JPATH_BASE.'/'.$club->picture)&&$club->picture!='') 
            {
				$thispic = $club->picture;
			}
			elseif (file_exists(JPATH_BASE.'/'.$club->default_picture)&&$club->default_picture!='') 
            {
				$thispic = $club->default_picture;
			}
		}
    
    switch ($club->days_to_birthday) 
        {
			case 0: $whenmessage = $params->get('todaymessage');break;
			case 1: $whenmessage = $params->get('tomorrowmessage');break;
			default: $whenmessage = str_replace('%DAYS_TO%', $club->days_to_birthday, trim($futuremessage));break;
		}
        
        if ( $club->founded != '0000-00-00' )
        {
            $birthdaytext2 = htmlentities(trim(JText::_($params->get('birthdaytext'))), ENT_COMPAT , 'UTF-8');
            $dayformat = htmlentities(trim($params->get('dayformat')));
		    $birthdayformat = htmlentities(trim($params->get('birthdayformat')));
		    $birthdaytext2 = str_replace('%WHEN%', $whenmessage, $birthdaytext2);
            $birthdaytext2 = str_replace('%AGE%', $club->age, $birthdaytext2);
            $birthdaytext2 = str_replace('%DATE%', strftime($dayformat, strtotime($club->year.'-'.$club->daymonth)), $birthdaytext2);
    		$birthdaytext2 = str_replace('%DATE_OF_BIRTH%', strftime($birthdayformat, strtotime($club->date_of_birth)), $birthdaytext2);
        }
        else
        {
            $birthdaytext2 = htmlentities(trim(JText::_($params->get('birthdaytextyear'))), ENT_COMPAT , 'UTF-8');
            $birthdaytext2 = str_replace('%AGE%', $club->age_year, $birthdaytext2);
        }
            
            $birthdaytext2 = str_replace('%BR%', '<br />', $birthdaytext2);
		$birthdaytext2 = str_replace('%BOLD%', '<b>', $birthdaytext2);
		$birthdaytext2 = str_replace('%BOLDEND%', '</b>', $birthdaytext2);
            
            $text .= '<br> '.$birthdaytext2;
            
$html_li .= '<div><a href="'.$club_link.'"><img u="image" src="'.$thispic.'" /></a>';
//$html_li .= '<div u="caption" t="transition_name1" style="position: absolute; top: 30px; left: 30px; width: 50px;height: 50px;">';
//$html_li .= $text;

$html_li .= '<div u="caption" t="'.$params->get('jssor_captiontransitions').'" style="position:absolute;left:10px;top:80px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">'.$club->name.'</div>';
$html_li .= '<div u="caption" t="'.$params->get('jssor_captiontransitions').'" style="position:absolute;left:10px;top:130px;width:600px;height:40px;font-size:36px;color:#000;line-height:40px;">'.$text.'</div>';
//$html_li .= '<div u="caption" t="B-T" style="position:absolute;left:380px;top:80px;width:130px;height:40px;font-size:36px;color:#000;line-height:40px;">Please!</div>';

//$html_li .= '</div>';
$html_li .= '</div>';
            
    //$html_li .= '<li><a href="'.$club_link.'"><img src="'.$thispic.'" alt="'.$text.'" title="'.$text.'" id="wows1_'.$id.'" /></a></li>';    
    //$html_li .= '<li><img src="'.$thispic.'" alt="" title="" id="wows1_'.$id.'" /></li>';
    $id++;      
    $html_ahref .= '<a href="#" title=""><img src="'.$thispic.'" alt=""    />'.$id.'</a>';    
        
          
    }    
        

            $document->addStyleSheet(JURI::base().'modules/'.$module->module.'/css/'.$module->module.'.css');
			break;     
	}
    
}
?>           
<div id="<?php echo $module->module; ?>-<?php echo $module->id; ?>">
<?PHP
require(JModuleHelper::getLayoutPath($module->module,$layout));
?>
</div>