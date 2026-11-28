<?php
/**
 * Joomla 5/6 dependent SQL list field for SportsManagement forms.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AjaxModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;

final class DependsqlField extends SportsManagementListField
{
    protected $type = 'dependsql';

    /** Native searchable list, with Choices synchronised after dependency changes. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Normalise legacy XML list-field attributes for Joomla, including the multiple-selection flag and automatic form submission for the nation dependency, before delegating field setup.
     * @param \SimpleXMLElement $element Field definition.
     * @param mixed $value Current field value.
     * @param string|null $group Optional form group.
     * @return bool Parent setup result.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['class'] = 'form-select';

        if (isset($element['multiple'])) {
            // Legacy XML often uses multiple="multiple" instead of Joomla's
            // native boolean form. Preserve that behaviour before ListField
            // interprets the attribute.
            $element['multiple'] = in_array(strtolower(trim((string) $element['multiple'])), ['true', '1', 'multiple'], true) ? 'true' : 'false';
        }

        if (trim((string) ($element['depends'] ?? '')) === 'search_nation') {
            $element['onchange'] = 'this.form.submit();';
        }

        return parent::setup($element, $value, $group);
    }

    /**
     * Resolve the dependent field configuration and register its client-side update script when both an AJAX task and dependency are configured. Render the searchable Joomla list input afterwards.
     * @return string Rendered list input.
     */
    protected function getInput(): string
    {
        $state = $this->resolveState();

        if ($state['ajaxTask'] !== '' && $state['depends'] !== '') {
            $this->registerDependencyScript([
                'targetId' => $this->id,
                'depends' => $state['depends'],
                'group' => $state['group'],
                'view' => $state['view'],
                'task' => $state['ajaxTask'],
                'database' => (string) $state['database'],
                'slug' => $state['slug'] ? '1' : '',
                'projectId' => $state['projectId'],
                'country' => (string) $state['keyValue'],
                'clubField' => $state['clubValueField'],
                'clubId' => (string) $state['clubValue'],
            ]);
        }

        return parent::getInput();
    }

    /**
     * Resolve the form state and load the initial options using the configured AJAX task, current value, database, slug and optional club filter.
     * @return array Initial selection options.
     */
    protected function getOptions(): array
    {
        $state = $this->resolveState();

        return $this->loadInitialOptions(
            $state['ajaxTask'],
            $state['value'],
            $state['required'],
            $state['slug'],
            $state['database'],
            $state['clubValue']
        );
    }

    /**
     * Read the field's XML configuration and current administrator form values. Determine the appropriate request or parameter group, including prediction-game and no-request exceptions, and collect the values required for dependent option loading.
     * @return array<string,mixed> Resolved field configuration and form state.
     */
    private function resolveState(): array
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $option = $input->getCmd('option');
        $required = (string) ($this->element['required'] ?? '') === 'true';
        $key = (string) ($this->element['key_field'] ?? 'value') ?: 'value';
        $valueField = (string) ($this->element['value_field'] ?? $this->name) ?: $this->name;
        // A club filter is optional. Do not fall back to the current field name:
        // for team selectors that would turn the selected team id into a club id
        // and filter the selected option out when the form is reloaded.
        $clubValueField = trim((string) (
            $this->element['value_clubid']
            ?? $this->element['club_ids']
            ?? ''
        ));
        $ajaxTask = trim((string) ($this->element['task'] ?? ''));
        $depends = trim((string) ($this->element['depends'] ?? ''));
        $slug = (string) ($this->element['slug'] ?? '') === 'true';
        $noRequest = (bool) (int) ($this->element['norequest'] ?? 0);
        $projectId = (int) $this->form->getValue('id');

        $group = match ($option) {
            'com_modules' => 'params',
            'com_sportsmanagement' => $noRequest ? '' : 'request',
            default => 'request',
        };

        if ($view === 'predictiongame') {
            $group = '';
        }

        return [
            'view' => $view,
            'required' => $required,
            'ajaxTask' => $ajaxTask,
            'depends' => $depends,
            'slug' => $slug,
            'projectId' => $projectId,
            'group' => $group,
            'value' => $this->form->getValue($valueField, $group),
            'keyValue' => $this->form->getValue($key, $group),
            'clubValueField' => $clubValueField,
            'clubValue' => $clubValueField !== ''
                ? $this->form->getValue($clubValueField, $group)
                : null,
            'database' => $this->form->getValue('cfg_which_database', $group),
        ];
    }

    /**
     * Resolve the configured AJAX model getter and return its initial option list. Pass database and club filters only for getters that support the corresponding arguments, and return an empty list when the task or callable is unavailable.
     * @param string $ajaxTask Configured option task.
     * @param mixed $value Current form value.
     * @param bool $required Whether the field is required.
     * @param bool $slug Whether slug values are requested.
     * @param mixed $database Database selector.
     * @param mixed $clubValue Optional club filter.
     * @return array Initial selection options.
     */
    private function loadInitialOptions(
        string $ajaxTask,
        mixed $value,
        bool $required,
        bool $slug,
        mixed $database,
        mixed $clubValue
    ): array {
        if ($ajaxTask === '') {
            return [];
        }

        if (!class_exists(AjaxModel::class)) {
            $modelFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/AjaxModel.php';

            if (is_file($modelFile)) {
                require_once $modelFile;
            }
        }

        $method = 'get' . $ajaxTask;

        if (!is_callable([AjaxModel::class, $method])) {
            return [];
        }

        return match (strtolower($method)) {
            'getprojects',
            'getprojectdivisionsoptions',
            'getprojecteventsoptions',
            'getprojectstatsoptions',
            'getprojectstatoptions',
            'getprojectplayeroptions',
            'getprojectstaffoptions',
            'getprojectcluboptions',
            'getmatchesoptions',
            'getrefereesoptions',
            'getprojecttreenodeoptions' =>
                (array) AjaxModel::$method($value, $required, $slug, $database),
            'getprojectteamoptions' =>
                (array) AjaxModel::$method($value, $required, $slug, $database, $clubValue),
            'getprojectroundoptions' =>
                (array) AjaxModel::$method($value, $required, $slug, null, null, $database),
            default => (array) AjaxModel::$method($value, $required, $slug),
        };
    }

    /**
     * Encode the field configuration for safe embedding in JavaScript and register an inline script for HTML documents. The script binds dependency change listeners, requests updated options from the administrator AJAX endpoint, refreshes the target list and dispatches a change event.
     * @param array $config Dependency identifiers, AJAX task and request context.
     * @return void
     */
    private function registerDependencyScript(array $config): void
    {
        $json = json_encode(
            $config,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            return;
        }

        $script = <<<'JS'
(() => {
    const config = __CONFIG__;

    const byId = (id) => id ? document.getElementById(id) : null;

    const fieldElements = (field) => {
        if (!field) return [];

        const candidates = [];
        if (config.group) {
            candidates.push(`jform_${config.group}_${field}`);
        }
        candidates.push(`jform_${field}`, field);

        for (const id of candidates) {
            const element = byId(id);
            if (!element || !element.matches?.('select, input, textarea')) continue;

            if ((element.type === 'radio' || element.type === 'checkbox') && element.name) {
                const group = Array.from(document.getElementsByName(element.name));
                if (group.length) return group;
            }

            return [element];
        }

        const matches = [];
        for (const element of document.querySelectorAll('select, input, textarea')) {
            const name = element.getAttribute('name') || '';
            if (
                name === field
                || name.endsWith(`[${field}]`)
                || name.endsWith(`[${field}][]`)
            ) {
                matches.push(element);
            }
        }

        return matches;
    };

    const fieldValue = (field) => {
        const elements = fieldElements(field);
        if (!elements.length) return null;

        const first = elements[0];
        if (first instanceof HTMLSelectElement && first.multiple) {
            return Array.from(first.selectedOptions)
                .map((option) => option.value)
                .filter((value) => value !== '')
                .join(',');
        }

        if (elements.some((element) => element.type === 'radio' || element.type === 'checkbox')) {
            return elements
                .filter((element) => element.checked)
                .map((element) => element.value ?? '')
                .filter((value) => value !== '')
                .join(',');
        }

        return first.value ?? '';
    };

    const countryElement = () => byId('jform_country') || byId('jform_request_country');

    let requestVersion = 0;
    const update = async () => {
        const target = byId(config.targetId);
        if (!target) return;
        const version = ++requestVersion;

        const params = new URLSearchParams({
            option: 'com_sportsmanagement',
            format: 'json',
            dbase: fieldValue('cfg_which_database') ?? config.database,
            slug: config.slug,
            task: `ajax.${config.task}`,
        });
        params.set(config.depends, fieldValue(config.depends) ?? '');

        if (config.task === 'projectteamoptions') {
            params.set('club_id', fieldValue(config.clubField) ?? config.clubId);
        }

        if (config.task === 'personagegroupoptions') {
            const country = countryElement();
            if (country) params.set('country', country.value ?? '');
        }

        if (config.view === 'project') {
            params.set('project', String(config.projectId));
        } else if (config.view === 'club') {
            params.set('country', config.country);
        }

        try {
            const response = await fetch(`index.php?${params.toString()}`, {
                headers: {Accept: 'application/json'},
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            if (version !== requestVersion || payload?.success === false) return;
            const rows = Array.isArray(payload)
                ? payload
                : Array.isArray(payload?.data)
                    ? payload.data
                    : Array.isArray(payload?.data?.data)
                        ? payload.data.data
                        : null;
            if (rows === null) return;

            // The native select and Joomla Choices must contain the same options.
            // Keep only selections that remain valid for the new dependency.
            const previous = new Set(Array.from(target.selectedOptions, option => String(option.value)));
            const seen = new Set();
            const options = [];
            for (const row of rows) {
                if (!row || !['string', 'number'].includes(typeof row.value) || typeof row.text !== 'string') continue;
                const value = String(row.value);
                if (seen.has(value)) continue;
                seen.add(value);
                options.push({value, label: row.text, selected: previous.has(value)});
            }
            if (rows.length && !options.length) return;
            if (!target.multiple && options.length && !options.some(option => option.selected)) {
                const placeholder = options.find(option => option.value === '' || option.value === '0');
                (placeholder || options[0]).selected = true;
            }

            const choices = target.closest?.('joomla-field-fancy-select')?.choicesInstance;
            if (choices && typeof choices.clearStore === 'function' && typeof choices.setChoices === 'function') {
                // Both Joomla 5 and 6 expose these Choices methods. Clear selected
                // items too, not just the available choices, to prevent stale IDs.
                choices.clearStore();
                choices.setChoices(options, 'value', 'label', true);
            } else {
                while (target.options.length) target.remove(0);
                for (const option of options) {
                    target.add(new Option(option.label, option.value, false, option.selected));
                }
            }

            if (payload?.messages && window.Joomla?.renderMessages) {
                window.Joomla.renderMessages(payload.messages);
            }

            target.dispatchEvent(new Event('change', {bubbles: true}));
        } catch (error) {
            if (window.console) console.error('SportsManagement dependent field update failed', error);
        }
    };

    const bindField = (field, suffix) => {
        const marker = `data-jsm-dependsql-${String(config.targetId)
            .replace(/[^a-z0-9_-]/gi, '-')
            .toLowerCase()}-${suffix}`;

        for (const source of fieldElements(field)) {
            if (source.hasAttribute(marker)) continue;
            source.setAttribute(marker, '1');
            source.addEventListener('change', update);
        }
    };

    const bind = () => {
        const dependencies = fieldElements(config.depends);
        if (!dependencies.length) return;

        bindField(config.depends, 'primary');

        if (
            config.task === 'projectteamoptions'
            && config.clubField
            && config.clubField !== config.depends
        ) {
            bindField(config.clubField, 'club');
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind, {once: true});
    } else {
        bind();
    }
})();
JS;
        $script = str_replace('__CONFIG__', $json, $script);
        $document = $this->administratorApplication()->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->addInlineScript($script);
        }
    }

    /**
     * Obtain the validated administrator application from the shared application resolver.
     * @return AdministratorApplication Active administrator application.
     */
    private function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
