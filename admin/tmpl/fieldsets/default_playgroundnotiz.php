<?php
/**
 * Native Joomla 5/6 administrator playground notes layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function (mixed $value): string {
    $date = trim((string) $value);

    if ($date === '') {
        return '';
    }

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $matches) === 1) {
        return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }

    return $date;
};

$playgroundId = (int) ($this->item->id ?? 0);
?>
<p>Hier können Sie die Notizen zum Stadion hinterlegen, zum Beispiel Name oder unterschiedliche Kapazitäten.</p>

<button type="button" class="btn btn-secondary mb-3" id="jsm-add-playground-note">
    Neuen Eintrag einfügen.
</button>

<table class="table table-striped" id="playgroundnotic">
    <thead>
        <tr>
            <th>id</th>
            <th>playground_id</th>
            <th>löschen?</th>
            <th>date_von</th>
            <th>date_bis</th>
            <th>name_visitors</th>
            <th>notes</th>
            <th>max_visitors</th>
            <th>max_visitors_int</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($this->playgroundnotic ?? []) as $item) : ?>
            <?php
            $id = (int) ($item->id ?? 0);
            $itemPlaygroundId = (int) ($item->playground_id ?? 0);
            ?>
            <tr>
                <td>
                    <input type="hidden" name="change_id[]" value="<?php echo $id; ?>">
                    <?php echo $id; ?>
                </td>
                <td>
                    <input type="hidden" name="change_playground_id[]" value="<?php echo $itemPlaygroundId; ?>">
                    <?php echo $itemPlaygroundId; ?>
                </td>
                <td>
                    <select name="change_delete[]" class="form-select">
                        <option value="0" selected><?php echo Text::_('JNO'); ?></option>
                        <option value="1"><?php echo Text::_('JYES'); ?></option>
                    </select>
                </td>
                <td>
                    <input type="text" name="change_date_von[]" value="<?php echo $escape($formatDate($item->date_von ?? '')); ?>">
                </td>
                <td>
                    <input type="text" name="change_date_bis[]" value="<?php echo $escape($formatDate($item->date_bis ?? '')); ?>">
                </td>
                <td>
                    <input type="hidden" name="name_visitors[]" value="<?php echo $escape($item->name_visitors ?? ''); ?>">
                    <?php echo $escape($item->name_visitors ?? ''); ?>
                </td>
                <td>
                    <input type="text" name="change_notes[]" value="<?php echo $escape($item->notes ?? ''); ?>">
                </td>
                <td>
                    <input type="text" name="change_max_visitors[]" value="<?php echo $escape($item->max_visitors ?? ''); ?>">
                </td>
                <td>
                    <input type="text" name="change_max_visitors_int[]" value="<?php echo $escape($item->max_visitors_int ?? ''); ?>">
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const addButton = document.getElementById('jsm-add-playground-note');
    const tableBody = document.querySelector('#playgroundnotic tbody');

    if (!addButton || !tableBody) {
        return;
    }

    const createInput = (type, name, value = '') => {
        const input = document.createElement('input');
        input.type = type;
        input.name = name;
        input.value = value;
        return input;
    };

    const appendCell = (row, control) => {
        const cell = document.createElement('td');
        cell.append(control);
        row.append(cell);
    };

    addButton.addEventListener('click', () => {
        const row = document.createElement('tr');

        const idInput = createInput('text', 'plnotic_id[]', 'NEW');
        idInput.disabled = true;
        appendCell(row, idInput);

        const playgroundInput = createInput('text', 'playground_id[]', '<?php echo $playgroundId; ?>');
        playgroundInput.disabled = true;
        appendCell(row, playgroundInput);

        appendCell(row, createInput('hidden', 'change_delete[]'));
        appendCell(row, createInput('text', 'date_von[]', '00-00-0000'));
        appendCell(row, createInput('text', 'date_bis[]', '00-00-0000'));

        const visitorType = document.createElement('select');
        visitorType.name = 'name_visitors[]';

        ['NAME', 'VISITORS'].forEach((value) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            visitorType.append(option);
        });

        appendCell(row, visitorType);
        appendCell(row, createInput('text', 'notes[]'));
        appendCell(row, createInput('text', 'max_visitors[]'));
        appendCell(row, createInput('text', 'max_visitors_int[]'));

        tableBody.append(row);
    });
});
</script>
