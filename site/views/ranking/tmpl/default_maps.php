<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ( $this->show_debug_info )
{
echo 'mapconfig<pre>',print_r($this->mapconfig,true),'</pre><br>';
}

//$kmlpath = JURI::root().'components'.DS.'com_sportsmanagement'.DS.'views'.DS.'ranking'.DS.'tmpl'.DS.'default_genkml3.php';
$kmlpath = JURI::root().'tmp'.DS.$this->project->id.'-ranking.kml';

//echo $kmlpath.'<br>';

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


$params  = '{mosmap ';
		$params .= implode('|', $arrPluginParams);
		$params .= "}";
        echo JHtml::_('content.prepare', $params); 
        
                        
//                echo '<pre>'.print_r($paramsPlugin, true).'</pre><br>';
//                echo $paramsPlugin->get('width','').'<br>';
//                echo $paramsPlugin->get('height','').'<br>';

//    $params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'|dir='1'|zoomWheel='1'|zoom='".$paramsPlugin->get('zoom','')."'|corzoom='0'|minzoom='0'|maxzoom='19'|showEarthMaptype='1'|showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'   |kml='".$kmlpath."'|kmlrenderer='geoxml'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|lightbox='1'|width='".$paramsPlugin->get('width','')."'|height='".$paramsPlugin->get('height','')."' |overview='1'  }";    
//		echo JHtml::_('content.prepare', $params);
                    
            }
            		
		
		
		
	?>
</div>