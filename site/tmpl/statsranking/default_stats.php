<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsranking
 * @file       default_stats.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

$input = $this->input;
$playerPlaceholder = PersonImageHelper::placeholder();
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

                $playerName = PersonNameFormatter::format(
                    null,
                    (string) ($row->firstname ?? ''),
                    (string) ($row->nickname ?? ''),
                    (string) ($row->lastname ?? ''),
                    (string) ($this->config['name_format'] ?? '')
                );
                ?>
                <tr<?php echo $favStyle; ?>>
                    <td class="td_r rank"><?php echo $rank; ?></td>

                    <?php if (($this->config['show_picture_thumb'] ?? 0) == 1) : ?>
                        <td class="td_c playerpic">
                            <?php
                            $picture = $row->teamplayerpic ?? null;
                            if (empty($picture) || $picture === $playerPlaceholder)
                            {
                                $picture = $row->picture ?? '';
                            }
                            if (!$picture || !file_exists($picture))
                            {
                                $picture = $playerPlaceholder;
                            }

                            echo ModalImageHelper::render(
                                'person' . $row->person_id,
                                (string) $picture,
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
                            $link = SiteRouteHelper::view('player', [
                                'cfg_which_database' => $input->getInt('cfg_which_database', 0),
                                's' => $input->get('s', ''),
                                'p' => $this->project->id,
                                'tid' => $row->team_id,
                                'pid' => $row->person_id,
                            ]);
                            echo HTMLHelper::link($link, $playerName);
                        }
                        else
                        {
                            echo $playerName;
                        }
                        ?>
                    </td>

                    <?php if (($this->config['show_nation'] ?? 0) == 1) : ?>
                        <td class="td_c playercountry"><?php echo CountryPresentationHelper::flag((string) ($row->country ?? '')); ?></td>
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
                                    $link = SiteRouteHelper::view('teaminfo', [
                                        'cfg_which_database' => $input->getInt('cfg_which_database', 0),
                                        's' => $input->get('s', ''),
                                        'p' => $this->project->id,
                                        'tid' => $teamId,
                                        'ptid' => 0,
                                        'division' => 0,
                                    ]);
                                }
                                else
                                {
                                    $link = null;
                                }

                                $teamForDisplay = clone $team;
                                if (empty($teamForDisplay->project_id))
                                {
                                    $teamForDisplay->project_id = (int) ($this->project->id ?? 0);
                                }

                                echo TeamPresentationHelper::formatName(
                                    $teamForDisplay,
                                    't' . $teamId . 'st' . $rows->id . 'p' . $row->person_id,
                                    $this->config,
                                    $isFavTeam,
                                    $this->project,
                                    $input->getInt('cfg_which_database', 0),
                                    $input->getInt('s', 0),
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
        $link = SiteRouteHelper::view('statsranking', [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->get('s', ''),
            'p' => $this->project->id,
            'division' => (int) ($this->division->id ?? 0),
            'tid' => $this->teamid,
        ]);
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
