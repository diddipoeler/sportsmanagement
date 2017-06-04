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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/ 

defined('_JEXEC') or die('Restricted access');
$this->view = JFactory::getApplication()->input->getCmd('view');
//echo $this->kmlfile.'<br>';
//echo JURI::root(true).'<br>';
//echo JURI::root().'<br>';
//$this->kmlfile = 'test-club.kml';

switch ($this->view)
{
case 'ranking':
//echo '<pre>'.print_r($this->allteams,true).'</pre><br>';
foreach ( $this->allteams as $row )
{
// team_name
//$values[]['latLng'] = '['.$row->latitude.','.$row->longitude.'], data:'.$row->team_name;
//$values[][data] = '['.$row->team_name.']';
$latitude = $row->latitude;
$longitude = $row->longitude;
//echo 'latitude  -> '.$latitude .'<br>';
//echo 'longitude -> '.$longitude .'<br>';
}
$icon = 'http://maps.google.com/mapfiles/marker_green.png';
//echo json_encode($values);

break;
case 'clubinfo':
$latitude = $this->club->latitude;
$longitude = $this->club->longitude;
$icon = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';
break;
case 'playground':
$latitude = $this->playground->latitude;
$longitude = $this->playground->longitude;
$icon = 'http://maps.google.com/mapfiles/kml/pal2/icon39.png';
break;

}

?>

<div class="row-fluid">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<h4>
<?php echo JText::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
</h4>
	
<?php
/**
 * welche joomla version 
 * und ist seo eingestellt
 */
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $sef = JFactory::getConfig()->get('sef', false);
        }
        else
        {
		$sef = JFactory::getConfig()->getValue('config.sef', false);
        }
        
if ( ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) ) || ( JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' ) && $sef ) )
{
// JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_ENABLED'));
//$this->document->addScript('http://maps.google.com/maps/api/js?language=de');
//$this->document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');
//$this->document->addScript('http://maps.google.com/maps/api/js?language=de');
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.21&sensor=false&language=de');	
//$this->document->addScript('https://maps.googleapis.com/maps/api/js?v=3.21&language=de');		
$this->document->addScript('https://cdn.jsdelivr.net/gmap3/7.2.0/gmap3.min.js');
        
?>

<div id="jsm_map" class="gmap3"></div>
<script type="text/javascript">
<?PHP
switch ($this->view)
{
case 'clubinfo':
case 'playground':
?>
var center = [<?PHP echo $latitude; ?>, <?PHP echo $longitude; ?>];
jQuery(document).ready(function()  {
    jQuery('#jsm_map')
      .gmap3({
center: center,
  zoom: 15,
          mapTypeId: google.maps.MapTypeId.HYBRID,
        mapTypeControl: true,
navigationControl: true,
        scrollwheel: true,
        streetViewControl: true
      })
      .marker({
        position: center,
        icon: '<?PHP echo $icon; ?>'
      })
    ;
  });

<?PHP
break;
default:
?>
jQuery(document).ready(function() {
    jQuery('#jsm_map')
      .gmap3({
        zoom: 3,
          mapTypeId: google.maps.MapTypeId.HYBRID,
        mapTypeControl: true,
navigationControl: true,
        scrollwheel: true,
        streetViewControl: true
      })
      .kmllayer({url: '<?PHP echo JURI::root().'tmp/'.$this->kmlfile; ?>'})
.wait(1000)
    ;
  });
<?PHP

break;

}
?>

</script>
<style>
.gmap3{
width: 100%;
height: 570px;
}
</style>


<?PHP                
}
else
{
$plugin = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
$paramsPlugin = new JRegistry($plugin->params);

//echo 'kml<br><pre>'.print_r($this->kmlpath,true).'</pre>';
//echo 'plugin_googlemap3<br><pre>'.print_r($paramsPlugin,true).'</pre>';

$params  = "{mosmap kml[0]='".'tmp'.DS.$this->kmlfile."'}";
echo JHtml::_('content.prepare', $params);
  
}
            
            
?>
</div>
</div>
