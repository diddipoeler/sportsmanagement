<?php
/**
 * Joomla 5/6 overall events ranking layout for the next match view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="nextmatchallovereventsranking">
    <?php
    $this->notes   = [];
    $this->notes[] = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTSRANKING');
    echo $this->loadTemplate('jsm_notes');

    echo HTMLHelper::_('bootstrap.startTabSet', 'myTab2', ['active' => 'name1']);

    $active = 1;

    foreach ($this->overallevents as $value) {
        $ranking = [];
        $eventId = (int) ($value->id ?? 0);

        if ($eventId <= 0) {
            continue;
        }

        foreach ($this->alloverevents as $playerId => $player) {
            $event = $player->events[$eventId] ?? null;
            $eventSum = (int) ($event->event_sum ?? 0);

            if ($eventSum === 0) {
                continue;
            }

            $ranking[] = (object) [
                'playerid' => $playerId,
                'event_sum' => $eventSum,
            ];
        }

        if ($ranking !== []) {
            usort(
                $ranking,
                static fn($first, $second): int => $second->event_sum <=> $first->event_sum
            );
        }

        $width    = 20;
        $height   = 20;
        $type     = 4;
        $imgTitle = Text::_((string) $value->name);
        $icon     = sportsmanagementHelper::getPictureThumb($value->icon, $imgTitle, $width, $height, $type);

        echo HTMLHelper::_(
            'bootstrap.addTab',
            'myTab2',
            'name' . $active,
            $icon . ' ' . Text::_((string) $value->name)
        );
        ?>
        <table class="table table-striped">
            <?php foreach ($ranking as $rankingValue) :
                $player = $this->alloverevents[$rankingValue->playerid] ?? null;

                if ($player === null) {
                    continue;
                }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($player->team_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <?php
                        echo sportsmanagementHelper::formatName(
                            null,
                            $player->firstname1 ?? '',
                            $player->nickname1 ?? '',
                            $player->lastname1 ?? '',
                            $this->config['name_format']
                        );
                        ?>
                    </td>
                    <td>
                        <?php
                        echo sportsmanagementHelperHtml::getBootstrapModalImage(
                            'nextmatchalloverevents' . ($player->playerid ?? $rankingValue->playerid),
                            $player->tppicture1 ?? '',
                            $player->lastname1 ?? '',
                            '20',
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $this->overallconfig['use_jquery_modal']
                        );
                        ?>
                    </td>
                    <td><?php echo (int) $rankingValue->event_sum; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        echo HTMLHelper::_('bootstrap.endTab');
        ++$active;
    }

    echo HTMLHelper::_('bootstrap.endTabSet');
    ?>
</div>
