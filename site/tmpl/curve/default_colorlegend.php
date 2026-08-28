<?php
/** Native Joomla 5/6 curve color legend. */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($this->config['show_colorlegend'])) {
    return;
}

$colors = $this->colors;
if ($this->input->getString('func', '') !== 'showCurve' && !empty($this->project->fav_team)) {
    $colors[] = [
        'color' => (string) ($this->project->fav_team_color ?? ''),
        'description' => Text::_('COM_SPORTSMANAGEMENT_RANKING_FAVTEAM'),
    ];
}

$entries = [];
foreach ($colors as $color) {
    $value = is_array($color) ? $color : (array) $color;
    $background = trim((string) ($value['color'] ?? ''));
    $description = trim((string) ($value['description'] ?? ''));
    $from = trim((string) ($value['from'] ?? ''));
    $to = trim((string) ($value['to'] ?? ''));

    if ($background === '' && $description === '' && $from === '' && $to === '') {
        continue;
    }

    if ($description === '' && ($from !== '' || $to !== '')) {
        $description = trim($from . ($from !== '' && $to !== '' ? ' - ' : '') . $to);
    }

    $entries[] = [
        'color' => preg_replace('/[^#A-Za-z0-9(),.%\s-]/', '', $background) ?? '',
        'description' => $description,
    ];
}

if ($entries === []) {
    return;
}
?>
<div class="table-responsive mb-3" id="curve-colorlegend">
    <table class="table table-sm">
        <tbody>
        <tr>
            <?php foreach ($entries as $entry) : ?>
                <td class="text-center align-middle">
                    <?php if ($entry['color'] !== '') : ?>
                        <span
                            aria-hidden="true"
                            style="display:inline-block;width:1.25rem;height:1.25rem;vertical-align:middle;background-color:<?php echo $this->escape($entry['color']); ?>;border:1px solid currentColor;"
                        ></span>
                    <?php endif; ?>
                    <?php if ($entry['description'] !== '') : ?>
                        <span class="ms-1"><?php echo $this->escape(Text::_($entry['description'])); ?></span>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
        </tbody>
    </table>
</div>
