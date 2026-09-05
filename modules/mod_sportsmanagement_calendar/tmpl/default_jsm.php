<?php
/**
 * Joomla 5/6 default SportsManagement calendar layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$moduleId = (int) ($module->id ?? 0);
$display = (int) $params->get('update_module', 1) === 1 ? 'block' : 'none';
$showTeamList = (int) $params->get('show_teamslist', 1) === 1 ? 'visible' : 'hidden';
$teamList = is_array($calendar['teamslist'] ?? null) ? $calendar['teamslist'] : [];
$list = is_array($calendar['list'] ?? null) ? $calendar['list'] : [];
$selectedTeam = (int) ($selected_team ?? 0);
$refreshUrl = rtrim((string) Uri::base(), '/')
    . '/index.php?option=com_ajax&module=sportsmanagement_calendar&method=refresh&format=raw';
?>
<div id="myModal<?php echo $moduleId; ?>" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div id="myModalheader<?php echo $moduleId; ?>" class="modal-header">
                <h2 class="modal-title fs-5"></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="myModalbody<?php echo $moduleId; ?>" class="modal-body"></div>
        </div>
    </div>
</div>

<?php if ((string) $inject_container !== '') : ?>
    <div id="<?php echo htmlspecialchars((string) $inject_container, ENT_QUOTES, 'UTF-8'); ?>"></div>
<?php endif; ?>

<div id="jlccalendar-<?php echo $moduleId; ?>"
     data-jsm-calendar="<?php echo $moduleId; ?>"
     data-refresh-url="<?php echo htmlspecialchars($refreshUrl, ENT_QUOTES, 'UTF-8'); ?>"
     data-inject-container="<?php echo htmlspecialchars((string) $inject_container, ENT_QUOTES, 'UTF-8'); ?>"
     data-calendar-month="<?php echo (int) $month; ?>"
     data-calendar-year="<?php echo (int) $year; ?>">
    <!--jlccalendar-<?php echo $moduleId; ?> start-->

    <?php if (isset($calendar['calendar'])) : ?>
        <?php echo $calendar['calendar']; ?>
    <?php endif; ?>

    <?php if ($teamList !== []) : ?>
        <div class="my-2">
            <?php
            echo HTMLHelper::_(
                'select.genericlist',
                $teamList,
                'jlcteam' . $moduleId,
                'class="form-select" style="width:100%;visibility:' . $showTeamList . ';" data-jsm-calendar-team="' . $moduleId . '"',
                'value',
                'text',
                $selectedTeam
            );
            ?>
        </div>
    <?php endif; ?>

    <?php if ($list !== []) : ?>
        <div>
            <div class="d-none">
                <div id="jlCalList-<?php echo $moduleId; ?>_temp" class="m-2 overflow-auto"></div>
            </div>

            <?php $counter = 0; ?>
            <?php foreach ($list as $row) : ?>
                <?php if (isset($row['tag'])) : ?>
                    <?php switch ((string) $row['tag']) :
                        case 'span': ?>
                            <span id="<?php echo htmlspecialchars((string) ($row['divid'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                  class="<?php echo htmlspecialchars((string) ($row['class'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars((string) ($row['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php break; ?>

                        <?php case 'div': ?>
                            <div id="<?php echo htmlspecialchars((string) ($row['divid'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                 class="<?php echo htmlspecialchars((string) ($row['class'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php break; ?>

                        <?php case 'divend': ?>
                            </div>
                            <?php break; ?>

                        <?php case 'table': ?>
                            <div class="table-responsive">
                                <table class="table table-striped" style="margin:0 auto;min-width:60%">
                            <?php break; ?>

                        <?php case 'tableend': ?>
                                </table>
                            </div>
                            <?php break; ?>

                        <?php case 'headingrow': ?>
                            <tr>
                                <th class="sectiontableheader jlcal_heading" colspan="5" scope="colgroup">
                                    <?php echo htmlspecialchars((string) ($row['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </th>
                            </tr>
                            <?php break; ?>
                    <?php endswitch; ?>
                    <?php continue; ?>
                <?php endif; ?>

                <?php $rowClass = ($counter++ % 2) ? 'sectiontableentry1' : 'sectiontableentry2'; ?>

                <?php if (($row['type'] ?? '') === 'jevents') : ?>
                    <?php
                    $eventColor = (string) ($row['color'] ?? '');
                    $eventStyle = preg_match('/^#[0-9a-f]{3,8}$/i', $eventColor)
                        ? 'border-left:4px solid ' . $eventColor
                        : '';
                    ?>
                    <tr class="<?php echo $rowClass; ?> jlcal_matchrow">
                        <td class="jlcal_jevents" colspan="5"<?php echo $eventStyle !== '' ? ' style="' . $eventStyle . '"' : ''; ?>>
                            <?php if (!empty($row['time'])) : ?>
                                <span class="jlcal_jevents_time"><?php echo htmlspecialchars((string) $row['time'], ENT_QUOTES, 'UTF-8'); ?>: </span>
                            <?php endif; ?>
                            <span class="jlcal_jevents_title">
                                <a href="<?php echo htmlspecialchars((string) ($row['link'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string) ($row['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </span>
                            <?php if (!empty($row['location'])) : ?>
                                – <span class="jlcal_jevents_location"><?php echo htmlspecialchars((string) $row['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php elseif (($row['type'] ?? '') === 'jlb') : ?>
                    <tr class="<?php echo $rowClass; ?> jlcal_matchrow">
                        <td class="jlcal_birthday" colspan="5">
                            <?php echo (string) ($row['image'] ?? ''); ?>
                            <span class="jlc_player_name">
                                <?php if (!empty($row['link'])) : ?>
                                    <a href="<?php echo htmlspecialchars((string) $row['link'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php endif; ?>
                                <?php echo htmlspecialchars((string) ($row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($row['link'])) : ?>
                                    </a>
                                <?php endif; ?>
                            </span>
                            <span class="jlc_player_age"><?php echo htmlspecialchars((string) ($row['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php
                    $timestamp = (int) ($row['timestamp'] ?? 0);
                    $time = $timestamp > 0 ? date('H:i', $timestamp) : '';
                    $link = (string) ($row['link'] ?? '');
                    ?>
                    <tr class="<?php echo $rowClass; ?> jlcal_matchrow">
                        <td class="jlcal_matchdate">
                            <?php if ($link !== '') : ?>
                                <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php else : ?>
                                <?php echo htmlspecialchars($time, ENT_QUOTES, 'UTF-8'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="jlcal_hometeam">
                            <?php echo (string) ($row['homepic'] ?? ''); ?><?php echo htmlspecialchars((string) ($row['homename'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="jlcal_teamseperator">–</td>
                        <td class="jlcal_awayteam">
                            <?php echo (string) ($row['awaypic'] ?? ''); ?><?php echo htmlspecialchars((string) ($row['awayname'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td class="jlcal_result"><?php echo htmlspecialchars((string) ($row['result'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <div style="display:<?php echo $display; ?>">
            <div id="jlCalList-<?php echo $moduleId; ?>" class="overflow-auto"></div>
        </div>
    <?php endif; ?>

    <!--jlccalendar-<?php echo $moduleId; ?> end-->
</div>
<div id="jlcTestlist-<?php echo $moduleId; ?>"></div>
