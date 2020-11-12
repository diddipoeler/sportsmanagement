<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_editdata.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * https://www.joomlashack.com/blog/tutorials/tabs-bootstrap/
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

switch ( $this->view )
{
case 'projectteam':
break;
default:

?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@<?php echo $this->leaflet_version;?>/dist/leaflet.css"
  integrity="<?php echo $this->leaflet_css_integrity;?>"
  crossorigin=""/>
<script src="https://unpkg.com/leaflet@<?php echo $this->leaflet_version;?>/dist/leaflet.js"
  integrity="<?php echo $this->leaflet_js_integrity;?>"
  crossorigin=""></script>
<?php

$this->document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
break;
}
$templatesToLoad = array('footer', 'fieldsets');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
try
{
	$fieldsets = $this->form->getFieldsets();
}
catch (Exception $e)
{
	Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
}
$view = $this->jinput->getCmd('view', 'cpanel');


/**
 *
 * welche joomla version ?
 */
if (version_compare(JSM_JVERSION, '4', 'eq'))
{
	/**
	 * anfang joomla 4 ----------------------------------------------------------------------------------------------
	 */
	?>
    <div>
		<?php
		echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details'));

		foreach ($fieldsets as $fieldset)
		{
			echo HTMLHelper::_('uitab.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
			?>
            <!-- <div class="row"> -->
                <!-- <div class="col-md-12"> -->
					<?PHP
					switch ($fieldset->name)
					{
						case 'details':
							?>
                            <div class="row">
                                <div class="col-lg-6">
									<?PHP
									foreach ($this->form->getFieldset($fieldset->name) as $field)
									{
										?>
                                        <div class="control-group">
                                            <div class="control-label">
												<?php echo $field->label; ?>
                                            </div>
                                            <div class="controls">
												<?php echo $field->input; ?>

												<?PHP
												$suchmuster     = array("jform[", "]", "request[", "params[");
												$ersetzen       = array('', '', '');
												$var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
												switch ($var_onlinehelp)
												{
													case 'ids':
														break;
													default:
														?>
                                                        <a href="#<?php echo $var_onlinehelp; ?>"
                                                           title="<?php echo $var_onlinehelp; ?>" class=""
                                                           data-toggle="modal">
															<?php
                                                            $image_attributes['title'] = 'title= "'.Text::_('COM_SPORTSMANAGEMENT_HELP_LINK') . '"';
															echo HTMLHelper::_(
																'image', 'media/com_sportsmanagement/jl_images/help.png',
																Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'), $image_attributes
															);

															echo HTMLHelper::_(
																'bootstrap.renderModal',
																$var_onlinehelp,
																array(
																	'title'  => Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'),
																	'url'    => COM_SPORTSMANAGEMENT_HELP_SERVER . 'SM-Backend-Felder:' . $this->jinput->getVar("view") . '-' . $var_onlinehelp,
																	'width'  => COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH,
																	'height' => COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT
																)
															);
															?>
                                                        </a>

														<?PHP
														if ($field->name == 'jform[country]')
														{
															echo JSMCountries::getCountryFlag($field->value);
														}

														if ($field->name == 'jform[standard_playground]')
														{
															$picture = sportsmanagementHelper::getPicturePlayground($field->value);
															?>
                                                            <a href="<?php echo Uri::root() . $picture; ?>"
                                                               title="<?php echo 'Playground'; ?>" class="modal">
                                                                <img src="<?php echo Uri::root() . $picture; ?>"
                                                                     alt="<?php echo 'Playground'; ?>" width="50"/>
                                                            </a>
															<?PHP
														}

														if ($field->name == 'jform[website]')
														{
															if ($field->value)
															{
																echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
															}
														}
														if ($field->name == 'jform[twitter]')
														{
															if ($field->value)
															{
																echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
															}
														}
														if ($field->name == 'jform[facebook]')
														{
															if ($field->value)
															{
																echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
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
					case 'player':
					if (!$this->item->latitude)
					{
					$this->item->latitude  = '0.00000000';
					$this->item->longitude = '0.00000000';
					}
					
					?>
                                <div class="col-lg-6">
                                    <div class="control-group">
                                        <style type="text/css">.map_canvas {
                                                width: 100%;
                                                height: 400px;
                                            }</style>
                                        <!-- google map anfang -->
                                        <div id="map_canvas" class="map_canvas">
                                        </div>
                                        <!-- google map ende -->

                                        <!-- leaflet map anfang -->
                                        <div id="map" style="width: 100%;height: 400px; margin-top: 50px; position: absolute;">
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
                                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
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
						case 'events':
							echo $this->loadTemplate('position_events');
							break;
						case 'statistics':
							echo $this->loadTemplate('position_statistics');
							break;
						default:
							$this->fieldset = $fieldset->name;
							echo $this->loadTemplate('fieldsets_4');
							break;
					}
					?>
                <!-- </div> -->
            <!-- </div> -->
			<?PHP
			echo HTMLHelper::_('uitab.endTab');
		}
		echo HTMLHelper::_('uitab.endTabSet');
		?>
    </div>
	<?php
	/**
	 *
	 * ende joomla 4 ----------------------------------------------------------------------------------------------
	 */
}
elseif (version_compare(JSM_JVERSION, '3', 'eq'))
{
	/**
	 * anfang joomla 3 ----------------------------------------------------------------------------------------------
	 */
	?>
    <div class="form-horizontal">
        <fieldset>
			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

			<?PHP
			foreach ($fieldsets	as $fieldset)
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
				case 'player':
                $class_span1 = 'span6';
                $class_span2 = 'span6';
				break;
				default:
                $class_span1 = 'span12';
				break;
				}
				?>
                <div class="<?php echo $class_span1; ?>">
						<?PHP
						foreach ($this->form->getFieldset($fieldset->name) as $field)
						{
							?>
                            <div class="control-group">
                                <div class="control-label span3">
									<?php echo $field->label; ?>
                                </div>
                                <div class="controls span9">
									<?php echo $field->input; ?>

									<?PHP
									$suchmuster     = array("jform[", "]", "request[", "params[");
									$ersetzen       = array('', '', '');
									$var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);
									switch ($var_onlinehelp)
									{
										case 'id':
											break;
										default:
											switch ($field->type)
                                            {
                                            case 'extensionsubtitle':
                                            break;
                                            default:
                                            ?>
                                            <a rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
                                               href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER . 'SM-Backend-Felder:' . $this->jinput->getVar("view") . '-' . $this->form->getName() . '-' . $var_onlinehelp; ?>"
                                               class="modal">
												<?php
												echo HTMLHelper::_(
													'image', 'media/com_sportsmanagement/jl_images/help.png',
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'), 'title= "' .
													Text::_('COM_SPORTSMANAGEMENT_HELP_LINK') . '"'
												);
												?>
                                            </a>

											<?PHP
                                            break;
                                            }
                                            
											if ($field->name == 'jform[country]')
											{
												echo JSMCountries::getCountryFlag($field->value);
											}

											if ($field->name == 'jform[standard_playground]')
											{
												$picture = sportsmanagementHelper::getPicturePlayground($field->value);

												?>
                                                <a href="<?php echo Uri::root() . $picture; ?>"
                                                   title="<?php echo 'Playground'; ?>" class="modal">
                                                    <img src="<?php echo Uri::root() . $picture; ?>"
                                                         alt="<?php echo 'Playground'; ?>" width="50"/>
                                                </a>
												<?PHP
											}

											if ($field->name == 'jform[website]')
											{
												if ($field->value)
												{
													echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
												}
											}
											if ($field->name == 'jform[twitter]')
											{
												if ($field->value)
												{
													echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
												}
											}
											if ($field->name == 'jform[facebook]')
											{
												if ($field->value)
												{
													echo '<img style="" src="http://free.pagepeeker.com/v2/thumbs.php?size=s&url=' . $field->value . '">';
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
					case 'player':
					if (!$this->item->latitude)
					{
					$this->item->latitude  = '0.00000000';
					$this->item->longitude = '0.00000000';
					}
					?>
                    <div class="<?php echo $class_span2; ?>">
                                <div class="control-group">
                                    <style type="text/css">.map_canvas {
                                            width: 100%;
                                            height: 400px;
                                        }</style>
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
                                                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
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
				case 'events':
					echo $this->loadTemplate('position_events');
					break;
				case 'statistics':
					echo $this->loadTemplate('position_statistics');
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

						break;

				}


				?>

				<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
        </fieldset>
    </div>

	<?PHP
	/**
	 *
	 * ende joomla 3 ----------------------------------------------------------------------------------------------
	 */
}
else
{
	?>

    <div class="width-40 fltrt">

        <div class="control-group">
            <style type="text/css">.map_canvas {
                    width: 100%;
                    height: 400px;
                }</style>
            <!-- google map anfang -->
            <div id="map_canvas" class="map_canvas">
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
				echo '<p class="tab-description">' . Text::_($fieldset->description) . '</p>';
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
    <input type="hidden" name="task" value="<?php echo $view; ?>.edit"/>
	<?php
	if ($view == 'teamplayer')
	{
		?>
        <input type="hidden" name="persontype" value="<?php echo $this->_persontype; ?>"/>
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>"/>
        <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>"/>
		<?php
	}

	if ($view == 'treetonode')
	{
		?>
        <input type="hidden" name="project_id" value="<?php echo $this->projectws->id; ?>"/>
        <input type="hidden" name="pid" value="<?php echo $this->projectws->id; ?>"/>
        <input type="hidden" name="tid" value="<?php echo $this->item->treeto_id; ?>"/>
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
