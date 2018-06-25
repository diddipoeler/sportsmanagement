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

switch($this->fieldset)
{
// für extra felder
case 'extra_fields':
?>
	<fieldset class="adminform">
	
	<table>
    <?php
	for($p=0;$p<count($this->lists['ext_fields']);$p++)
            {
			?>
			<tr>
				<td width="100">
					<?php echo $this->lists['ext_fields'][$p]->name;?>
				</td>
				<td>
					<input type="text" maxlength="100" size="100" name="extraf[]" value="<?php echo isset($this->lists['ext_fields'][$p]->fvalue)?htmlspecialchars($this->lists['ext_fields'][$p]->fvalue):""?>" />
					<input type="hidden" name="extra_id[]" value="<?php echo $this->lists['ext_fields'][$p]->id?>" />
                    <input type="hidden" name="extra_value_id[]" value="<?php echo $this->lists['ext_fields'][$p]->value_id?>" />
				</td>
			</tr>
			<?php	
			}
	?>
    </table>
	</fieldset>
	<?php

break;

// für google maps    
case 'maps':
$document = JFactory::getDocument();
$document->addScript('http://maps.google.com/maps/api/js?&sensor=false');
?>
<script language="javascript" type="text/javascript">
var map;

function initialize() {
	var start = new google.maps.LatLng(<?php echo $this->item->latitude?>,<?php echo $this->item->longitude?>);
 	var image = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';
     var myOptions = {
      zoom: 12,
      center: start,
      mapTypeId: google.maps.MapTypeId.HYBRID
    };
    map = new google.maps.Map($('map'), myOptions);
    
    var marker = new google.maps.Marker({
      position: start,
      map: map,
      icon: image,
      title: '<?php echo $this->item->name?>'
  });
    
    kartenwerte();
	}
	
	function kartenwerte() {
	var mapcenter =  map.getCenter();
	$('conf_center_lat').value =mapcenter.lat();
	$('conf_center_lng').value =mapcenter.lng();	
	$('conf_start_zoom').value = map.getZoom();
	
	} 
</script>

<fieldset class="adminform">
			
<body onLoad="initialize()">              

<div id="map" style="width:400px; height:400px;"></div>
</fieldset>
<?PHP
break;

// für die extended daten
case 'params':
if ( isset($this->formparams) )
{
foreach ($this->formparams->getFieldsets() as $fieldset)
{
	?>
	<fieldset class="adminform">
	
	<?php
	$fields = $this->formparams->getFieldset($fieldset->name);
	
	if(!count($fields)) 
    {
		echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
	}
	echo '<b><p class="tab-description">'.JText::_($this->description).'</p></b>';
	foreach ($fields as $field)
	{
		echo $field->label;
       	echo $field->input;
	}
	?>
	</fieldset>
	<?php
}
}
else
{
    echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
}
break;

// für die extended daten
case 'extended':
if ( isset($this->extended) )
{
foreach ($this->extended->getFieldsets() as $fieldset)
{
	?>
	<fieldset class="adminform">
	
	<?php
	$fields = $this->extended->getFieldset($fieldset->name);
	
	if(!count($fields)) {
		echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
	}
	
	foreach ($fields as $field)
	{
		echo $field->label;
       	echo $field->input;
	}
	?>
	</fieldset>
	<?php
}
}
else
{
    echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
}
break;

// das ist der standard
default:
?>
		<fieldset class="adminform">
			<table class="admintable">
					<?php 
                    foreach ($this->form->getFieldset($this->fieldset) as $field): 
                    ?>
					<tr>
						<td class="key"><?php echo $field->label; ?></td>
						<td><?php echo $field->input; ?></td>
					</tr>					
					<?php endforeach; ?>
			</table>
		</fieldset>
<?PHP
break;
}

?>        