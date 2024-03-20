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
											echo HTMLHelper::_('select.genericlist', $federationselect[$value], 'jlamtopfederation' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $country_id);
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
											echo HTMLHelper::_('select.genericlist', $countryassocselect[$fed_array]['assocs'], 'jlamtopassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $assoc_id);
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
											echo HTMLHelper::_('select.genericlist', $countrysubassocselect[$fed_array]['assocs'], 'jlamtopsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subassoc_id);
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
											echo HTMLHelper::_('select.genericlist', $countrysubsubassocselect[$fed_array]['subassocs'], 'jlamtopsubsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subsubassoc_id);
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
											echo HTMLHelper::_('select.genericlist', $countrysubsubsubassocselect[$fed_array]['subsubassocs'], 'jlamtopsubsubsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $subsubsubassoc_id);
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
											echo HTMLHelper::_('select.genericlist', $leagueselect[$fed_array]['leagues'], 'jlamtopleagues' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $league_id);
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
											echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['projects'], 'jlamtopprojects' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $project_id);
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
											echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['teams'], 'jlamtopteams' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange=""', 'value', 'text', $team_id);
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
																if ($params->get('show_tournament_nav_links'))
																{
																	$link = $helper->getLink('jltournamenttree')
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
