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

//echo $this->kmlfile.'<br>';
//echo JURI::root(true).'<br>';
//echo JURI::root().'<br>';
//$this->kmlfile = 'test-club.kml';
?>



<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
	
		<h4>
			<?php echo JText::_('COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'); ?>
		</h4>


	
<?php

if ( !JPluginHelper::isEnabled( 'system', 'plugin_googlemap3' )  )
            {
//                JError::raiseWarning(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_NOT_ENABLED'));
$this->document->addScript('http://maps.google.com/maps/api/js?language=de');
$this->document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');
        
?>
<script type="text/javascript">

jQuery(document).ready(function() {
   
jQuery("#jsm_map").gmap3({ 
  map:{
    options:{ 
      //center:{lat: 40.65,lng: -73.95}, 
      MapTypeId: google.maps.MapTypeId.HYBRID,
      zoom: 9
    }
  },
  kmllayer:{
    options:{
      url: "<?PHP echo JURI::root().'tmp/'.$this->kmlfile; ?>",
      opts:{
        suppressInfoWindows: true
      }
    },
    events:{
      click: function(kml, event){
        alert(event.featureData.description);
      }
    }
  }
});   
   
});




</script>
<style>
.gmap{
width: 100%;
height: 570px;
}
</style>
<div id="jsm_map" class="gmap"></div>

<?PHP                
            }
            else
            {
                //JError::raiseNotice(100,JText::_('COM_SPORTSMANAGEMENT_ADMIN_GOOGLEMAP_AVAILABLE'));
                //JError::raiseNotice(100,JText::_($this->kmlpath));
                //JError::raiseNotice(100,JText::_($this->kmlfile));
                $plugin = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
                $paramsPlugin = new JRegistry($plugin->params);

//echo 'kml<br><pre>'.print_r($this->kmlpath,true).'</pre>';
//echo 'plugin_googlemap3<br><pre>'.print_r($paramsPlugin,true).'</pre>';
    

$params  = "{mosmap kml[0]='".'tmp'.DS.$this->kmlfile."'}";
echo JHtml::_('content.prepare', $params);
  
            }
            
            
?>
</div>