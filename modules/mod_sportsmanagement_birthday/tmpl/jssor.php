<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                GNU General Public License version 2 or later; see LICENSE.txt
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
?>
<script>

jQuery(document).ready(function ($) {
    
    var _SlideshowTransitions = [
        //Fade
<?php echo $params->get('jssor_transition'); ?>
//{$Duration:1600,x:1,$Rows:2,$ChessMode:{$Row:3},$Easing:{$Left:$JssorEasing$.$EaseInOutQuart,$Opacity:$JssorEasing$.$EaseLinear},$Opacity:2,$Brother:{$Duration:1600,x:-1,$Rows:2,$ChessMode:{$Row:3},$Easing:{$Left:$JssorEasing$.$EaseInOutQuart,$Opacity:$JssorEasing$.$EaseLinear},$Opacity:2}}        
        ];
    
    var _CaptionTransitions = [];
        //Left to Right
        _CaptionTransitions["L-R"] = { $Duration: 800, $FlyDirection: 1 };
        //Right to Left
        _CaptionTransitions["R-L"] = { $Duration: 800, $FlyDirection: 2 };
        //Top to Bottom
        _CaptionTransitions["T-B"] = { $Duration: 800, $FlyDirection: 4 };
        //Bottom to Top
        _CaptionTransitions["B-T"] = { $Duration: 800, $FlyDirection: 8 };
            
        var options = {$AutoPlay: true,                     //[Optional] Whether to auto play, to enable slideshow, this option must be set to true, default value is false
        $PlayOrientation: <?php echo $params->get('jssor_playorientation'); ?>,                                //[Optional] Orientation to play slide (for auto play, navigation), 1 horizental, 2 vertical, 5 horizental reverse, 6 vertical reverse, default value is 1
        $FillMode: 4,
        //$SlideWidth: 150,
        //$SlideHeight: 150,

        
        $SlideshowOptions: {                                //[Optional] Options to specify and enable slideshow or not
                $Class: $JssorSlideshowRunner$,                 //[Required] Class to create instance of slideshow
                $Transitions: _SlideshowTransitions,            //[Required] An array of slideshow transitions to play slideshow
                $TransitionsOrder: 1,                           //[Optional] The way to choose transition to play slide, 1 Sequence, 0 Random
                $ShowLink: true                                    //[Optional] Whether to bring slide link on top of the slider when slideshow is running, default value is false
            },

        $CaptionSliderOptions: {                            //[Optional] Options which specifies how to animate caption
                $Class: $JssorCaptionSlider$,                   //[Required] Class to create instance to animate caption
                $CaptionTransitions: _CaptionTransitions,
                $PlayInMode: 1,                                 //[Optional] 0 None (no play), 1 Chain (goes after main slide), 3 Chain Flatten (goes after main slide and flatten all caption animations), default value is 1
                $PlayOutMode: 3                                 //[Optional] 0 None (no play), 1 Chain (goes before main slide), 3 Chain Flatten (goes before main slide and flatten all caption animations), default value is 1
            }
//
//        $ThumbnailNavigatorOptions: {
//                    $Class: $JssorThumbnailNavigator$,              //[Required] Class to create thumbnail navigator instance
//                    $ChanceToShow: 2,                               //[Required] 0 Never, 1 Mouse Over, 2 Always
//
//                    $ActionMode: 0,                                 //[Optional] 0 None, 1 act by click, 2 act by mouse hover, 3 both, default value is 1
//                    $AutoCenter: 3,                                 //[Optional] Auto center thumbnail items in the thumbnail navigator container, 0 None, 1 Horizontal, 2 Vertical, 3 Both, default value is 3
//                    $Lanes: 1,                                      //[Optional] Specify lanes to arrange thumbnails, default value is 1
//                    $SpacingX: 0,                                   //[Optional] Horizontal space between each thumbnail in pixel, default value is 0
//                    $SpacingY: 0,                                   //[Optional] Vertical space between each thumbnail in pixel, default value is 0
//                    $DisplayPieces: 3,                              //[Optional] Number of pieces to display, default value is 1
//                    $ParkingPosition: 0,                          //[Optional] The offset position to park thumbnail
//                    $Orientation: 1,                                //[Optional] Orientation to arrange thumbnails, 1 horizental, 2 vertical, default value is 1
//                    $DisableDrag: false                            //[Optional] Disable drag or not, default value is false
//                }

          
          };                            
        var jssor_slider1 = new $JssorSlider$('<?php echo $container; ?>', options);

        //responsive code begin
        //you can remove responsive code if you don't want the slider scales
        //while window resizes
        function ScaleSlider() {
            var parentWidth = $('#<?php echo $container; ?>').parent().width();
            if (parentWidth) {
                jssor_slider1.$ScaleWidth(parentWidth);
            }
            else
                window.setTimeout(ScaleSlider, 30);
        }
        //Scale slider after document ready
        ScaleSlider();
                                        
        //Scale slider while window load/resize/orientationchange.
        $(window).bind("load", ScaleSlider);
        $(window).bind("resize", ScaleSlider);
        $(window).bind("orientationchange", ScaleSlider);
        //responsive code end
    });
    
</script>

<div id="<?php echo $container; ?>" style="position: relative; top: 0px; left: 0px; width: 600px; height: 300px;">
<!-- Slides Container -->
<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 600px; height: 300px;">
        
<?php echo $html_li; ?>        
        
</div>

<!-- Trigger --> 
<script>
jssor_slider1_starter('<?php echo $container; ?>');
</script> 

</div>

