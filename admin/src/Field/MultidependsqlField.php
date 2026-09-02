<?php
/**
 * Joomla 5/6 replacement for the historical dependent multi-select field.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Joomla 5/6 replacement for the historical dependent multi-select field.
 *
 * The persisted value intentionally remains pipe separated for compatibility
 * with existing module/menu parameters (for example: 1|4|7). The visible
 * selector therefore remains a compatibility renderer while its option data
 * uses Joomla-native value/text objects.
 */
final class MultidependsqlField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'multidependsql';

    protected function getInput(): string
    {
        $keyField = trim((string) ($this->element['key_field'] ?? 'value')) ?: 'value';
        $valueField = trim((string) ($this->element['value_field'] ?? 'text')) ?: 'text';
        $task = trim((string) ($this->element['task'] ?? ''));
        $depends = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($this->element['depends'] ?? ''))
        )));
        $query = trim((string) ($this->element['query'] ?? ''));
        $size = max(1, (int) ($this->element['size'] ?? 10));
        $selected = $this->normaliseSelectedValues($this->value);
        $options = $this->loadQueryOptions($query, $keyField, $valueField);
        $visibleId = $this->id . '_list';
        $visibleName = 'l' . $this->name . '[]';

        $html = HTMLHelper::_(
            'select.genericlist',
            $options,
            $visibleName,
            'class="form-select" multiple="multiple" size="' . $size . '"',
            'value',
            'text',
            $selected,
            $visibleId
        );
        $html .= '<input type="hidden" name="'
            . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars(implode('|', $selected), ENT_QUOTES, 'UTF-8') . '" />';

        $this->registerScript($visibleId, $this->id, $task, $depends, $selected);

        return $html;
    }

    private function normaliseSelectedValues(mixed $value): array
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        $parts = preg_split('/[|,]/', (string) $value) ?: [];

        return array_values(array_unique(array_filter(
            array_map('trim', $parts),
            static fn (string $item): bool => $item !== ''
        )));
    }

    private function loadQueryOptions(string $query, string $keyField, string $valueField): array
    {
        if ($query === '') {
            return [];
        }

        $db = $this->getSportsManagementDatabase();

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }

        $options = [];

        foreach ($rows as $row) {
            if (!isset($row->{$keyField}) && !property_exists($row, $keyField)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $row->{$keyField},
                'text' => (string) ($row->{$valueField} ?? $row->{$keyField}),
            ];
        }

        return $options;
    }

    private function registerScript(
        string $visibleId,
        string $hiddenId,
        string $task,
        array $depends,
        array $selected
    ): void {
        $config = json_encode(
            [
                'visibleId' => $visibleId,
                'hiddenId' => $hiddenId,
                'task' => $task,
                'depends' => $depends,
                'selected' => $selected,
            ],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
        );

        if ($config === false) {
            return;
        }

        $script = <<<'JS'
(() => {
    const config = __CONFIG__;
    const select = document.getElementById(config.visibleId);
    const hidden = document.getElementById(config.hiddenId);
    if (!select || !hidden) return;

    const selectedValues = () => Array.from(select.selectedOptions, option => String(option.value));
    const syncHidden = () => {
        hidden.value = selectedValues().join('|');
    };

    const findDependency = (name) => {
        const ids = [`jform_request_${name}`, `jform_params_${name}`, `jform_${name}`, name];
        for (const id of ids) {
            const element = document.getElementById(id);
            if (element) return element;
        }

        for (const element of document.querySelectorAll('select, input, textarea')) {
            const fieldName = element.getAttribute('name') || '';
            if (fieldName === name || fieldName.endsWith(`[${name}]`)) return element;
        }

        return null;
    };

    const refresh = async () => {
        if (!config.task || !config.depends.length) return;

        const params = new URLSearchParams({
            option: 'com_sportsmanagement',
            format: 'json',
            task: `ajax.${config.task}`,
            required: 'true',
        });

        for (const name of config.depends) {
            const dependency = findDependency(name);
            params.set(name, dependency?.value ?? '');
        }

        const keepSelected = new Set(selectedValues().length ? selectedValues() : config.selected.map(String));

        try {
            const response = await fetch(`index.php?${params.toString()}`, {
                headers: {Accept: 'application/json'},
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            const rows = Array.isArray(payload?.data)
                ? payload.data
                : (Array.isArray(payload) ? payload : []);

            select.replaceChildren();
            for (const row of rows) {
                const value = String(row?.value ?? '');
                const option = new Option(String(row?.text ?? ''), value, false, keepSelected.has(value));
                select.add(option);
            }

            if (payload?.messages && window.Joomla?.renderMessages) {
                window.Joomla.renderMessages(payload.messages);
            }

            syncHidden();
        } catch (error) {
            if (window.console) console.error('SportsManagement multi dependent field update failed', error);
        }
    };

    select.addEventListener('change', syncHidden);

    for (const name of config.depends) {
        const dependency = findDependency(name);
        if (dependency) dependency.addEventListener('change', refresh);
    }

    syncHidden();
    if (config.task && config.depends.length) refresh();
})();
JS;

        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(
            str_replace('__CONFIG__', $config, $script)
        );
    }
}
