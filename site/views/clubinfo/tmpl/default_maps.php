<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 
//JPluginHelper::importPlugin('content');

$kmlpath = JURI::root().'tmp'.DS.$this->club->id.'-club.kml';
//$kmlpath = JURI::base().'tmp'.DS.$this->club->id.'-club.kml';

/*
<script type="text/javascript" language="JavaScript">document.write("<b><font color='#ff0000'>JavaScript ist in Ihrem Browser aktiviert, alles paletti!</font></b>");</script>
<noscript><font color="#ff0000"><b>JavaScript ist leider deaktiviert, bitte aktivieren Sie es in Ihrem Browser </b>(unter Voreinstellungen, Optionen, Preferences, je nach Browsertyp)</font>
</noscript>
*/
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
                //JError::raiseNotice(100,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_AVAILABLE'));
                $plugin = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
                $paramsPlugin = new JRegistry($plugin->params);

//echo 'club.kml<br><pre>'.print_r($kmlpath,true).'</pre>';
//echo 'plugin_googlemap3<br><pre>'.print_r($paramsPlugin,true).'</pre>';
                
$arrPluginParams = array();

$arrPluginParams[] = "mapType='".$paramsPlugin->get('mapType','')."'";
$arrPluginParams[] = "zoomWheel='".$paramsPlugin->get('zoomWheel','')."'";
$arrPluginParams[] = "zoom='".$paramsPlugin->get('zoom','')."'";
$arrPluginParams[] = "corzoom='".$paramsPlugin->get('corzoom','')."'";
$arrPluginParams[] = "minzoom='".$paramsPlugin->get('minzoom','')."'";
$arrPluginParams[] = "maxzoom='".$paramsPlugin->get('maxzoom','')."'";
$arrPluginParams[] = "showEarthMaptype='".$paramsPlugin->get('showEarthMaptype','')."'";

$arrPluginParams[] = "kml='".$kmlpath."'";
$arrPluginParams[] = "kmlrenderer='GeoXML'";
$arrPluginParams[] = "kmlsidebar='left'";
$arrPluginParams[] = "kmlsbwidth='200'";
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
        
//    $params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'|dir='1'|zoomWheel='1'|zoom='".$paramsPlugin->get('zoom','')."'|corzoom='0'|minzoom='0'|maxzoom='19'|showEarthMaptype='1'|showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'   |kml='".$kmlpath."'|kmlrenderer='GeoXML'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|lightbox='1'|width='".$paramsPlugin->get('width','')."'|height='".$paramsPlugin->get('height','')."' |overview='1'  }";    
//		echo JHtml::_('content.prepare', $params);        
            }
            		
        

                
	?>
</div>