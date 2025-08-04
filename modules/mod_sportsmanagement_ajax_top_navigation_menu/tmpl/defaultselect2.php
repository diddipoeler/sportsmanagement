<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_ajax_top_navigation_menu
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
/**
https://select2.org/getting-started/installation

https://cdnjs.com/libraries/select2
https://cdnjs.com/libraries/AlertifyJS
*/
$select2 = "progControlSelect2";
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/alertify.min.js"></script>


<script>
$(document).ready(function() {
        var $progControl = $(".progControlSelect2").select2({
             //   placeholder: "Neue Mannschaft",
            //maximumSelectionLength: 2
        });
        $(".AGSPlan").on("click", function() {
                $progControl.val(["aa", "ac", "ae"]).trigger("change");
        });
        $(".TradPlan").on("click", function() {
                $progControl.val(["aa", "ab", "af"]).trigger("change");
        });
        $(".RegStudent").on("click", function() {
                $progControl.val(["ag", "ah", "ai", "aj"]).trigger("change");
        });
        $(".Other").on("click", function() {
                $progControl.val(["ak"]).trigger("change");
        });
        $(".clearSelect2").on("click", function() {
                $progControl.val(null).trigger("change");
        });
        $(".Submit").on("click", function(event) {
                alertify.alert(
                        '<strong class="purple">Silly guy</strong>, This is a fake button.'
                );
        });

        alertify.defaults = {
                glossary: {
                        title: "Nothing to see here...",
                        ok: "I AM a silly guy"
                },
                theme: {
                        input: "ajs-input",
                        ok: "ajs-ok",
                        cancel: "ajs-cancel"
                }
        };

        //Disable select open on option remove
        $("select").on("select2:unselect", function (evt) {
                if (!evt.params.originalEvent) {
                        return;
                }
                evt.params.originalEvent.stopPropagation();
        });


});
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- <link rel='stylesheet' https://cdnjs.cloudflare.com/ajax/libs/AlertifyJS/1.13.1/css/alertify.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/default.min.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/semantic.min.css'> -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/bootstrap.min.css'>    -->


<style>
.container {
    //font-size: 20px;
    //width: 1000px;
}
.title {
    font-size: 16px;
    background: black;
    width: 100%;
    margin: 0;
    padding: 8px;
    color: white;
    text-align: center;
}
.content {
    padding-top: 20px;
}
.full {
    text-align: center;
}
.bg {
    padding: 15px;
    background: #bfe6e4;
}
input[type="button"] {
    display: block;
    width: 200px;
    margin-bottom: 4px;
    cursor: pointer;
}
.btn-success {
    color: #fff;
    background-color: #246b24;
    border-color: #1e5f1e;
}

.btn.btn-default.btn-sm.Other {
    border: 1px solid #292b2c;
    border: 1px solid rgba(0, 0, 0, 0.17);
    background: #464646;
    color: white;
}
.btn.btn-default.btn-sm.Other:hover {
    background: #333333;
}
input.clearSelect2, input.Submit {
    margin-top: 15px;
    width: 99px;
    display: inline-block;
}
.clearSelect2 {
    background: red;
}
.progControlSelect2 {
    width: 350px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    border-radius: 0;
}
.purple {
    color: #980798;
}
.select2-selection__choice__remove {
    color: #5a5a5a !important;
    position: relative;
    top: -1px;
    font-size: 13px;
}
.ajs-button {
    cursor: pointer;
}
@media (min-width: 576px)
_grid.scss:24
.container {
    width: 100%;
    max-width: 100%;
}
</style>


<?php
//echo '<pre>'.print_r($project->project_type,true).'</pre>';

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{	
HTMLHelper::_('behavior.tooltip');
}

$div_col_1 = $params->get('col_tabs');
$div_col_2 = $params->get('col_img');
$div_col_3 = $params->get('col_menu');

?>
    <script type="text/javascript">
        var ajaxmenu_baseurl = '<?php echo Uri::base() ?>';
    </script>

    <div class="container-fluid">

<div class="row">


