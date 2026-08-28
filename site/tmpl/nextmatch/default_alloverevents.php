<?php
/**
 * Native Joomla 5/6 project event totals for next-match.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$events = is_array($this->overallevents ?? null) ? $this->overallevents : [];
$players = is_array($this->alloverevents ?? null) ? $this->alloverevents : [];
$tableClass = (string) ($this->config['hystory_table_class'] ?? 'table');
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTS')];
?>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="nextmatch-alloverevents">
    <?php echo $this->loadTemplate('jsm_notes'); ?>

    <?php if ($players) : ?>
        <table class="<?php echo $escape($tableClass); ?>">
            <thead>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TEAM'); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PLAYER_NAME'); ?></th>
                <th></th>
                <?php foreach ($events as $event) : ?>
                    <?php
                    $title = Text::_((string) ($event->name ?? ''));
                    $icon = trim((string) ($event->icon ?? ''));
                    if ($icon !== '' && !str_contains($icon, '/')) {
                        $icon = 'media/com_sportsmanagement/events/' . $icon;
                    }
                    ?>
                    <th title="<?php echo $escape($title); ?>">
                        <?php if ($icon !== '') : ?>
                            <?php echo HTMLHelper::image($icon, $title, ['title' => $title, 'height' => 20]); ?>
                        <?php else : ?>
                            <?php echo $escape($title); ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($players as $player) : ?>
                <?php
                $name = PersonNameFormatter::format(
                    null,
                    (string) ($player->firstname1 ?? ''),
                    (string) ($player->nickname1 ?? ''),
                    (string) ($player->lastname1 ?? ''),
                    $this->config['name_format'] ?? 0
                );
                $picture = PersonImageHelper::url(
                    PersonImageHelper::resolve((string) ($player->tppicture1 ?? ''))
                );
                ?>
                <tr>
                    <td><?php echo $escape($player->team_name ?? ''); ?></td>
                    <td><?php echo $name; ?></td>
                    <td>
                        <?php if ($picture !== '') : ?>
                            <?php echo ModalImageHelper::render(
                                'nextmatch-alloverevents-player-' . (int) ($player->playerid ?? 0),
                                $picture,
                                strip_tags($name),
                                20,
                                '',
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            ); ?>
                        <?php endif; ?>
                    </td>
                    <?php foreach ($events as $event) : ?>
                        <?php $eventId = (int) ($event->id ?? 0); ?>
                        <td><?php echo $escape($player->events[$eventId]->event_sum ?? 0); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p class="text-muted mb-0"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
    <?php endif; ?>
</div>
