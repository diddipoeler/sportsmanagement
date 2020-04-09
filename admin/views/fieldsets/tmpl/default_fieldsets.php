<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fieldsets
 * @file       default_fieldsets.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

switch ($this->fieldset)
{
	case 'playground_jquery':
		$backgroundimage = Uri::root() . 'media/com_sportsmanagement/rosterground/' . $this->item->picture;
		list($width, $height, $type, $attr) = getimagesize($backgroundimage);
		$picture = Uri::root() . 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
		?>

        <style type="text/css">
            #draggable {
                width: 100px;
                height: 70px;
                background: silver;
            }
        </style>

        <div id="start">
            <input type='text' id='text' value=''/>
        </div>

        <div id="stop">
            spieler verschieben
        </div>

		<?php
		echo "<div id=\"roster\"   style=\"background-image:url('" . $backgroundimage . "');background-position:left;position:relative;height:" . $height . "px;width:" . $width . "px;\">";
		$schemahome = $this->bildpositionen[$this->item->name];
		$testlauf   = 1;

		foreach ($schemahome as $key => $value)
		{
			// <div id="draggable">
			?>

            <div id="draggable_<?PHP echo $testlauf; ?>"
                 style="position:absolute; width:103px; left:<?PHP echo $value['heim']['links']; ?>px; top:<?PHP echo $value['heim']['oben']; ?>px; text-align:center;">
                <img class="bild_s" style="width:60px;" id="img_<?PHP echo $testlauf; ?>" src="<?PHP echo $picture; ?>"
                     alt=""/><br/>
            </div>
			<?php
			$testlauf++;
		}

		echo "</div>";

		break;
	case 'training':
		$view = Factory::getApplication()->input->getCmd('view', 'cpanel');
		?>
        <fieldset class="adminform">

            <table class='table'>
                <tr>
                    <td class='key' nowrap='nowrap'>
						<?php echo Text::_('JACTION_CREATE'); ?>&nbsp;<input type='checkbox' name='add_trainingData'
                                                                             id='add' value='1'
                                                                             onchange='javascript:submitbutton("<?php echo $view; ?>.apply");'/>
                    </td>
                    <td class='key' style='text-align:center;'
                        width='5%'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_DAY'); ?></td>
                    <td class='key' style='text-align:center;'
                        width='5%'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_STARTTIME'); ?></td>
                    <td class='key' style='text-align:center;'
                        width='5%'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_ENDTIME'); ?></td>
                    <td class='key'
                        style='text-align:center;'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_PLACE'); ?></td>
                    <td class='key'
                        style='text-align:center;'><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_NOTES'); ?></td>
                </tr>
				<?php
				if (!empty($this->trainingData))
				{
					?>
                    <input type='hidden' name='tdCount' value='<?php echo count($this->trainingData); ?>'/>
					<?php
					foreach ($this->trainingData AS $td)
					{
						$hours     = ($td->time_start / 3600);
						$hours     = (int) $hours;
						$mins      = (($td->time_start - (3600 * $hours)) / 60);
						$mins      = (int) $mins;
						$startTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
						$hours     = ($td->time_end / 3600);
						$hours     = (int) $hours;
						$mins      = (($td->time_end - (3600 * $hours)) / 60);
						$mins      = (int) $mins;
						$endTime   = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
						?>
                        <tr>
                            <td class='key' nowrap='nowrap'>
								<?php echo Text::_('JACTION_DELETE'); ?>&nbsp;<input type='checkbox' name='delete[]'
                                                                                     value='<?php echo $td->id; ?>'
                                                                                     onchange='javascript:submitbutton("<?php echo $view; ?>.apply");'/>
                            </td>
                            <td nowrap='nowrap' width='5%'><?php echo $this->lists['dayOfWeek'][$td->id]; ?></td>
                            <td nowrap='nowrap' width='5%'>
                                <input class='text' type='text' name='time_start[<?php echo $td->id; ?>]' size='8'
                                       maxlength='5' value='<?php echo $startTime; ?>'/>
                            </td>
                            <td nowrap='nowrap' width='5%'>
                                <input class='text' type='text' name='time_end[<?php echo $td->id; ?>]' size='8'
                                       maxlength='5' value='<?php echo $endTime; ?>'/>
                            </td>
                            <td>
                                <input class='text' type='text' name='place[<?php echo $td->id; ?>]' size='40'
                                       maxlength='255' value='<?php echo $td->place; ?>'/>
                            </td>
                            <td>
                                <textarea class='text_area' name='notes[<?php echo $td->id; ?>]' rows='3' cols='40'
                                          value=''/><?php echo $td->notes; ?></textarea>
                                <input type='hidden' name='tdids[]' value='<?php echo $td->id; ?>'/>
                            </td>
                        </tr>
						<?php
					}
				}
				?>
            </table>
        </fieldset>
		<?PHP
		break;

	case 'help':
		?>
        <fieldset class='adminform'>

			<?php
			echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_HINT_1');
			?>
        </fieldset>
		<?php
		break;

	// Für mannschaften des vereines
	case 'teamsofclub':
		if (isset($this->teamsofclub))
		{
			?>
            <fieldset class="adminform">
                <table>
					<?php
					foreach ($this->teamsofclub as $team)
					{
						?>
                        <tr>
                            <td>
                                <input type="hidden" name="team_id[]" value="<?php echo $team->id; ?>"/>
                                <input type="text" name="team_value_id[]" size='50' maxlength='100'
                                       value="<?php echo $team->name; ?>"/>
                            </td>
                        </tr>
						<?php
					}
					?>
                </table>
            </fieldset>
			<?PHP
		}
		break;


	// Für extra felder
	case 'extra_fields':
		?>
        <fieldset class="adminform">

            <table>
				<?php
				if ($this->lists)
				{
					for ($p = 0; $p < count($this->lists['ext_fields']); $p++)
					{
						?>
                        <tr>
                            <td width="100">
								<?php echo $this->lists['ext_fields'][$p]->name; ?>
                            </td>
                            <td>
                                <textarea name="extraf[]" cols="100"
                                          rows="4"><?php echo isset($this->lists['ext_fields'][$p]->fvalue) ? htmlspecialchars($this->lists['ext_fields'][$p]->fvalue) : "" ?></textarea>
                                <input type="hidden" name="extra_id[]"
                                       value="<?php echo $this->lists['ext_fields'][$p]->id ?>"/>
                                <input type="hidden" name="extra_value_id[]"
                                       value="<?php echo $this->lists['ext_fields'][$p]->value_id ?>"/>
                            </td>
                        </tr>
						<?php
					}
				}
				?>
            </table>
        </fieldset>
		<?php
		break;

	// Für google maps
	case 'maps':
		?>
        <style type="text/css">
            <!--
            /* die Map */
            #map {
                height: 350px;
                width: 600px;
                margin: 20px auto;
                /*border: 5px solid #969696;*/
            }

            -->
        </style>

        <script type="text/javascript">
            // var start;
            /*calculate center points*/
            var start = new google.maps.LatLng(<?php echo $this->item->latitude ?>,<?php echo $this->item->longitude ?>);
            var image = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';

            jQuery(function () { // document.ready

                jQuery("#map").gmap3({
                        map: {
                            options: {
                                center: start,
                                zoom: 9,
                                maxZoom: 14,
                                //      mapTypeId: google.maps.MapTypeId.SATELLITE,
                                mapTypeId: google.maps.MapTypeId.HYBRID,
                                navigationControl: true,
                                mapTypeControlOptions: {
                                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                                },
                                //scrollwheel: true,
                                streetViewControl: true
                            }
                        }
                        ,
                        marker: {
                            latLng: start,
                            //position: start,
                            options: {
                                icon: new google.maps.MarkerImage(
                                    "http://maps.google.com/mapfiles/kml/pal2/icon49.png",
                                    new google.maps.Size(32, 37, "px", "px")
                                )
                            }
                        }

                    }
                    ,
                    "autofit")

            });


            setTimeout(function () {
                jQuery('#map')
                    .width("600px")
                    .height("350px")
                    .gmap3({trigger: "resize"})
                ;
            }, 4000);


        </script>

        <div id="map" class="ui-widget ui-corner-all ui-helper-clearfix"></div>
		<?PHP
		break;

	// Für google maps
	case 'maps1':
		$plugin       = JPluginHelper::getPlugin('system', 'plugin_googlemap3');
		$paramsPlugin = new Registry($plugin->params);

		$arrPluginParams = array();

		$arrPluginParams[] = "mapType='" . $paramsPlugin->get('mapType', '') . "'";
		$arrPluginParams[] = "zoomWheel='" . $paramsPlugin->get('zoomWheel', '') . "'";
		$arrPluginParams[] = "zoom='" . $paramsPlugin->get('zoom', '') . "'";
		$arrPluginParams[] = "corzoom='" . $paramsPlugin->get('corzoom', '') . "'";
		$arrPluginParams[] = "minzoom='" . $paramsPlugin->get('minzoom', '') . "'";
		$arrPluginParams[] = "maxzoom='" . $paramsPlugin->get('maxzoom', '') . "'";
		$arrPluginParams[] = "showEarthMaptype='" . $paramsPlugin->get('showEarthMaptype', '') . "'";

		// $arrPluginParams[] = "kml='".$this->kmlpath."'";
		$arrPluginParams[] = "kmlrenderer='" . $paramsPlugin->get('kmlrenderer', '') . "'";
		$arrPluginParams[] = "kmlsidebar='" . $paramsPlugin->get('kmlsidebar', '') . "'";
		$arrPluginParams[] = "kmlsbwidth='" . $paramsPlugin->get('kmlsbwidth', '') . "'";
		$arrPluginParams[] = "overview='1'";
		$arrPluginParams[] = "lightbox='1'";

		$arrPluginParams[] = "width='" . $paramsPlugin->get('width', '') . "'";
		$arrPluginParams[] = "height='" . $paramsPlugin->get('height', '') . "'";

		/*
		$params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'|dir='1'|zoomWheel='1'|zoom='".$paramsPlugin->get('zoom','')."'|corzoom='0'|minzoom='0'|maxzoom='19'|
		showEarthMaptype='1'|
		showNormalMaptype='1' |showSatelliteMaptype='1' |showTerrainMaptype='1' |showHybridMaptype='1'
		|kml=''|kmlrenderer='geoxml'|controltype='user'|kmlsidebar='left'|kmlsbwidth='200'|
		lightbox='1'|
		width='".$paramsPlugin->get('width','')."'|height='".$paramsPlugin->get('height','')."' |overview='1'  }";
		*/

		$params = "{mosmap width='500'\|height='400'\|lat='52.052312'\|lon='4.447141'\|
