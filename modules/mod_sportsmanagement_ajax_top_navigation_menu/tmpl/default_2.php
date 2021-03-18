<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.00
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_ajax_top_navigation_menu
 * @file       default_2.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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

?>
    <script type="text/javascript">
        var ajaxmenu_baseurl = '<?php echo Uri::base() ?>';
    </script>

<?PHP

if ($project_id)
{
	$options_slider = array(
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'display'     => 1,
		'show'        => 1,
		'useCookie'   => true, // This must not be a string. Don't use quotes.
	);
}
else
{
	$options_slider = array(
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'display'     => 0,
		'show'        => 0,
		'useCookie'   => true, // This must not be a string. Don't use quotes.
	);
}


echo HTMLHelper::_('sliders.start', 'menueslidername', $options_slider);
echo HTMLHelper::_('sliders.panel', Text::_('MOD_SPORTSMANAGEMENT_AJAX_TOP_NAVIGATION_MENU'), 'menue-params');

// Tabs anzeigen
$idxTab = 100;
echo HTMLHelper::_('tabs.start', 'tabs_ajaxtopmenu', array('useCookie' => 1, 'startOffset' => $startoffset));

foreach ($tab_points as $key => $value)
{
	$fed_array = strtoupper($value);

	echo HTMLHelper::_('tabs.panel', Text::_(strtoupper($value)), 'panelmenue' . ($idxTab++));
	?>

    <div id="jlajaxtopmenu-<?php echo $value ?><?php echo $module->id ?>">

        <!--jlajaxtopmenu<?php echo $value ?>-<?php echo $module->id ?> start-->

		<?PHP

		?>

        <p>
            <img style="float: left;"
                 src="images/com_sportsmanagement/database/laender_karten/dfs_kl_<?php echo strtolower($value) ?>.gif"
                 alt="<?php echo $value ?>" width="144" height=""/>

			<?PHP


			if ($country_id)
			{
				?>
                <img style="float: right;"
                     src="images/com_sportsmanagement/database/laender_karten/<?php echo strtolower($country_id) ?>.gif"
                     alt="<?php echo $country_id ?>" width="144" height=""/>
				<?PHP
			}
			?>

        <table>
            <tr>
                <td>

                    <table>
                        <tr>
                            <td>
								<?PHP
								echo HTMLHelper::_('select.genericlist', $federationselect[$value], 'jlamtopfederation' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $country_id);
								?>
                            </td>
                        </tr>


						<?PHP
						if (isset($countryassocselect[$fed_array]['assocs']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $countryassocselect[$fed_array]['assocs'], 'jlamtopassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $assoc_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>



						<?PHP
						if (isset($countrysubassocselect[$fed_array]['assocs']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $countrysubassocselect[$fed_array]['assocs'], 'jlamtopsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubassoc(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $subassoc_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>



						<?PHP
						if (isset($countrysubsubassocselect[$fed_array]['subassocs']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $countrysubsubassocselect[$fed_array]['subassocs'], 'jlamtopsubsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $subsubassoc_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>



						<?PHP
						if (isset($countrysubsubsubassocselect[$fed_array]['subsubassocs']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $countrysubsubsubassocselect[$fed_array]['subsubassocs'], 'jlamtopsubsubsubassoc' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $subsubsubassoc_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>



						<?PHP
						if (isset($leagueselect[$fed_array]['leagues']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $leagueselect[$fed_array]['leagues'], 'jlamtopleagues' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewprojects(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $league_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>



						<?PHP
						if (isset($projectselect[$fed_array]['projects']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['projects'], 'jlamtopprojects' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewdivisions(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $project_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
						?>


						<?PHP
						if (isset($projectselect[$fed_array]['teams']))
						{
							?>
                            <tr>
                                <td>
									<?PHP
									echo HTMLHelper::_('select.genericlist', $projectselect[$fed_array]['teams'], 'jlamtopteams' . $value . $module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewteams(' . $module->id . ',\'' . $value . '\');"', 'value', 'text', $team_id);
									?>
                                </td>
                            </tr>
							<?PHP
						}
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
                                    <div style="margin: 0 auto;">
                                        <fieldset class="">

                                            <ul class="nav-list">
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
                                    </div>
								<?php } ?>
                            </td>

                            <td>
								<?php if ($team_id)
								{
									?>
                                    <div style="margin: 0 auto;">
                                        <fieldset class="">

                                            <ul class="nav-list">
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
        </p>

        <!--jlajaxtopmenu<?php echo $value ?>-<?php echo $module->id ?> end-->
    </div>

	<?PHP
}

echo HTMLHelper::_('tabs.end');

echo HTMLHelper::_('sliders.end');
?>

<?php
if ($ajax && $ajaxmod == $module->id)
{
	exit();
}
