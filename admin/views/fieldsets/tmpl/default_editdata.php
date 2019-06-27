<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_editdata.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

$this->document->addScript('https://unpkg.com/leaflet@1.3.4/dist/leaflet.js');
$this->document->addStyleSheet('https://unpkg.com/leaflet@1.3.4/dist/leaflet.css');
$this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');

/**
 * welche joomla version ?
 */
if( version_compare(JSM_JVERSION,'4','eq') ) 
{
/**  Include the component HTML helpers. */
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');    
HTMLHelper::_('jquery.framework');
}    

$templatesToLoad = array('footer','fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
try{
/** Get the form fieldsets. */
$fieldsets = $this->form->getFieldsets();
}
catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns
	Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error');	
	return false;
}
$view = $this->jinput->getCmd('view', 'cpanel');


/**
 * welche joomla version ?
 */
if( version_compare(JSM_JVERSION,'4','eq') ) 
{
/**
 * joomla 4
 */     
?>
<div>
<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));

foreach ($fieldsets as $fieldset) 
{
echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
?>
<div class="row">
<div class="col-md-12">
<?PHP
switch ($fieldset->name)
{
    case 'details':
    ?>
    <div class="row-fluid">
					<div class="span6">
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						
                        <?PHP
                        $suchmuster = array ("jform[","]","request[","params[");
                $ersetzen = array ('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                        switch ($var_onlinehelp)
                {
                    case 'ids':
                    break;
                    default:
                ?>
<a href="#<?php echo $var_onlinehelp;?>" title="<?php echo $var_onlinehelp;?>" class="" data-toggle="modal">
<?php
echo HTMLHelper::_(	'image','media/com_sportsmanagement/jl_images/help.png',
Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
Text::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
	
echo HTMLHelper::_('bootstrap.renderModal',
	$var_onlinehelp,
	array(
	'title' => Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),
	'url' => COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.$this->jinput->getVar( "view").'-'.$var_onlinehelp,
    'width' => COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH,
    'height' => COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT
	)
	);	
?>									
</a>                
                
                <?PHP
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'jform[standard_playground]' )
                {
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);
?>
<a href="<?php echo Uri::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo Uri::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'jform[website]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">';
                } 
                }
                if ( $field->name == 'jform[twitter]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">'; 
                }
                }
                if ( $field->name == 'jform[facebook]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">'; 
                }
                }
                break;
                }
                        ?>
                        </div>
					</div>
				<?php

    }
    ?>
    </div>
<div class="span6">
<div class="control-group">
<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
<!-- google map anfang -->
<div id="map_canvas"  class="map_canvas">
</div>
<!-- google map ende -->

<!-- leaflet map anfang -->
<div id="map" style="height: 400px; margin-top: 50px; position: relative;">
</div>
<!-- leaflet map ende -->	

</div>
</div>
</div>
    <?PHP
    break;
    default:
    $this->fieldset = $fieldset->name;
    echo $this->loadTemplate('fieldsets_4');
    break;
}    
?>
</div>
</div>
<?PHP
echo HTMLHelper::_('bootstrap.endTab');
}

/**
 * bei den positionen müssen noch zusätzliche templates 
 * eingebunden werden
 */

switch ($view)
{
    case 'position':
    echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_EVENTS', Text::_('COM_SPORTSMANAGEMENT_TABS_EVENTS', true));
    echo $this->loadTemplate('position_events');
    echo HTMLHelper::_('bootstrap.endTab');
    echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_STATISTICS', Text::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS', true));
    echo $this->loadTemplate('position_statistics');
    echo HTMLHelper::_('bootstrap.endTab');  
    break;
    
} 

echo HTMLHelper::_('bootstrap.endTabSet'); 
?>
</div>
<?php
}	
elseif( version_compare(JSM_JVERSION,'3','eq') ) 
{
/**
 * joomla 3
 */    
?> 
<div class="form-horizontal">
<fieldset>
<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

<?PHP    
foreach ($fieldsets as $fieldset) 
{
echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));    

