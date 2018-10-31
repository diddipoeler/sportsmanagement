<?php 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

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
$document = Factory::getDocument();
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
case 'extended':
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