zoom='3'\|mapType='Satellite'\|text='sv DWO'\|tooltip='DWO'\|
marker='1'\|align='center' } ";

		// $params  = "{mosmap mapType='".$paramsPlugin->get('mapType','')."'}";
		echo HTMLHelper::_('content.prepare', $params);

		break;

	// Für google maps
	case 'maps2':
		$document = Factory::getDocument();
		$document->addScript('http://maps.google.com/maps/api/js?&sensor=true');

		// $document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp');
		?>
        <script language="javascript" type="text/javascript">
            var map;

            jQuery(function () { // document.ready
                //function initialize() {
                var start = new google.maps.LatLng(<?php echo $this->item->latitude ?>,<?php echo $this->item->longitude ?>);
                var image = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';
                var myOptions = {
                    zoom: 12,
                    center: start,
                    mapTypeId: google.maps.MapTypeId.HYBRID
                };
                //map = new google.maps.Map($('map'), myOptions);
                map = new google.maps.Map(document.getElementById('map'), myOptions);
                var marker = new google.maps.Marker({
                    position: start,
                    map: map,
                    icon: image,
                    title: '<?php echo $this->item->name ?>'
                });

                kartenwerte();
                //    }
            });
            //google.maps.event.addDomListener(window, 'load', initialize);
            //google.maps.event.trigger(map,'resize');

            function kartenwerte() {
                var mapcenter = map.getCenter();
                $('conf_center_lat').value = mapcenter.lat();
                $('conf_center_lng').value = mapcenter.lng();
                $('conf_start_zoom').value = map.getZoom();

            }

            setTimeout(function () {
                jQuery('#map')
                    .width("400px")
                    .height("400px")
                    .gmap3({trigger: "resize"})
                ;
            }, 4000);

        </script>


        <div id="map" class="ui-widget ui-corner-all ui-helper-clearfix" style="width:400px; height:400px;"></div>

		<?PHP
		break;

	// Für die extended daten
	case 'extended':
		if (isset($this->extended))
		{
			foreach ($this->extended->getFieldsets() as $fieldset)
			{
				?>
                <fieldset class="adminform">

					<?php
					$fields = $this->extended->getFieldset($fieldset->name);

					if (!count($fields))
					{
						echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
					}

					foreach ($fields as $field)
					{
						if (COM_SPORTSMANAGEMENT_JOOMLAVERSION == '2.5')
						{
							echo $field->label;
							echo $field->input;
						}
						else
						{
							?>
                            <div class="control-group">
                                <div class="control-label">
									<?php echo $field->label; ?>
                                </div>
                                <div class="controls">
									<?php echo $field->input; ?>
                                </div>
                            </div>
							<?php
						}
					}
					?>
                </fieldset>
				<?php
			}
		}
		else
		{
			echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
		}
		break;

	// Für die extended daten
	case 'extendeduser':
		if (isset($this->extendeduser))
		{
			foreach ($this->extendeduser->getFieldsets() as $fieldset)
			{
				?>
                <fieldset class="adminform">

					<?php
					$fields = $this->extendeduser->getFieldset($fieldset->name);

					if (!count($fields))
					{
						echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
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
			echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
		}
		break;

	// Für die extended daten
	case 'params':
		if (isset($this->formparams))
		{
			foreach ($this->formparams->getFieldsets() as $fieldset)
			{
				?>
                <fieldset class="adminform">

					<?php
					$fields = $this->formparams->getFieldset($fieldset->name);

					if (!count($fields))
					{
						echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
					}

					echo '<b><p class="tab-description">' . Text::_($this->description) . '</p></b>';

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
			echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
		}
		break;

	// Das ist der standard
	default:
		?>
        <fieldset class="adminform">
            <table class="table">
				<?php
				foreach ($this->form->getFieldset($this->fieldset) as $field)
					:
					?>
                    <tr>
                        <td class="key"><?php echo $field->label; ?></td>
                        <td><?php echo $field->input; ?></td>
                        <td>
							<?PHP
							$suchmuster     = array("jform[", "]", "request[");
							$ersetzen       = array('', '', '');
							$var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);

							switch ($var_onlinehelp)
							{
								case 'id':
									break;
								default:
									?>
                                    <a rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
                                       href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER . 'SM-Backend-Felder:' . Factory::getApplication()->input->getVar("view") . '-' . $this->form->getName() . '-' . $var_onlinehelp; ?>"
                                       class="modal">
										<?php
										echo HTMLHelper::_(
											'image', 'media/com_sportsmanagement/jl_images/help.png', Text::_('COM_SPORTSMANAGEMENT_HELP_LINK'), 'title= "' .
											Text::_('COM_SPORTSMANAGEMENT_HELP_LINK') . '"'
										);
										?>
                                    </a>

									<?PHP
									break;
							}
							?>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>
        </fieldset>
		<?PHP
		break;
}

