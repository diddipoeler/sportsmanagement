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

?>
<div style="width: 100%; float: left">
	<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
		</div>
	</div>
<?php

if ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' )  )
            {
                JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_ENABLED'));
                
            }
            else
            {
                JError::raiseNotice(100,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_AVAILABLE'));
                $plugin = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
                $paramsPlugin = new JRegistry($plugin->params);

//echo 'kml<br><pre>'.print_r($this->kmlpath,true).'</pre>';
//echo 'plugin_googlemap3<br><pre>'.print_r($paramsPlugin,true).'</pre>';
                
$arrPluginParams = array();

$arrPluginParams[] = "mapType='".$paramsPlugin->get('mapType','')."'";
$arrPluginParams[] = "zoomWheel='".$paramsPlugin->get('zoomWheel','')."'";
$arrPluginParams[] = "zoom='".$paramsPlugin->get('zoom','')."'";
$arrPluginParams[] = "corzoom='".$paramsPlugin->get('corzoom','')."'";
$arrPluginParams[] = "minzoom='".$paramsPlugin->get('minzoom','')."'";
$arrPluginParams[] = "maxzoom='".$paramsPlugin->get('maxzoom','')."'";
$arrPluginParams[] = "showEarthMaptype='".$paramsPlugin->get('showEarthMaptype','')."'";

$arrPluginParams[] = "kml='".$this->kmlpath."'";
$arrPluginParams[] = "kmlrenderer='".$paramsPlugin->get('kmlrenderer','')."'";
$arrPluginParams[] = "kmlsidebar='".$paramsPlugin->get('kmlsidebar','')."'";
$arrPluginParams[] = "kmlsbwidth='".$paramsPlugin->get('kmlsbwidth','')."'";
$arrPluginParams[] = "overview='1'";
$arrPluginParams[] = "lightbox='1'";

$arrPluginParams[] = "width='".$paramsPlugin->get('width','')."'";
$arrPluginParams[] = "height='".$paramsPlugin->get('height','')."'";

/*
$arrPluginParams[] = "mapType='".$paramsPlugin->get('mapType','')."'";
$arrPluginParams[] = "zoomWheel='".$paramsPlugin->get('zoomWheel','')."'";
$arrPluginParams[] = "zoom='".$paramsPlugin->get('zoom','')."'";
$arrPluginParams[] = "corzoom='".$paramsPlugin->get('corzoom','')."'";
$arrPluginParams[] = "minzoom='".$paramsPlugin->get('minzoom','')."'";
$arrPluginParams[] = "maxzoom='".$paramsPlugin->get('maxzoom','')."'";
$arrPluginParams[] = "showEarthMaptype='".$paramsPlugin->get('showEarthMaptype','')."'";

$arrPluginParams[] = "showNormalMaptype='".$paramsPlugin->get('showNormalMaptype','')."'";
$arrPluginParams[] = "showSatelliteMaptype='".$paramsPlugin->get('showSatelliteMaptype','')."'";
$arrPluginParams[] = "showTerrainMaptype='".$paramsPlugin->get('showTerrainMaptype','')."'";
$arrPluginParams[] = "showHybridMaptype='".$paramsPlugin->get('showHybridMaptype','')."'";

$arrPluginParams[] = "kml='".$kmlpath."'";
//$arrPluginParams[] = "kmlrenderer='".$paramsPlugin->get('kmlrenderer','')."'";
$arrPluginParams[] = "kmlrenderer='GeoXML'";
$arrPluginParams[] = "kmlsidebar='".$paramsPlugin->get('kmlsidebar','')."'";
$arrPluginParams[] = "kmlsbwidth='".$paramsPlugin->get('kmlsbwidth','')."'";
$arrPluginParams[] = "overview='".$paramsPlugin->get('overview','')."'";
$arrPluginParams[] = "lightbox='".$paramsPlugin->get('lightbox','')."'";
$arrPluginParams[] = "controltype='".$paramsPlugin->get('controltype','')."'";

$arrPluginParams[] = "width='".$paramsPlugin->get('width','')."'";
$arrPluginParams[] = "height='".$paramsPlugin->get('height','')."'";
*/

/*                
$params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'|dir='1'|zoomWheel='1'|zoom='".$paramsPlugin->get('zoom','')."'|corzoom='0'|minzoom='0'|maxzoom='19'|
showEarthMaptype='1'|

showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'   

|kml='".$kmlpath."'|kmlrenderer='geoxml'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|


lightbox='1'|

width='".$paramsPlugin->get('width','')."'|height='".$paramsPlugin->get('height','')."' |overview='1'  }";    
*/	

    
//    	echo JHtml::_('content.prepare', $params);		

$params  = '{mosmap ';
		$params .= implode('|', $arrPluginParams);
		$params .= "}";
        echo JHtml::_('content.prepare', $params);
 
//		$content = JHtml::_('content.prepare', $params);
//        echo $content;
        
//    $params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'|dir='1'|zoomWheel='1'|zoom='".$paramsPlugin->get('zoom','')."'|corzoom='0'|minzoom='0'|maxzoom='19'|showEarthMaptype='1'|showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'   |kml='".$this->kmlpath."'|kmlrenderer='GeoXML'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|lightbox='1'|width='".$paramsPlugin->get('width','')."'|height='".$paramsPlugin->get('height','')."' |overview='1'  }";    
//		echo JHtml::_('content.prepare', $params);        
            }
?>
</div>