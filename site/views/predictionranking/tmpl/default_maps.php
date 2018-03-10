<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'mapconfig<pre>',print_r($this->mapconfig,true),'</pre><br>';
}

//$kmlpath = JURI::root().'components'.DS.'com_sportsmanagement'.DS.'views'.DS.'ranking'.DS.'tmpl'.DS.'default_genkml3.php';
$kmlpath = JURI::root().'tmp'.DS.$this->predictionGame->id.'-prediction.kml';

//echo $kmlpath.'<br>';

?>
<div style="width: 100%; float: left">
	<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
		</div>
	</div>
	<?php
		$arrPluginParams = array();
		
		$arrPluginParams[] = "zoomWheel='1'";
		
		$param = 'default_map_type';
		if($this->mapconfig[$param]) {
			$arrPluginParams[] = "mapType='".$this->mapconfig[$param]."'";
		}
		$param = 'map_control';
		if($this->mapconfig[$param]) {
			$arrPluginParams[] = "zoomType='".$this->mapconfig[$param]."'";
		}
		$param = 'width';
		if($this->mapconfig[$param]) {
			$arrPluginParams[] = "$param='".$this->mapconfig[$param]."'";
		}
		$param = 'height';
		if($this->mapconfig[$param]) {
			$arrPluginParams[] = "$param='".$this->mapconfig[$param]."'";
		}
		
		/*
    foreach ( $this->allteams as $row )
    {
		if($row->address_string != '') {
			$arrPluginParams[] = "address='" .$row->address_string. "'";
			$arrPluginParams[] = "text='<div style=width:250px;height=30px;>".$row->address_string."</div>'";
		}
		$icon = '';
		if($row->logo_small != '')
		{
			$arrPluginParams[] = "tooltip='". $row->team_name . "'";
			$icon= $row->logo_small;
		}
		if($icon!='') {
			$arrPluginParams[] = "icon='".$icon."'";
		}
    
    }
    */
    
		$params  = '{mosmap ';
		$params .= implode('|', $arrPluginParams);
		$params .= "}";
		//echo JHTML::_('content.prepare', $params);
		
    $params  = "{mosmap mapType='HYBRID'|dir='1'|zoomWheel='1'|zoom='10'|corzoom='0'|minzoom='0'|maxzoom='19'|showEarthMaptype='1'|showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'   |kml='".$kmlpath."'|kmlrenderer='geoxml'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|lightbox='1'|width='100%'|height='".$this->mapconfig['height']."' |overview='1'  }";    
		echo JHTML::_('content.prepare', $params);		
		
		
	?>
</div>