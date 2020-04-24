<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage nextmatch
 * @file       default_history.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

?>
    <!-- Start of show matches through all projects -->
<?php
if ($this->games)
{
	?>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatch">
        <h4><?php echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_HISTORY'); ?></h4>
        <table class="<?php echo $this->config['hystory_table_class']; ?>">
            <tr>
                <td>
                    <table class="<?php echo $this->config['hystory_table_class']; ?>">
						<?php
						/**
						 *
						 * sort games by dates
						 */
						$gamesByDate = Array();

						$pr_id = 0;
						$k     = 0;

						foreach ($this->games as $game)
						{
							$gamesByDate[$game->project_name][] = $game;
						}

						foreach ($gamesByDate as $date => $games)
						{
							foreach ($games as $game)
							{
								if ($game->prid != $pr_id)
								{
									?>
                                    <thead>
                                    <tr class="sectiontableheader">
                                        <th colspan=10><?php echo $game->project_name; ?></th>
                                    </tr>
                                    </thead>
									<?php
									$pr_id = $game->prid;
								}
								?>
								<?php

								$routeparameter                       = array();
								$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
								$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
								$routeparameter['p']                  = $game->project_slug;
								$routeparameter['r']                  = $game->round_slug;
								$routeparameter['division']           = 0;
								$routeparameter['mode']               = 0;
								$routeparameter['order']              = '';
								$routeparameter['layout']             = '';
								$result_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

								$routeparameter                       = array();
								$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
								$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
								$routeparameter['p']                  = $game->project_slug;
								$routeparameter['mid']                = $game->match_slug;
								$report_link                          = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport', $routeparameter);

								$home = $this->gamesteams[$game->projectteam1_id];
								$away = $this->gamesteams[$game->projectteam2_id];
								?>
                                <tr class="">
                                    <td id="roundcode"><?php
										echo HTMLHelper::link($result_link, $game->roundcode);
                                        /** ereignisse des spiels */
                                        if ($this->config['show_events'])
					{
						
							$events = sportsmanagementModelProject::getMatchEvents($game->id, 0, 0, Factory::getApplication()->input->getInt('cfg_which_database', 0));
							$subs   = sportsmanagementModelProject::getMatchSubstitutions($game->id, Factory::getApplication()->input->getInt('cfg_which_database', 0));

							if ($this->config['use_tabs_events'])
							{
								$hasEvents = (count($events) + count($subs) > 0 && $this->config['show_events']);
							}
							else
							{

								/**
								 * no subs are shown when not using tabs for displaying events so don't check for that
								 */
								$hasEvents = (count($events) > 0 && $this->config['show_events']);
							}

							if ($hasEvents)
							{
								$link   = "javascript:void(0);";
								$img    = HTMLHelper::image('media/com_sportsmanagement/jl_images/events.png', 'events.png');
								$params = array("title"   => Text::_('COM_SPORTSMANAGEMENT_TEAMPLAN_EVENTS'),
								                "onclick" => 'switchMenu(\'info' . $game->id . '\');return false;');
								echo HTMLHelper::link($link, $img, $params);
							}
							
					}
                                        
                                        
                                        
                                        
                                        
										?></td>
                                    <td class="nowrap" id="matchdate"><?php
										if ($game->match_date == '0000-00-00 00:00:00' || empty($game->match_date) || !isset($game->match_date))
										{
											echo Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_DATE_EMPTY');
										}
										else
										{
											echo HTMLHelper::date($game->match_date, Text::_('COM_SPORTSMANAGEMENT_MATCHDAYDATE'));
										}
										?></td>
                                    <td id="matchtime"><?php
										if ($game->match_date == '0000-00-00 00:00:00' || empty($game->match_date) || !isset($game->match_date))
										{
											echo '';
										}
										else
										{
											echo substr($game->match_date, 11, 5);
										}
										?></td>
                                    <td class="nowrap" id="homename"><?php
										echo $home->name;
										?></td>

                                    <td class="nowrap">
										<?php
										if (!sportsmanagementHelper::existPicture($home->picture))
										{
											$home->picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');
										}

										echo sportsmanagementHelperHtml::getBootstrapModalImage(
											'nextmatchprevh' . $game->id . '-' . $game->projectteam1_id,
											$home->picture,
											$home->name,
											'20',
											'',
											$this->modalwidth,
											$this->modalheight,
											$this->overallconfig['use_jquery_modal']
										);

										?>
                                    </td>

                                    <td class="nowrap">-</td>

                                    <td class="nowrap">
										<?php
										if (!sportsmanagementHelper::existPicture($away->picture))
										{
											$away->picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');
										}

										echo sportsmanagementHelperHtml::getBootstrapModalImage(
											'nextmatchprevh' . $game->id . '-' . $game->projectteam2_id,
											$away->picture,
											$away->name,
											'20',
											'',
											$this->modalwidth,
											$this->modalheight,
											$this->overallconfig['use_jquery_modal']
										);
										?>
                                    </td>

                                    <td class="nowrap"><?php
										echo $away->name;
										?></td>
                                    <td class="nowrap"><?php
										echo $game->team1_result;
										?></td>
                                    <td class="nowrap"><?php echo $this->overallconfig['seperator']; ?></td>
                                    <td class="nowrap"><?php
										echo $game->team2_result;
										?></td>
                                    <td class="nowrap"><?php
										if ($game->show_report == 1)
										{
											$desc = HTMLHelper::image(
												Uri::base() . "media/com_sportsmanagement/jl_images/zoom.png",
												Text::_('Match Report'),
												array("title" => Text::_('Match Report'))
											);
											echo HTMLHelper::link($report_link, $desc);
										}

										$k = 1 - $k;
										?></td>
                                </tr>
								<?php
							}
						}
						?>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <!-- End of  show matches through all projects -->
	<?php
}
