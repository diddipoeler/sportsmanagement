<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsranking
 * @file       default_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$input = $this->input;
$colspan = 4;
$show_icons = 0;
if (($this->config['show_picture_thumb'] ?? 0) == 1)
{
    $colspan++;
}
if (($this->config['show_nation'] ?? 0) == 1)
{
    $colspan++;
}
if (($this->config['show_icons'] ?? 0) == 1)
{
    $show_icons = 1;
}
?>

<?php foreach ($this->stats as $rows): ?>
    <?php if ($this->multiple_stats == 1) : ?>
        <h2><?php echo Text::_($rows->name); ?></h2>
    <?php endif; ?>
    <table class="<?php echo $this->config['table_class'] ?? 'table'; ?>">
        <thead>
        <tr class="sectiontableheader">
            <th class="td_r rank"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_RANK'); ?></th>

            <?php if (($this->config['show_picture_thumb'] ?? 0) == 1) : ?>
                <th class="td_c">&nbsp;</th>
            <?php endif; ?>

            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_PLAYER_NAME'); ?></th>

            <?php if (($this->config['show_nation'] ?? 0) == 1) : ?>
                <th class="td_c">&nbsp;</th>
            <?php endif; ?>

            <?php if (($this->config['show_team'] ?? 0) == 1) : ?>
                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_TEAM'); ?></th>
            <?php endif; ?>
            <?php if ($show_icons == 1) : ?>
                <th class="td_r nowrap"><?php echo $rows->getImage(); ?></th>
            <?php else: ?>
                <th class="td_r nowrap"><?php echo Text::_($rows->name); ?></th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $ranking = $this->playersstats[$rows->id]->ranking ?? [];
        if (count((array) $ranking) > 0)
        {
            $lastrank = 0;
            foreach ((array) $ranking as $row)
            {
                $rank = $lastrank == $row->rank ? '-' : $row->rank;
                $lastrank = $row->rank;

                $favStyle = '';
                $isFavTeam = in_array((int) $row->team_id, $this->favteams, true);
                if (($this->config['highlight_fav'] ?? 0) == 1 && $isFavTeam && ($this->project->fav_team_highlight_type ?? 0) == 1)
                {
                    $favStyle = ' style="';
                    $favStyle .= !empty($this->project->fav_team_text_bold) ? 'font-weight:bold;' : '';
                    $favStyle .= trim((string) ($this->project->fav_team_text_color ?? '')) !== ''
                        ? 'color:' . trim((string) $this->project->fav_team_text_color) . ';'
                        : '';
                    $favStyle .= trim((string) ($this->project->fav_team_color ?? '')) !== ''
                        ? 'background-color:' . trim((string) $this->project->fav_team_color) . ';'
                        : '';
                    $favStyle = $favStyle !== ' style="' ? $favStyle . '"' : '';
                }

                $playerName = sportsmanagementHelper::formatName(
                    null,
                    $row->firstname,
                    $row->nickname,
                    $row->lastname,
                    $this->config['name_format'] ?? ''
                );
                ?>
                <tr<?php echo $favStyle; ?>>
                    <td class="td_r rank"><?php echo $rank; ?></td>

                    <?php if (($this->config['show_picture_thumb'] ?? 0) == 1) : ?>
                        <td class="td_c playerpic">
                            <?php
                            $picture = $row->teamplayerpic ?? null;
                            if (empty($picture) || $picture == sportsmanagementHelper::getDefaultPlaceholder('player'))
                            {
                                $picture = $row->picture ?? '';
                            }
                            if (!$picture || !file_exists($picture))
                            {
                                $picture = sportsmanagementHelper::getDefaultPlaceholder('player');
                            }

                            echo sportsmanagementHelperHtml::getBootstrapModalImage(
                                'person' . $row->person_id,
                                $picture,
                                $playerName,
                                $this->config['player_picture_width'] ?? 40,
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                $this->overallconfig['use_jquery_modal'] ?? 0
                            );
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="td_l playername">
                        <?php
                        if (($this->config['link_to_player'] ?? 0) == 1)
                        {
                            $routeparameter = [];
                            $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
                            $routeparameter['s'] = $input->get('s', '');
                            $routeparameter['p'] = $this->project->id;
                            $routeparameter['tid'] = $row->team_id;
                            $routeparameter['pid'] = $row->person_id;
                            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
                            echo HTMLHelper::link($link, $playerName);
                        }
                        else
                        {
                            echo $playerName;
                        }
                        ?>
                    </td>

                    <?php if (($this->config['show_nation'] ?? 0) == 1) : ?>
                        <td class="td_c playercountry"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
                    <?php endif; ?>

                    <?php if (($this->config['show_team'] ?? 0) == 1) : ?>
                        <td class="td_l playerteam">
                            <?php
                            $teamId = (int) $row->team_id;
                            $team = $this->teams[$teamId] ?? null;
                            if ($team)
                            {
                                if (($this->config['link_to_team'] ?? 0) == 1 && ($this->project->id ?? 0) > 0 && $teamId > 0)
                                {
                                    $routeparameter = [];
                                    $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
                                    $routeparameter['s'] = $input->get('s', '');
                                    $routeparameter['p'] = $this->project->id;
                                    $routeparameter['tid'] = $teamId;
                                    $routeparameter['ptid'] = 0;
                                    $routeparameter['division'] = 0;
                                    $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
                                }
                                else
                                {
                                    $link = null;
                                }

                                echo sportsmanagementHelper::formatTeamName(
                                    $team,
                                    't' . $teamId . 'st' . $rows->id . 'p' . $row->person_id,
                                    $this->config,
                                    $isFavTeam,
                                    $link
                                );
                            }
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="td_r playertotal"><?php echo $row->total; ?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>

    <?php
    if ($this->multiple_stats == 1)
    {
        $routeparameter = [];
        $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $input->get('s', '');
        $routeparameter['p'] = $this->project->id;
        $routeparameter['division'] = (int) ($this->division->id ?? 0);
        $routeparameter['tid'] = $this->teamid;
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('statsranking', $routeparameter);
        ?>
        <div class="fulltablelink">
            <?php echo HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_VIEW_FULL_TABLE')); ?>
        </div>
        <?php
    }
    else
    {
        $statResult = $this->playersstats[$rows->id] ?? null;
        $paginationTotal = (int) ($statResult->pagination_total ?? 0);
        $pagination = new Pagination($paginationTotal, $this->limitstart, $this->limit);
        ?>
        <div class="pageslinks">
            <?php echo $pagination->getPagesLinks(); ?>
        </div>
        <p class="pagescounter">
            <?php echo $pagination->getPagesCounter(); ?>
        </p>
        <?php
    }
    ?>

<?php endforeach; ?>