switch ($fieldset->name)
{
    case 'details':
    ?>
    <div class="row-fluid">
    <?php
    switch ($view)
    {
    case 'club':
    case 'playground':
    case 'person':
    ?>
    <div class="span6">
    <?php
    break;
    default:
    ?>
    <div class="span12">
    <?php
    break;
    }
    ?>
					
    <?PHP
    foreach( $this->form->getFieldset($fieldset->name) as $field ) 
    {
        ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						
                        <?PHP
                        $suchmuster = array ("jform[","]","request[","params[");
                $ersetzen = array ('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
                        switch ($var_onlinehelp)
                {
                    case 'id':
                    break;
                    default:
                ?>
                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
									href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER.'SM-Backend-Felder:'.$this->jinput->getVar( "view").'-'.$var_onlinehelp; ?>"
									 class="modal">
									<?php
									echo HTMLHelper::_(	'image','media/com_sportsmanagement/jl_images/help.png',
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK').'"');
									?>
								</a>
                
                <?PHP
                if ( $field->name == 'jform[country]' )
                {
                echo JSMCountries::getCountryFlag($field->value);    
                }
                
                if ( $field->name == 'jform[standard_playground]' )
                {
                $picture = sportsmanagementHelper::getPicturePlayground($field->value);

?>
<a href="<?php echo Uri::root().$picture;?>" title="<?php echo 'Playground';?>" class="modal">
<img src="<?php echo Uri::root().$picture;?>" alt="<?php echo 'Playground';?>" width="50" />
</a>
<?PHP                   
                }
                
                if ( $field->name == 'jform[website]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">'; 
                }
                }
                if ( $field->name == 'jform[twitter]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">'; 
                }
                }
                if ( $field->name == 'jform[facebook]' )
                {
                    if ( $field->value )
                    {
                echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url='.$field->value.'">'; 
                }
                }
                break;
                }
                        ?>
                        </div>
					</div>
				<?php

    }
    ?>
    </div>
<?php
    switch ($view)
    {
    case 'club':
    case 'playground':
    case 'person':
?>
<div class="span6">
<div class="control-group">
<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
<!-- google map anfang -->
<div id="map_canvas" class="map_canvas" style="display: none;">
</div>
<!-- google map ende -->	
<!-- leaflet map anfang -->	
<div id="map" style="height: 400px; margin-top: 50px; position: relative;">
</div>
<!-- leaflet map ende -->
	
<script>
  
     var planes = [
         ["position",<?php echo $this->item->latitude; ?>,<?php echo $this->item->longitude; ?>]
         ];
  
         var map = L.map('map').setView([<?php echo $this->item->latitude; ?>,<?php echo $this->item->longitude; ?>], 15);
         mapLink =
             '<a href="http://openstreetmap.org">OpenStreetMap</a>';
         L.tileLayer(
             'http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
             attribution: '&copy; ' + mapLink + ' Contributors',
             maxZoom: 20,
             subdomains:['mt0','mt1','mt2','mt3'],
             }).addTo(map);
var myIcon = L.icon({
	iconUrl: 'http://maps.google.com/mapfiles/kml/pal2/icon49.png'
});    

var layerGroup = L.layerGroup().addTo(map);
//var geocoder = new L.Control.Geocoder.Nominatim();
for (i = 0; i < planes.length; i++) {
    marker = L.marker([planes[i][1], planes[i][2]]);
    layerGroup.addLayer(marker);
}

var overlay = {'markers': layerGroup};
L.control.layers(null, overlay).addTo(map);

//         for (var i = 0; i < planes.length; i++) {
//             marker = new L.marker([planes[i][1],planes[i][2]], {icon: myIcon} )
//                 .bindPopup(planes[i][0])
//                 .addTo(map);
//         }

//L.Control.geocoder().addTo(map); 
              
     </script>                            
                            
						</div>
					</div>
                    <?php
                    break;
                    }
                    ?>
            </div>
    <?PHP
    break;
    default:
    $this->fieldset = $fieldset->name;
    echo $this->loadTemplate('fieldsets_3');
    break;
}    
echo HTMLHelper::_('bootstrap.endTab');    
}    

/**
 * bei den positionen müssen noch zusätzliche templates 
 * eingebunden werden
 */

switch ($view)
{
    case 'position':
	?>	
	    
<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'position_events', Text::_('COM_SPORTSMANAGEMENT_TABS_EVENTS')); ?> 
<?php echo $this->loadTemplate('position_events');  ?>
<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'position_statistics', Text::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS')); ?> 
<?php echo $this->loadTemplate('position_statistics');  ?>
<?php echo HTMLHelper::_('bootstrap.endTab'); ?>	    
	    
	    
/*		
    echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_EVENTS', Text::_('COM_SPORTSMANAGEMENT_TABS_EVENTS', true));
    echo $this->loadTemplate('position_events');
    echo HTMLHelper::_('bootstrap.endTab');
    echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'COM_SPORTSMANAGEMENT_TABS_STATISTICS', Text::_('COM_SPORTSMANAGEMENT_TABS_STATISTICS', true));
    echo $this->loadTemplate('position_statistics');
    echo HTMLHelper::_('bootstrap.endTab');  
*/	
	    <?pphp
    break;
    
} 
 
 
?>    
	
<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
</fieldset>
</div> 

<?PHP
}
else
{
?>                

<div class="width-40 fltrt">
	
<div class="control-group">
<style type="text/css">.map_canvas{width:100%;height:400px;}</style>
<!-- google map anfang -->
<div id="map_canvas"  class="map_canvas">
</div>
<!-- google map ende -->	
<!-- leaflet map anfang -->		
<div id="map" style="height: 400px; margin-top: 50px; position: relative;">
</div>
<!-- leaflet map ende -->			
</div>

		<?php
		echo HTMLHelper::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'details') :
				continue;
			endif;
			echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $fieldset->name);
		if (isset($fieldset->description) && !empty($fieldset->description)) :
				echo '<p class="tab-description">'.Text::_($fieldset->description).'</p>';
			endif;
		//echo $this->loadTemplate($fieldset->name);
        $this->fieldset = $fieldset->name;
        echo $this->loadTemplate('fieldsets');
		endforeach; ?>
		<?php echo HTMLHelper::_('sliders.end'); ?>

	
	</div>
<?PHP
}
?>
    
<div class="clr"></div>
<div>
<input type="hidden" name="task" value="<?php echo $view; ?>.edit" />
<?php 
if ( $view == 'teamperson' )
{
?>    
<input type="hidden" name="persontype" value="<?php echo $this->_persontype; ?>" />
<input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />	
<?php    
}
	
if ( $view == 'treetonode' )
{
?>    
<input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->projectws->id; ?>" />
<input type="hidden" name="tid" value="<?php echo $this->item->treeto_id; ?>" />
<?php    
}

	
echo HTMLHelper::_('form.token'); 
?>
</div>
</form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>   
</div>
