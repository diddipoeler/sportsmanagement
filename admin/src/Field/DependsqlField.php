<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\AjaxModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

final class DependsqlField extends FormField
{
    protected $type = 'dependsql';

    protected function getInput(): string
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $option = $input->getCmd('option');

        $required = (string) ($this->element['required'] ?? '') === 'true';
        $key = (string) ($this->element['key_field'] ?? 'value') ?: 'value';
        $valueField = (string) ($this->element['value_field'] ?? $this->name) ?: $this->name;
        $clubValueField = (string) ($this->element['club_ids'] ?? $this->name) ?: $this->name;
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

        $value = $this->form->getValue($valueField, $group);
        $keyValue = $this->form->getValue($key, $group);
        $clubValue = $this->form->getValue($clubValueField, $group);
        $database = $this->form->getValue('cfg_which_database', $group);

        $attributes = ['class="form-select"'];
        $size = trim((string) ($this->element['size'] ?? ''));
        $multiple = trim((string) ($this->element['multiple'] ?? ''));

        if ($size !== '') {
            $attributes[] = 'size="' . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($multiple !== '') {
            $attributes[] = 'multiple="' . htmlspecialchars($multiple, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($depends === 'search_nation') {
            $attributes[] = 'onchange="this.form.submit();"';
        }

        $options = $this->loadInitialOptions($ajaxTask, $value, $required, $slug, $database);

        if ($ajaxTask !== '' && $depends !== '') {
            $this->registerDependencyScript([
                'targetId' => $this->id,
                'depends' => $depends,
                'group' => $group,
                'view' => $view,
                'task' => $ajaxTask,
                'database' => (string) $database,
                'slug' => $slug ? '1' : '',
                'projectId' => $projectId,
                'country' => (string) $keyValue,
                'clubId' => (string) $clubValue,
            ]);
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            implode(' ', $attributes),
            'value',
            'text',
            $this->value,
            $this->id
        );
    }

    private function loadInitialOptions(string $ajaxTask, mixed $value, bool $required, bool $slug, mixed $database): array
    {
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
            'getprojects', 'getprojectdivisionsoptions', 'getprojectstatsoptions' =>
                (array) AjaxModel::$method($value, $required, $slug, $database),
            'getprojectroundoptions' =>
                (array) AjaxModel::$method($value, $required, $slug, null, null, $database),
            default => (array) AjaxModel::$method($value, $required, $slug),
        };
    }

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
    const dependency = () => {
        const candidates = [];
        if (config.group) {
            candidates.push(`jform_${config.group}_${config.depends}`);
        }
        candidates.push(`jform_${config.depends}`);

        for (const id of candidates) {
            const element = byId(id);
            if (element) return element;
        }

        for (const element of document.querySelectorAll('select, input, textarea')) {
            const name = element.getAttribute('name') || '';
            if (name === config.depends || name.endsWith(`[${config.depends}]`)) return element;
        }

        return null;
    };

    const countryElement = () => byId('jform_country') || byId('jform_request_country');

    const update = async (source) => {
        const target = byId(config.targetId);
        if (!target) return;

        const params = new URLSearchParams({
            option: 'com_sportsmanagement',
            format: 'json',
            dbase: config.database,
            slug: config.slug,
            task: `ajax.${config.task}`,
        });
        params.set(config.depends, source.value ?? '');

        if (config.task === 'projectteamoptions') {
            params.set('club_id', config.clubId);
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

            while (target.options.length) target.remove(0);

            for (const row of Array.isArray(payload.data) ? payload.data : []) {
                target.add(new Option(row.text ?? '', row.value ?? ''));
            }

            if (payload.messages && window.Joomla?.renderMessages) {
                window.Joomla.renderMessages(payload.messages);
            }

            target.dispatchEvent(new Event('change', {bubbles: true}));
        } catch (error) {
            if (window.console) console.error('SportsManagement dependent field update failed', error);
        }
    };

    const bind = () => {
        const source = dependency();
        if (!source) return;

        const marker = `data-jsm-dependsql-${String(config.targetId).replace(/[^a-z0-9_-]/gi, '-').toLowerCase()}`;
        if (source.hasAttribute(marker)) return;
        source.setAttribute(marker, '1');
        source.addEventListener('change', () => update(source));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind, {once: true});
    } else {
        bind();
    }
})();
JS;
        $script = str_replace('__CONFIG__', $json, $script);
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript($script);
    }
}