<div class="col-lg-<?php echo $div_col_1; ?> col-md-<?php echo $div_col_1; ?> col-sm-<?php echo $div_col_1; ?> col-xs-<?php echo $div_col_1; ?>">


        <!-- Nav tabs -->
        <ul class="nav nav-tabs" >

			<?PHP
			foreach ($tab_points as $key => $value)
			{
				$fed_array = strtoupper($value);
				$active    = ($value == $country_federation) ? 'active' : '';
				$selected    = ($value == $country_federation) ? 'show active' : '';
				?>


                <li  class="nav-item">
				<a class="nav-link <?php echo $active ?>"
				
				
                href="#jlajaxtopmenu-<?php echo $value ?><?php echo $module->id ?>"
                
				
                data-bs-toggle="tab">
				<?php echo Text::_(strtoupper($value)) ?>
				</a>
				</li>

				<?PHP
			}
			?>

        </ul>

        <!-- Tab panes -->
        <div class="tab-content">

			<?PHP
			foreach ($tab_points as $key => $value)
			{
				$fed_array = strtoupper($value);
				$active    = ($value == $country_federation) ? 'active' : '';
				$selected    = ($value == $country_federation) ? 'show active' : '';
				?>
                <!--jlajaxtopmenu<?php echo $value ?>-<?php echo $module->id ?> start-->

                <div  class="tab-pane fade <?php echo $selected ?>"
                     id="jlajaxtopmenu-<?php echo $value ?><?php echo $module->id ?>"
					 >
					

                    <table>
                        <tr>
                            <td>

                                <table>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $federationselect[$value], 'jlamtopfederation'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\''.$value.'\');"',  'value', 'text', $country_id);
											echo HTMLHelper::_('select.genericlist', $federationselect[$value], 'jlamtopfederation' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $country_id);
											?>
                                        </td>
                                    </tr>


									<?PHP
									// If ( isset($countryassocselect[$fed_array]['assocs']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $countryassocselect[$fed_array]['assocs'], 'jlamtopassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $assoc_id);
											echo HTMLHelper::_('select.genericlist', $countryassocselect[$fed_array]['assocs'], 'jlamtopassoc' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $assoc_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>



									<?PHP
									// If ( isset($countrysubassocselect[$fed_array]['assocs']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $countrysubassocselect[$fed_array]['assocs'], 'jlamtopsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subassoc_id);
											echo HTMLHelper::_('select.genericlist', $countrysubassocselect[$fed_array]['assocs'], 'jlamtopsubassoc' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subassoc_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>



									<?PHP
									// If ( isset($countrysubsubassocselect[$fed_array]['subassocs']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $countrysubsubassocselect[$fed_array]['subassocs'], 'jlamtopsubsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subsubassoc_id);
											echo HTMLHelper::_('select.genericlist', $countrysubsubassocselect[$fed_array]['subassocs'], 'jlamtopsubsubassoc' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subsubassoc_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>



									<?PHP
									// If ( isset($countrysubsubsubassocselect[$fed_array]['subsubassocs']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $countrysubsubsubassocselect[$fed_array]['subsubassocs'], 'jlamtopsubsubsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subsubsubassoc_id);
											echo HTMLHelper::_('select.genericlist', $countrysubsubsubassocselect[$fed_array]['subsubassocs'], 'jlamtopsubsubsubassoc' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subsubsubassoc_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>



									<?PHP
									// If ( isset($leagueselect[$fed_array]['leagues']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $leagueselect[$fed_array]['leagues'], 'jlamtopleagues'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewprojects('.$module->id.',\''.$value.'\');"',  'value', 'text', $league_id);
											echo HTMLHelper::_('select.genericlist', $leagueselect[$fed_array]['leagues'], 'jlamtopleagues' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $league_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>



									<?PHP
									// If ( isset($projectselect[$fed_array]['projects']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['projects'], 'jlamtopprojects'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewdivisions('.$module->id.',\''.$value.'\');"',  'value', 'text', $project_id);
											echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['projects'], 'jlamtopprojects' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $project_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>


									<?PHP
									// If ( isset($projectselect[$fed_array]['teams']) )
									// {
									?>
                                    <tr>
                                        <td>
											<?PHP
											// Echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['teams'], 'jlamtopteams'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewteams('.$module->id.',\''.$value.'\');"',  'value', 'text', $team_id);
											echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['teams'], 'jlamtopteams' . $value . $module->id, 'class="'.$select2.'" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $team_id);
											?>
                                        </td>
                                    </tr>
									<?PHP
									// }
									?>

                                </table>

                            </td>

                            <td>
                                <table>
                                    <tr>
                                        <td>

                                           


											<?php if ($project_id)
											{
												?>
                                                
											<?php } ?>
                                        </td>

                                        <td>
											<?php if ($team_id)
											{
												?>
                                                <div style="margin: 0 auto;">
                                                    <fieldset class="">

                                                        <!-- <ul class="nav-list"> -->
                                                        <ul class="">
															<?php if ($params->get('show_nav_links'))
																:
																?>

																<?php for ($i = 17; $i < 23; $i++)
																:
																?>
																<?php
																if ($params->get('navpointct' . $i) && $link = $helper->getLink($params->get('navpointct' . $i)))
																	:
																	?>
                                                                    <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), $params->get('navpointct_label' . $i)); ?></li>
																<?php elseif ($params->get('navpointct' . $i) == "separator")
																	:
																	?>
                                                                    <li class="nav-item separator"><?php echo $params->get('navpointct_label' . $i); ?></li>
																<?php endif; ?>
															<?php endfor; ?>


															<?php
															endif;
															?>
                                                        </ul>
                                                    </fieldset>
                                                </div>
											<?php } ?>
                                        </td>

                                    </tr>

                                </table>
                            </td>
                        </tr>
                    </table>

                </div>

                <!--jlajaxtopmenu<?php echo $value ?>-<?php echo $module->id ?> end-->

				<?PHP
			}
			?>

        </div>

</div>
<div class="col-lg-<?php echo $div_col_2; ?> col-md-<?php echo $div_col_2; ?> col-sm-<?php echo $div_col_2; ?> col-xs-<?php echo $div_col_2; ?>">
<?PHP
					if ($country_id)
					{
					$flag =  JSMCountries::getCountryFlag($country_id,'',false,true);	
						?>
                        <img style="float: right;"
                             src="<?php echo $flag; ?>"
                             alt="<?php echo $country_id ?>" width="144" height=""/>
						<?PHP
					}
					?>  
</div>  
<div class="col-lg-<?php echo $div_col_3; ?> col-md-<?php echo $div_col_3; ?> col-sm-<?php echo $div_col_3; ?> col-xs-<?php echo $div_col_3; ?>">
<div style="margin: 0 auto;">
   <ul class="jsmpage pagination" id="pagination">


                                            </ul>
<?php if ($project_id)
											{
												?>
                                                    <fieldset class="">

                                                        <!-- <ul class="nav-list"> -->
                                                        <ul class="" id="ajax-nav-list">
															<?php if ($params->get('show_nav_links'))
																:
																?>

																<?php for ($i = 1; $i < 18; $i++)
																:
																?>
																<?php
																if ($params->get('navpoint' . $i) && $link = $helper->getLink($params->get('navpoint' . $i)))
																	:
																	?>
                                                                    <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), $params->get('navpoint_label' . $i)); ?></li>
																<?php elseif ($params->get('navpoint' . $i) == "separator")
																	:
																	?>
                                                                    <li class="nav-item separator"><?php echo $params->get('navpoint_label' . $i); ?></li>
																<?php endif; ?>
															<?php endfor; ?>


																<?php
																if ( $params->get('show_tournament_nav_links') || $project->project_type == 'TOURNAMENT_MODE' )
																{
																	//$link = $helper->getLink('jltournamenttree')
                                                                    $link = $helper->getLink('tournamentbracket')
																	?>
                                                                    <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), $params->get('show_tournament_text')); ?></li>
																	<?php
																}

																if ($params->get('show_alltimetable_nav_links'))
																{
																	$link = $helper->getLink('rankingalltime')
																	?>
                                                                    <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), $params->get('show_alltimetable_text')); ?></li>
																	<?php
																}

																if ($user_name == 'diddipoeler')
																{
																	$params_new = array("option" => "com_sportsmanagement",
																	                    "view"   => "jlusernewseason",
																	                    "p"      => $project_id);

																	$query = sportsmanagementHelperRoute::buildQuery($params_new);
																	$link  = Route::_('index.php?' . $query, false);
																	?>
                                                                    <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), 'neue Saison'); ?></li>
																	<?php
																}
															endif;

															// If ( $user_name != '' )
															if ($user_name == 'diddipoeler')
															{
																$params_new = array("option" => "com_sportsmanagement",
																                    "view"   => "jlxmlexports",
																                    "p"      => $project_id);

																$query = sportsmanagementHelperRoute::buildQuery($params_new);
																$link  = Route::_('index.php?' . $query, false);
																?>
                                                                <li class="nav-item"><?php echo HTMLHelper::link(Route::_($link), 'XML Export'); ?></li>
																<?php
															}


															?>
                                                        </ul>
                                                    </fieldset>
													<?php } ?>
                                                </div>

</div>
    </div>

    </div>
<?PHP

?>

<?php
if ($ajax && $ajaxmod == $module->id)
{
	exit();
}
