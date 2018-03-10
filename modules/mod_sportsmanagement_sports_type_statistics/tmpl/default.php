<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version                1.0.05
 * @file                   agegroups.php
 * @author                 diddipoeler, stony und svdoldie (diddipoeler@gmx.de)
 * @copyright              Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license                This file is part of SportsManagement.
 *
 *        SportsManagement is free software: you can redistribute it and/or modify
 *        it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  SportsManagement is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Diese Datei ist Teil von SportsManagement.
 *
 *  SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 *  der GNU General Public License, wie von der Free Software Foundation,
 *  Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 *  veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 *  SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 *  OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
 *  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 *  Siehe die GNU General Public License für weitere Details.
 *
 *  Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 *  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

defined('_JEXEC') or die('Restricted access');

//echo 'data -><pre>'.print_r($data,true).'</pre>';

// check if any results returned
if ( $data['projectscount'] == 0 )
{
    echo '<p class="modjlgsports">' . JText::_('MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_NO_PROJECTS') . '</p>';

    return;
}
else
{
    ?>
    <div class="">
        <h4>
            <?php
            if ( $data['sportstype'][0]->icon )
            { ?><img src="<?php echo $data['sportstype'][0]->icon; ?>"
                     alt=""/><?php } ?><?php echo JText::_($data['sportstype'][0]->name); ?>
        </h4>
        <ul class="list-group">
            <?php if ( $params->get('show_project', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS") . '" src="administrator/components/com_sportsmanagement/assets/images/projects.png"/>';
                        echo ' ' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS");
                    }
                    else
                    {
                        echo JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PROJECTS");
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectscount'] ?></span>
                </li>
            <?php } ?>

            <?php if ( $params->get('show_leagues', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES") . '" src="administrator/components/com_sportsmanagement/assets/images/leagues.png"/>';
                        echo ' ' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES");
                    }
                    else
                    {
                        echo JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_LEAGUES");
                    }
                    ?>
                    <span class="badge"><?php echo $data['leaguescount'] ?></span>
                </li>
            <?php } ?>

            <?php if ( $params->get('show_seasons', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS") . '" src="administrator/components/com_sportsmanagement/assets/images/seasons.png"/>';
                        echo ' ' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS");
                    }
                    else
                    {
                        echo JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_SEASONS");
                    }
                    ?>
                    <span class="badge"><?php echo $data['seasonscount'] ?></span>
                </li>
            <?php } ?>

            <?php if ( $params->get('show_playgrounds', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS") . '" src="administrator/components/com_sportsmanagement/assets/images/playground.png"/>';
                        echo ' ' . JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS");
                    }
                    else
                    {
                        echo JText::_("MOD_SPORTSMANAGEMENT_SPORTS_TYPE_STATISTICS_PLAYGROUNDS");
                    }
                    ?>
                    <span class="badge"><?php echo $data['playgroundscount'] ?></span>
                </li>
            <?php } ?>


            <?php if ( $params->get('show_clubs', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_clubs')) . '" src="administrator/components/com_sportsmanagement/assets/images/clubs.png"/>';
                        echo ' ' . JText::_($params->get('text_clubs'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_clubs'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['clubscount'] ?></span>
                </li>
            <?php } ?>


            <?php if ( $params->get('show_teams', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_teams')) . '" src="administrator/components/com_sportsmanagement/assets/images/teams.png"/>';
                        echo ' ' . JText::_($params->get('text_teams'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_teams'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectteamscount'] ?></span>
                </li>
            <?php } ?>
            <?php if ( $params->get('show_players', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_players')) . '" src="administrator/components/com_sportsmanagement/assets/images/players.png"/>';
                        echo ' ' . JText::_($params->get('text_players'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_players'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['personscount'] ?></span>
                </li>
            <?php } ?>
            <?php if ( $params->get('show_divisions', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_divisions')) . '" src="administrator/components/com_sportsmanagement/assets/images/division.png"/>';
                        echo ' ' . JText::_($params->get('text_divisions'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_divisions'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectdivisionscount'] ?></span>
                </li>
            <?php } ?>
            <?php if ( $params->get('show_rounds', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_rounds')) . '" src="administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png"/>';
                        echo ' ' . JText::_($params->get('text_rounds'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_rounds'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectroundscount'] ?></span>
                </li>
            <?php } ?>
            <?php if ( $params->get('show_matches', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_matches')) . '" src="administrator/components/com_sportsmanagement/assets/images/matches.png"/>';
                        echo ' ' . JText::_($params->get('text_matches'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_matches'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectmatchescount'] ?></span>
                </li>
            <?php } ?>
            <?php if ( $params->get('show_player_events', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_player_events')) . '" src="administrator/components/com_sportsmanagement/assets/images/events.png"/>';
                        echo ' ' . JText::_($params->get('text_player_events'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_player_events'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectmatcheseventscount'] ?></span>
                </li>
                <?PHP
                if ( isset($data['projectmatcheseventsnamecount']) )
                {
                    foreach ($data['projectmatcheseventsnamecount'] as $row)
                    {
                        ?>

                        <li class="list-group-item"><?php
                            if ( $params->get('show_icon', 1) == 1 )
                            {
                                echo '<img alt="' . JText::_($row->name) . '" src="' . $row->icon . '"/>';
                                echo ' ' . JText::_($row->name);
                            }
                            else
                            {
                                echo JText::_($row->name);
                            }
                            ?>
                            <span class="badge"><?php echo $row->total ?></span>
                        </li>
                        <?PHP
                    }
                }
                ?>

            <?php } ?>
            <?php if ( $params->get('show_player_stats', 1) == 1 )
            { ?>

                <li class="list-group-item"><?php
                    if ( $params->get('show_icon', 1) == 1 )
                    {
                        echo '<img alt="' . JText::_($params->get('text_player_stats')) . '" src="administrator/components/com_sportsmanagement/assets/images/icon-48-statistics.png"/>';
                        echo ' ' . JText::_($params->get('text_player_stats'));
                    }
                    else
                    {
                        echo JText::_($params->get('text_player_stats'));
                    }
                    ?>
                    <span class="badge"><?php echo $data['projectmatchesstatscount'] ?></span>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php
}
?>
