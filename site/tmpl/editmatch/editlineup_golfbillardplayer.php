<?php
/**
 * https://getbootstrap.com/docs/5.2/helpers/color-background/
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatPlayer = function (object $player): string {
    $name = PersonNameFormatter::format(
        null,
        (string) ($player->firstname ?? ''),
        (string) ($player->nickname ?? ''),
        (string) ($player->lastname ?? ''),
        $this->default_name_format
    );
    $associationNumber = trim((string) ($player->knvbnr ?? ''));

    return $associationNumber !== '' ? $name . ' (' . $associationNumber . ')' : $name;
};
$assignedPlayers = (array) ($this->lists['team_players_billard_assign'] ?? []);
$findAssignedPlayer = static function (array $players, int $slot) use ($formatPlayer): array {
    foreach ($players as $player) {
        if ((int) ($player->trikot_number ?? 0) !== $slot) {
            continue;
        }

        return [
            'id' => (int) ($player->tpid ?? 0),
            'name' => $formatPlayer($player),
        ];
    }

    return ['id' => 0, 'name' => ''];
};

$notAssignedOptions = [
    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER')),
];

foreach ((array) ($this->lists['team_players_billard'] ?? []) as $player) {
    $notAssignedOptions[] = HTMLHelper::_(
        'select.option',
        (int) ($player->value ?? 0),
        $formatPlayer($player)
    );
}

for ($slot = 1; $slot <= 5; $slot++) :
    $assigned = $findAssignedPlayer($assignedPlayers, $slot);
    ?>
    <div class="row">
        <div class="col-md-1 box">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_PLAYER') . ' ' . $slot; ?>
        </div>
        <div class="col-sm">
            <?php if ($assigned['id'] > 0) : ?>
                <?php echo $escape($assigned['name']); ?>
            <?php else : ?>
                <?php
                echo HTMLHelper::_(
                    'select.genericlist',
                    $notAssignedOptions,
                    'roster[' . $slot . ']',
                    '',
                    'value',
                    'text',
                    0
                );
                ?>
            <?php endif; ?>
        </div>
    </div>
<?php endfor; ?>

<div class="row">
    <div class="text-bg-secondary p-3"></div>
</div>

<?php $captain = $findAssignedPlayer($assignedPlayers, 100); ?>
<div class="row">
    <div class="col-md-1 box">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN'); ?>
    </div>
    <div class="col-sm">
        <?php if ($captain['id'] > 0) : ?>
            <?php echo $escape($captain['name']); ?>
        <?php else : ?>
            <?php echo HTMLHelper::_('select.genericlist', $notAssignedOptions, 'rosterc[]', '', 'value', 'text', 0); ?>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="text-bg-secondary p-3"></div>
</div>

<?php $reserve = $findAssignedPlayer($assignedPlayers, 50); ?>
<div class="row">
    <div class="col-md-1 box">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_RESERVE'); ?>
    </div>
    <div class="col-sm">
        <?php if ($reserve['id'] > 0) : ?>
            <?php echo $escape($reserve['name']); ?>
        <?php else : ?>
            <?php echo HTMLHelper::_('select.genericlist', $notAssignedOptions, 'rosterr[]', '', 'value', 'text', 0); ?>
        <?php endif; ?>
    </div>
</div>
