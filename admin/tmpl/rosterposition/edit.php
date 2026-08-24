<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$renderFields = static function (array $fields): void {
    foreach ($fields as $field) {
        if (strtolower((string) $field->type) === 'hidden') {
            echo $field->input;
            continue;
        }
        ?>
        <div class="control-group mb-3">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};

$id = (int) ($this->item->id ?? 0);
$pitchBase = Uri::root() . 'images/com_sportsmanagement/database/rosterground/';
$pitchUrl = $pitchBase . rawurlencode((string) $this->pitchPicture);
$maxPlayers = 11;
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=rosterposition&layout=edit&id=' . $id); ?>"
    method="post"
    name="adminForm"
    id="rosterposition-form"
    class="form-validate"
>
    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
                <?php $renderFields($this->form->getFieldset('details')); ?>
            </fieldset>

            <?php if ($this->extended) : ?>
                <fieldset class="options-form mb-4">
                    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'); ?></legend>
                    <p class="text-muted">
                        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITION_EDIT'); ?> —
                        <?php echo htmlspecialchars($this->positionType, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Top</th>
                                <th scope="col">Left</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php for ($position = 1; $position <= $maxPlayers; $position++) : ?>
                                <?php
                                $topName = 'COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_TOP';
                                $leftName = 'COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_LEFT';
                                $topField = $this->extended->getField($topName);
                                $leftField = $this->extended->getField($leftName);
                                ?>
                                <tr<?php echo $position > $this->playerCount ? ' class="text-muted"' : ''; ?>>
                                    <th scope="row"><?php echo $position; ?></th>
                                    <td><?php echo $topField ? $topField->input : ''; ?></td>
                                    <td><?php echo $leftField ? $leftField->input : ''; ?></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            <?php endif; ?>
        </div>

        <div class="col-12 col-xl-7">
            <fieldset class="options-form mb-4">
                <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'); ?></legend>
                <p class="text-muted mb-3">
                    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTER_PLAYGROUND_SELECT_DESC'); ?>
                </p>

                <div
                    id="jsm-roster-pitch"
                    class="position-relative mx-auto border rounded overflow-hidden bg-body-tertiary"
                    data-pitch-base="<?php echo htmlspecialchars($pitchBase, ENT_QUOTES, 'UTF-8'); ?>"
                    style="width:min(100%,578px);aspect-ratio:578/1050;touch-action:none;user-select:none"
                >
                    <img
                        id="jsm-roster-pitch-image"
                        src="<?php echo htmlspecialchars($pitchUrl, ENT_QUOTES, 'UTF-8'); ?>"
                        alt=""
                        class="position-absolute top-0 start-0 w-100 h-100"
                        style="object-fit:fill;pointer-events:none"
                    >

                    <?php for ($position = 1; $position <= $this->playerCount; $position++) : ?>
                        <?php $coordinate = $this->coordinates[$position] ?? ['top' => 0, 'left' => 0]; ?>
                        <button
                            type="button"
                            class="btn btn-primary btn-sm rounded-circle position-absolute p-0 jsm-roster-marker"
                            data-position="<?php echo $position; ?>"
                            data-top="<?php echo (int) $coordinate['top']; ?>"
                            data-left="<?php echo (int) $coordinate['left']; ?>"
                            aria-label="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_PLAYERS') . ' ' . $position, ENT_QUOTES, 'UTF-8'); ?>"
                            style="width:32px;height:32px;line-height:30px;z-index:2;cursor:grab"
                        ><?php echo $position; ?></button>
                    <?php endfor; ?>
                </div>

                <div id="jsm-roster-coordinate-status" class="small text-muted mt-2" aria-live="polite"></div>
            </fieldset>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php
$this->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    const pitch = document.getElementById('jsm-roster-pitch');
    const pitchImage = document.getElementById('jsm-roster-pitch-image');
    const status = document.getElementById('jsm-roster-coordinate-status');
    const picture = document.getElementById('jform_picture');

    if (!pitch) {
        return;
    }

    const intrinsicWidth = 578;
    const intrinsicHeight = 1050;
    const markers = Array.from(pitch.querySelectorAll('.jsm-roster-marker'));

    const field = (position, axis) => document.querySelector(
        `[name="extended[COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_${position}_${axis}]"]`
    );

    const clamp = (value, minimum, maximum) => Math.min(maximum, Math.max(minimum, value));

    const readCoordinate = (position, axis, fallback) => {
        const input = field(position, axis);
        const value = input ? Number.parseInt(input.value, 10) : Number.NaN;

        return Number.isFinite(value) ? value : fallback;
    };

    const placeMarker = (marker) => {
        const rect = pitch.getBoundingClientRect();
        const position = Number.parseInt(marker.dataset.position, 10);
        const top = readCoordinate(position, 'TOP', Number.parseInt(marker.dataset.top || '0', 10));
        const left = readCoordinate(position, 'LEFT', Number.parseInt(marker.dataset.left || '0', 10));
        const markerWidth = marker.offsetWidth || 32;
        const markerHeight = marker.offsetHeight || 32;
        const x = clamp(left / intrinsicWidth * rect.width, 0, Math.max(0, rect.width - markerWidth));
        const y = clamp(top / intrinsicHeight * rect.height, 0, Math.max(0, rect.height - markerHeight));

        marker.style.left = `${x}px`;
        marker.style.top = `${y}px`;
        marker.dataset.top = String(top);
        marker.dataset.left = String(left);
    };

    const placeAll = () => markers.forEach(placeMarker);

    markers.forEach((marker) => {
        let activePointer = null;

        marker.addEventListener('pointerdown', (event) => {
            activePointer = event.pointerId;
            marker.setPointerCapture(event.pointerId);
            marker.style.cursor = 'grabbing';
            event.preventDefault();
        });

        marker.addEventListener('pointermove', (event) => {
            if (activePointer !== event.pointerId) {
                return;
            }

            const rect = pitch.getBoundingClientRect();
            const markerWidth = marker.offsetWidth || 32;
            const markerHeight = marker.offsetHeight || 32;
            const displayLeft = clamp(event.clientX - rect.left - markerWidth / 2, 0, Math.max(0, rect.width - markerWidth));
            const displayTop = clamp(event.clientY - rect.top - markerHeight / 2, 0, Math.max(0, rect.height - markerHeight));
            const left = Math.round(displayLeft / rect.width * intrinsicWidth);
            const top = Math.round(displayTop / rect.height * intrinsicHeight);
            const position = Number.parseInt(marker.dataset.position, 10);
            const topInput = field(position, 'TOP');
            const leftInput = field(position, 'LEFT');

            marker.style.left = `${displayLeft}px`;
            marker.style.top = `${displayTop}px`;
            marker.dataset.top = String(top);
            marker.dataset.left = String(left);

            if (topInput) {
                topInput.value = String(top);
            }

            if (leftInput) {
                leftInput.value = String(left);
            }

            if (status) {
                status.textContent = `#${position}: Top ${top}, Left ${left}`;
            }
        });

        const release = (event) => {
            if (activePointer !== event.pointerId) {
                return;
            }

            activePointer = null;
            marker.style.cursor = 'grab';

            if (marker.hasPointerCapture(event.pointerId)) {
                marker.releasePointerCapture(event.pointerId);
            }
        };

        marker.addEventListener('pointerup', release);
        marker.addEventListener('pointercancel', release);
    });

    document.querySelectorAll('[name^="extended[COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_"]').forEach((input) => {
        input.addEventListener('input', () => {
            const match = input.name.match(/ROSTERPOSITIONS_(\d+)_(TOP|LEFT)/);

            if (!match) {
                return;
            }

            const marker = pitch.querySelector(`.jsm-roster-marker[data-position="${match[1]}"]`);

            if (marker) {
                placeMarker(marker);
            }
        });
    });

    if (picture && pitchImage) {
        picture.addEventListener('change', () => {
            const selected = String(picture.value || '').split('/').pop();

            if (selected) {
                pitchImage.src = pitch.dataset.pitchBase + encodeURIComponent(selected);
            }
        });
    }

    window.addEventListener('resize', placeAll);
    placeAll();
});
JS);
?>
