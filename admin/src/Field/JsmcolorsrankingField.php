<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraSelectOptionsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class JsmcolorsrankingField extends FormField
{
    protected $type = 'jsmcolorsranking';

    protected function getInput(): string
    {
        $rankingTeams = max(0, (int) ($this->element['rankingteams'] ?? 0));
        $templateName = trim((string) ($this->element['templatename'] ?? ''));
        $templateField = trim((string) ($this->element['name'] ?? $this->fieldname));
        $rankingOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')),
        ];

        for ($rank = 1; $rank <= $rankingTeams; ++$rank) {
            $rankingOptions[] = HTMLHelper::_('select.option', (string) $rank, (string) $rank);
        }

        $textOptions = (new ExtraSelectOptionsHelper())->getOptions($templateName, $templateField);
        $textSelectOptions = [];

        if ($textOptions !== []) {
            $textSelectOptions[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));

            foreach ($textOptions as $option) {
                $textSelectOptions[] = HTMLHelper::_('select.option', $option->value, $option->text);
            }
        }

        $values = is_array($this->value) ? $this->value : [];
        $html = [
            '<div class="table-responsive">',
            '<table class="table table-sm align-middle mb-0">',
            '<thead><tr>',
            '<th scope="col">von</th>',
            '<th scope="col">bis</th>',
            '<th scope="col">farbe</th>',
            '<th scope="col">text</th>',
            '</tr></thead>',
            '<tbody>',
        ];

        for ($row = 1; $row <= $rankingTeams; ++$row) {
            $rowValue = array_replace(
                ['von' => '', 'bis' => '', 'color' => '', 'text' => ''],
                isset($values[$row]) && is_array($values[$row]) ? $values[$row] : []
            );
            $color = (string) $rowValue['color'];
            $pickerColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $color)
                ? '#' . ltrim($color, '#')
                : '#ffffff';
            $colorId = $this->id . '_' . $row . '_color';
            $pickerId = $this->id . '_' . $row . '_picker';

            $html[] = '<tr>';
            $html[] = '<td>' . HTMLHelper::_(
                'select.genericlist',
                $rankingOptions,
                $this->name . '[' . $row . '][von]',
                'class="form-select form-select-sm"',
                'value',
                'text',
                (string) $rowValue['von']
            ) . '</td>';
            $html[] = '<td>' . HTMLHelper::_(
                'select.genericlist',
                $rankingOptions,
                $this->name . '[' . $row . '][bis]',
                'class="form-select form-select-sm"',
                'value',
                'text',
                (string) $rowValue['bis']
            ) . '</td>';
            $html[] = '<td><div class="d-flex gap-2 align-items-center">'
                . '<input type="text" class="form-control form-control-sm jsm-ranking-color-value" id="'
                . htmlspecialchars($colorId, ENT_QUOTES, 'UTF-8') . '" name="'
                . htmlspecialchars($this->name . '[' . $row . '][color]', ENT_QUOTES, 'UTF-8') . '" value="'
                . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '" size="8" maxlength="7"'
                . ($color !== '' ? ' style="background-color:' . htmlspecialchars($pickerColor, ENT_QUOTES, 'UTF-8') . ';"' : '')
                . '>'
                . '<input type="color" class="form-control form-control-color jsm-ranking-color-picker" id="'
                . htmlspecialchars($pickerId, ENT_QUOTES, 'UTF-8') . '" value="'
                . htmlspecialchars($pickerColor, ENT_QUOTES, 'UTF-8') . '" data-target="'
                . htmlspecialchars($colorId, ENT_QUOTES, 'UTF-8') . '" aria-label="Farbe wählen">'
                . '</div></td>';

            if ($textSelectOptions !== []) {
                $html[] = '<td>' . HTMLHelper::_(
                    'select.genericlist',
                    $textSelectOptions,
                    $this->name . '[' . $row . '][text]',
                    'class="form-select form-select-sm"',
                    'value',
                    'text',
                    (string) $rowValue['text']
                ) . '</td>';
            } else {
                $html[] = '<td><input type="text" class="form-control form-control-sm" name="'
                    . htmlspecialchars($this->name . '[' . $row . '][text]', ENT_QUOTES, 'UTF-8') . '" value="'
                    . htmlspecialchars((string) $rowValue['text'], ENT_QUOTES, 'UTF-8') . '" size="40"></td>';
            }

            $html[] = '</tr>';
        }

        $html[] = '</tbody></table></div>';
        $this->registerColorPickerScript();

        return implode('', $html);
    }

    private function registerColorPickerScript(): void
    {
        $document = Factory::getApplication()->getDocument();

        if (!method_exists($document, 'getWebAssetManager')) {
            return;
        }

        $document->getWebAssetManager()->addInlineScript(<<<'JS'
document.addEventListener('input', (event) => {
    const picker = event.target.closest('.jsm-ranking-color-picker');

    if (picker) {
        const target = document.getElementById(picker.dataset.target || '');

        if (target) {
            target.value = picker.value;
            target.style.backgroundColor = picker.value;
        }

        return;
    }

    const input = event.target.closest('.jsm-ranking-color-value');

    if (!input) {
        return;
    }

    const normalized = input.value.trim();

    if (/^#?[0-9a-f]{6}$/i.test(normalized)) {
        const color = '#' + normalized.replace(/^#/, '');
        input.style.backgroundColor = color;
        const pickerInput = document.querySelector('.jsm-ranking-color-picker[data-target="' + CSS.escape(input.id) + '"]');

        if (pickerInput) {
            pickerInput.value = color;
        }
    } else {
        input.style.backgroundColor = '';
    }
});
JS);
    }
}
