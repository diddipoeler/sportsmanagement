<?php
/**
 * Joomla 5/6 replacement for the historical dependent multi-select field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Document\HtmlDocument;
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

    /**
     * Render a native multiple-selection list and its historical hidden value.
     * Loads initial SQL options, preserves the pipe-separated storage format
     * and registers dependency refresh behaviour using Joomla web assets.
     * @return string Rendered select and hidden input markup.
     */
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

    /**
     * Normalise the stored selection without coercing structured values.
     * Accepts the historical single wrapped value and pipe or comma separators,
     * removes empty entries and retains the original order of unique values.
     * @param mixed $value Persisted field value.
     * @return array<int,string> Selected option values.
     */
    private function normaliseSelectedValues(mixed $value): array
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        if (!is_string($value) && !is_int($value)) {
            return [];
        }

        $parts = preg_split('/[|,]/', (string) $value) ?: [];

        return array_values(array_unique(array_filter(
            array_map('trim', $parts),
            static fn (string $item): bool => $item !== ''
        )));
    }

    /**
     * Load initial selection options from the configured XML query.
     * Ignores unavailable query results and malformed rows without converting
     * arrays or objects into option labels or values.
     * @param string $query Trusted field-definition SQL.
     * @param string $keyField Result column containing the option value.
     * @param string $valueField Result column containing the option label.
     * @return array<int,object> Joomla value/text option objects.
     */
    private function loadQueryOptions(string $query, string $keyField, string $valueField): array
    {
        if ($query === '') {
            return [];
        }

        try {
            $db = $this->getSportsManagementDatabase();
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $options = [];

        foreach ($rows as $row) {
            if (!is_object($row)) {
                continue;
            }

            $key = $row->{$keyField} ?? null;
            $label = $row->{$valueField} ?? $key;

            if ((!is_string($key) && !is_int($key))
                || (!is_string($label) && !is_int($label))) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $key,
                'text' => (string) $label,
            ];
        }

        return $options;
    }

    /**
     * Register dependency listeners and asynchronous option updates.
     * Encodes configuration for inline JavaScript and uses the HTML document's
     * native WebAssetManager while keeping the hidden selection synchronized.
     * @param string $visibleId Visible select identifier.
     * @param string $hiddenId Persisted hidden input identifier.
     * @param string $task Configured AJAX task.
     * @param array $depends Dependency field names.
     * @param array $selected Initial selected values.
     * @return void
     */
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

    let initialSelection = true;
    let requestVersion = 0;
    const refresh = async () => {
        if (!config.task || !config.depends.length) return;
        const version = ++requestVersion;

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


        try {
            const response = await fetch(`index.php?${params.toString()}`, {
                headers: {Accept: 'application/json'},
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const payload = await response.json();
            if (version !== requestVersion || payload?.success === false) return;
            const keepSelected = new Set(initialSelection ? config.selected.map(String) : selectedValues());
            const rows = Array.isArray(payload?.data)
                ? payload.data
                : (Array.isArray(payload) ? payload : []);

            initialSelection = false;
            select.replaceChildren();
            for (const row of rows) {
                if (!row || !['string', 'number'].includes(typeof row.value) || !['string', 'number'].includes(typeof row.text)) continue;
                const value = String(row.value);
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

    select.addEventListener('change', () => {
        initialSelection = false;
        syncHidden();
    });

    for (const name of config.depends) {
        const dependency = findDependency(name);
        if (dependency) dependency.addEventListener('change', refresh);
    }

    if (config.task && config.depends.length) refresh();
    else syncHidden();
})();
JS;

        $document = SportsManagementAdministratorApplicationResolver::resolve()->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->addInlineScript(
                str_replace('__CONFIG__', $config, $script)
            );
        }
    }
}
