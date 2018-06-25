<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_fieldsets_3.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fieldsets
 */
defined('_JEXEC') or die('Restricted access');

switch ($this->fieldset) {
    /**
     * für die spielfeldpositionen
     */
    case 'playground_jquery':
        $backgroundimage = JURI::root() . 'media/com_sportsmanagement/rosterground/' . $this->item->picture;
        list($width, $height, $type, $attr) = getimagesize($backgroundimage);
        $picture = JURI::root() . 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
        ?>

        <style type="text/css">
            #draggable { width: 100px; height: 70px; background: silver; }
        </style>

        <div id="start">
            <input type='text' id='text' value='' />
        </div>

        <div id="stop">
            spieler verschieben
        </div>

        <?php
        echo "<div id=\"roster\"   style=\"background-image:url('" . $backgroundimage . "');background-position:left;position:relative;height:" . $height . "px;width:" . $width . "px;\">";
        $schemahome = $this->bildpositionen[$this->item->name];
        $testlauf = 1;
        foreach ($schemahome as $key => $value) {
//<div id="draggable">
            ?>  

            <div id="draggable_<?PHP echo $testlauf; ?>" style="position:absolute; width:103px; left:<?PHP echo $value['heim']['links']; ?>px; top:<?PHP echo $value['heim']['oben']; ?>px; text-align:center;">
                <img class="bild_s" style="width:60px;" id="img_<?PHP echo $testlauf; ?>" src="<?PHP echo $picture; ?>" alt="" /><br />
            </div>
            <?php
            $testlauf++;
        }
        echo "</div>";

        break;
    /**
     * für die trainingsdaten
     */
    case 'training':
        $view = $this->jinput->getCmd('view', 'cpanel');
        ?>                
        <fieldset class="adminform">

            <table class="table">
                <tr>
                    <td class='key' nowrap='nowrap'>
        <?php echo JText::_('JACTION_CREATE'); ?>&nbsp;<input type='checkbox' name='add_trainingData' id='add' value='1' onchange='javascript:submitbutton("<?php echo $view; ?>.apply");' />
                    </td>
                    <td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_DAY'); ?></td>
                    <td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_STARTTIME'); ?></td>
                    <td class='key' style='text-align:center;' width='5%'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_ENDTIME'); ?></td>
                    <td class='key' style='text-align:center;'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_PLACE'); ?></td>
                    <td class='key' style='text-align:center;'><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_NOTES'); ?></td>
                </tr>
        <?php
        if (!empty($this->trainingData)) {
            ?>
                    <input type='hidden' name='tdCount' value='<?php echo count($this->trainingData); ?>' />
                    <?php
                    foreach ($this->trainingData AS $td) {
                        $hours = ($td->time_start / 3600);
                        $hours = (int) $hours;
                        $mins = (($td->time_start - (3600 * $hours)) / 60);
                        $mins = (int) $mins;
                        $startTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
                        $hours = ($td->time_end / 3600);
                        $hours = (int) $hours;
                        $mins = (($td->time_end - (3600 * $hours)) / 60);
                        $mins = (int) $mins;
                        $endTime = sprintf('%02d', $hours) . ':' . sprintf('%02d', $mins);
                        ?>
                        <tr>
                            <td class='key' nowrap='nowrap'>
                                <?php echo JText::_('JACTION_DELETE'); ?>&nbsp;<input type='checkbox' name='delete[]' value='<?php echo $td->id; ?>' onchange='javascript:submitbutton("<?php echo $view; ?>.apply");' />
                            </td>
                            <td nowrap='nowrap' width='5%'><?php echo $this->lists['dayOfWeek'][$td->id]; ?></td>
                            <td nowrap='nowrap' width='5%'>
                                <input class='text' type='text' name='time_start[<?php echo $td->id; ?>]' size='8' maxlength='5' value='<?php echo $startTime; ?>' />
                            </td>
                            <td nowrap='nowrap' width='5%'>
                                <input class='text' type='text' name='time_end[<?php echo $td->id; ?>]' size='8' maxlength='5' value='<?php echo $endTime; ?>' />
                            </td>
                            <td>
                                <input class='text' type='text' name='place[<?php echo $td->id; ?>]' size='40' maxlength='255' value='<?php echo $td->place; ?>' />
                            </td>
                            <td>
                                <textarea class='text_area' name='notes[<?php echo $td->id; ?>]' rows='3' cols='40' value='' /><?php echo $td->notes; ?></textarea>
                                <input type='hidden' name='tdids[]' value='<?php echo $td->id; ?>' />
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
    /**
     * für die hilfe
     */
    case 'help':
        ?>
        <fieldset class='adminform'>

            <?php
            echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_HINT_1');
            ?>
        </fieldset>
        <?php
        break;
    /**
     * für mannschaften des vereines
     */
    case 'teamsofclub':
        if (isset($this->teamsofclub)) {
            ?>
            <fieldset class="adminform">
                <table class="table">
            <?php
            foreach ($this->teamsofclub as $team) {
                ?>
                        <tr>
                            <td>
                                <input type="hidden" name="team_id[]" value="<?php echo $team->id; ?>" />
                                <input type="text" name="team_value_id[]" size='50' maxlength='100' value="<?php echo $team->name; ?>" />
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
            /**
             * für extra felder
             */
            case 'extra_fields':
                ?>
        <fieldset class="adminform">

            <table class="table">
        <?php
        if (isset($this->lists) && $this->lists) {
            for ($p = 0; $p < count($this->lists['ext_fields']); $p++) {
                ?>
                        <tr>
                            <td width="100">
                        <?php echo $this->lists['ext_fields'][$p]->name; ?>
                            </td>
                            <td>
                                <textarea name="extraf[]" cols="100" rows="4"><?php echo isset($this->lists['ext_fields'][$p]->fvalue) ? htmlspecialchars($this->lists['ext_fields'][$p]->fvalue) : "" ?></textarea>
                                <input type="hidden" name="extra_id[]" value="<?php echo $this->lists['ext_fields'][$p]->id ?>" />
                                <input type="hidden" name="extra_value_id[]" value="<?php echo $this->lists['ext_fields'][$p]->value_id ?>" />
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

            /**
             * für die extended daten
             */
            case 'extended':
                if (isset($this->extended)) {
                    foreach ($this->extended->getFieldsets() as $fieldset) {
                        ?>
                <fieldset class="adminform">

                <?php
                $fields = $this->extended->getFieldset($fieldset->name);

                if (!count($fields)) {
                    echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                }

                foreach ($fields as $field) {
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
                    ?>
                </fieldset>
                <?php
            }
        } else {
            echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;
    /**
     * für die extendeduser daten
     */
    case 'extendeduser':
        if (isset($this->extendeduser)) {
            foreach ($this->extendeduser->getFieldsets() as $fieldset) {
                ?>
                <fieldset class="adminform">

                <?php
                $fields = $this->extendeduser->getFieldset($fieldset->name);

                if (!count($fields)) {
                    echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                }

                foreach ($fields as $field) {
                    echo $field->label;
                    echo $field->input;
                }
                ?>
                </fieldset>
                <?php
            }
        } else {
            echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

case 'teamperson':
        if (isset($this->extended)) {
            foreach ($this->extended->getFieldsets() as $fieldset) {
                ?>
                <fieldset class="adminform">

                    <?php
                    $fields = $this->extended->getFieldset($fieldset->name);

                    if (!count($fields)) {
                        echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                    }

                    foreach ($fields as $field) {
                        if (COM_SPORTSMANAGEMENT_JOOMLAVERSION == '2.5') {
                            echo $field->label;
                            echo $field->input;
                        } else {
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
            } else {
                echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
            }

            break;
            
    /**
     * tabellenpositionen in de gruppen/divisionen
     */
    case 'paramsranking':
        if (isset($this->extended)) {
            foreach ($this->extended->getFieldsets() as $fieldset) {
                ?>
                <fieldset class="adminform">

                    <?php
                    $fields = $this->extended->getFieldset($fieldset->name);

                    if (!count($fields)) {
                        echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                    }

                    foreach ($fields as $field) {
                        if (COM_SPORTSMANAGEMENT_JOOMLAVERSION == '2.5') {
                            echo $field->label;
                            echo $field->input;
                        } else {
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
            } else {
                echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
            }

            break;
// für die extended daten
        case 'params':
            if (isset($this->formparams)) {
                foreach ($this->formparams->getFieldsets() as $fieldset) {
                    ?>
                <fieldset class="adminform">

                            <?php
                            $fields = $this->formparams->getFieldset($fieldset->name);

                            if (!count($fields)) {
                                echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
                            }
                            echo '<b><p class="tab-description">' . JText::_($this->description) . '</p></b>';
                            foreach ($fields as $field) {
                                echo $field->label;
                                echo $field->input;
                            }
                            ?>
                </fieldset>
                <?php
            }
        } else {
            echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NO_PARAMS');
        }
        break;

// das ist der standard
    default:
//echo 'fieldset -><pre> '.print_r($this->fieldset,true).'</pre>';
        ?>

        <table class="table">
        <?php
        foreach ($this->form->getFieldset($this->fieldset) as $field):
            //echo 'name -><pre> '.print_r($field,true).'</pre>';
            ?>
                <tr>
                    <td class="key"><?php echo $field->label; ?></td>
                    <td><?php echo $field->input; ?></td>
                    <td>
                <?PHP
                //echo 'field_name -> '.$field->name;
                $suchmuster = array("jform[", "]", "request[");
                $ersetzen = array('', '', '');
                $var_onlinehelp = str_replace($suchmuster, $ersetzen, $field->name);

                switch ($var_onlinehelp) {
                    case 'id':
                        break;
                    default:
                        ?>
                                <a	rel="{handler: 'iframe',size: {x: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH; ?>,y: <?php echo COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT; ?>}}"
                                   href="<?php echo COM_SPORTSMANAGEMENT_HELP_SERVER . 'SM-Backend-Felder:' . $this->jinput->getVar("view") . '-' . $var_onlinehelp; ?>"
                                   class="modal">
                    <?php
                    echo JHtml::_('image', 'media/com_sportsmanagement/jl_images/help.png', JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'), 'title= "' .
                            JText::_('COM_SPORTSMANAGEMENT_HELP_LINK') . '"');
                    ?>
                                </a>

                    <?PHP
                    break;
            }
            ?> 
                    </td>       
                </tr>					
            <?php endforeach; ?>

            <tr>
            <?PHP
            if ($this->fieldset === 'request') {
                ?>
                <script type="text/javascript">
                // var start;
                    /*calculate center points*/

                //var start= new google.maps.LatLng(<?php echo $this->item->latitude ?>,<?php echo $this->item->longitude ?>);
                //var image = 'http://maps.google.com/mapfiles/kml/pal2/icon49.png';

                //jQuery(function(){ // document.ready

                //jQuery("#map").gmap3({
                //  map:{
                //    options: {
                //      center: start,
                //      zoom: 9,
                //      maxZoom: 16 ,
                //      mapTypeId: google.maps.MapTypeId.SATELLITE,
                //      mapTypeId: google.maps.MapTypeId.HYBRID,
                //      navigationControl: true,
                //      mapTypeControlOptions: {
                //       style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                //     },
                //     //scrollwheel: true,
                //      streetViewControl: true
                //    }
                //  }
                //  ,
                //  marker:{
                //    latLng: start,
                //    //position: start,
                //    options: {
                //     icon: new google.maps.MarkerImage(
                //       "http://maps.google.com/mapfiles/kml/pal2/icon49.png",
                //       new google.maps.Size(32, 37, "px", "px")
                //     )
                //    }
                    //}
                //  
                //  }
                //  ,
                //"autofit" )
                //                
                //});



                //setTimeout(function(){
                //  jQuery('#map')
                //    .width("100%")
                //    .height("350px") 
                //    .gmap3({trigger:"resize"})
                //    ;
                //}, 4000);





                </script>            

            </div>
            </div>
            <?PHP
        }
        ?>
        </tr>
        </table>


        <script>
        //      jQuery(function(){
        //        var center = new google.maps.LatLng(51.165691,10.451526);
        //        jQuery("#geocomplete").geocomplete({
        //          map: ".map_canvas",
        //          types: ['establishment'],
        //          country: 'de'
        //        });
        //        var map =  jQuery("#geocomplete").geocomplete("map")
        //        map.setCenter(center);
        //        map.setZoom(6);
        //      });
        </script>


        <?PHP
        break;
}
?>        
