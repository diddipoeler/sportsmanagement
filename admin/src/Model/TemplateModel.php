<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/** Native Joomla 5/6 administrator form model for project templates. */
final class TemplateModel extends SportsManagementAdminModel
{
    public function getTable($type = 'template', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * Load the settings XML belonging to the selected template.
     *
     * Template settings intentionally use the legacy-compatible params[] control
     * because the shared administrator edit layout renders these XML forms directly.
     */
    public function getForm($data = [], $loadData = true)
    {
        $input = Factory::getApplication()->getInput();
        $id = (int) ($data['id'] ?? $input->getInt('id'));

        if ($id <= 0) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS'));

            return false;
        }

        $item = $this->getItem($id);

        if (!$item || empty($item->template)) {
            return false;
        }

        $xmlFile = $this->findTemplateXml((string) $item->template, (int) $item->project_id);

        if ($xmlFile === null) {
            $this->setError('Template settings XML not found: ' . (string) $item->template);

            return false;
        }

        try {
            $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
            $form = $formFactory->createForm((string) $item->template, ['control' => 'params']);

            if (!$form->loadFile($xmlFile)) {
                return false;
            }

            if ($loadData) {
                $params = $data['params'] ?? json_decode((string) ($item->params ?? ''), true);
                $form->bind(is_array($params) ? $params : []);
            }

            return $form;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /** Return all own templates plus templates still available from the master project. */
    public function getAllTemplatesList($project_id, $master_id): array
    {
        $projectId = (int) $project_id;
        $masterId = (int) $master_id;
        $db = $this->getDatabase();

        $currentQuery = $db->getQuery(true)
            ->select($db->quoteName('template'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($currentQuery);
        $current = array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));

        $masterTemplates = [];

        if ($masterId > 0) {
            $masterQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('id', 'value'),
                    $db->quoteName('title', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_template_config'))
                ->where($db->quoteName('project_id') . ' = ' . $masterId);

            if ($current) {
                $masterQuery->where(
                    $db->quoteName('template') . ' NOT IN ('
                    . implode(',', array_map([$db, 'quote'], $current)) . ')'
                );
            }

            $masterQuery->order($db->quoteName('title') . ' ASC');
            $db->setQuery($masterQuery);
            $masterTemplates = $db->loadObjectList() ?: [];
        }

        $ownQuery = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('title', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('title') . ' ASC');
        $db->setQuery($ownQuery);
        $ownTemplates = $db->loadObjectList() ?: [];

        foreach (array_merge($ownTemplates, $masterTemplates) as $template) {
            $template->text = Text::_((string) $template->text);
        }

        return array_merge($ownTemplates, $masterTemplates);
    }

    /** Copy one master template configuration to another project. */
    public function import($templateid, $projectid): bool
    {
        $templateId = (int) $templateid;
        $projectId = (int) $projectid;

        if ($templateId <= 0 || $projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('id') . ' = ' . $templateId);
        $db->setQuery($query, 0, 1);
        $row = $db->loadObject();

        if (!$row) {
            return false;
        }

        unset($row->id);
        $row->project_id = $projectId;
        $row->checked_out = 0;
        $row->checked_out_time = $db->getNullDate();
        $row->modified = Factory::getDate()->toSql();
        $row->modified_by = (int) Factory::getApplication()->getIdentity()->id;

        try {
            return (bool) $db->insertObject('#__sportsmanagement_template_config', $row, 'id');
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Add newly introduced XML settings to selected templates while retaining
     * existing values, matching the legacy update action.
     */
    public function update(&$pks): bool
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $pks))));
        $db = $this->getDatabase();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        foreach ($ids as $id) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('template'),
                    $db->quoteName('params'),
                    $db->quoteName('project_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_template_config'))
                ->where($db->quoteName('id') . ' = ' . $id);
            $db->setQuery($query, 0, 1);
            $row = $db->loadObject();

            if (!$row) {
                continue;
            }

            $xmlFile = $this->findTemplateXml((string) $row->template, (int) $row->project_id);

            if ($xmlFile === null) {
                Factory::getApplication()->enqueueMessage(
                    'Template settings XML not found: ' . (string) $row->template,
                    'notice'
                );
                continue;
            }

            try {
                $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
                $form = $formFactory->createForm((string) $row->template, ['control' => 'params']);

                if (!$form->loadFile($xmlFile)) {
                    continue;
                }

                $current = json_decode((string) $row->params, true);
                $form->bind(is_array($current) ? $current : []);
                $params = [];

                foreach ($form->getFieldsets() as $fieldset) {
                    foreach ($form->getFieldset($fieldset->name) as $field) {
                        if (in_array(strtolower((string) $field->type), ['spacer', 'jsmmessage'], true)) {
                            continue;
                        }

                        $params[$field->fieldname] = $field->value;
                    }
                }

                $update = (object) [
                    'id' => $id,
                    'params' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'modified' => Factory::getDate()->toSql(),
                    'modified_by' => $userId,
                ];
                $db->updateObject('#__sportsmanagement_template_config', $update, 'id');
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                    'notice'
                );
            }
        }

        $this->cleanCache();

        return true;
    }

    /** Validate and persist the params[] payload rendered by the dynamic settings form. */
    public function saveTemplateParams(int $id, array $params): bool
    {
        if ($id <= 0) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'));

            return false;
        }

        $form = $this->getForm(['id' => $id], false);

        if (!$form) {
            return false;
        }

        $filtered = $form->filter($params);

        if (!$form->validate($filtered)) {
            foreach ($form->getErrors() as $error) {
                $this->setError($error instanceof \Throwable ? $error->getMessage() : (string) $error);
            }

            return false;
        }

        $encoded = json_encode($filtered, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            $this->setError(json_last_error_msg());

            return false;
        }

        $row = (object) [
            'id' => $id,
            'params' => $encoded,
            'modified' => Factory::getDate()->toSql(),
            'modified_by' => (int) Factory::getApplication()->getIdentity()->id,
        ];

        try {
            $this->getDatabase()->updateObject('#__sportsmanagement_template_config', $row, 'id');
            $this->cleanCache();

            return true;
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }

    public function getProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('master_template'),
                $db->quoteName('season_id'),
                $db->quoteName('project_art_id'),
                $db->quoteName('sports_type_id'),
                $db->quoteName('extension'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getProjectTeamsCount(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    public function getProjectIdForTemplate(int $id): int
    {
        if ($id <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('project_id'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('id') . ' = ' . $id);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    private function findTemplateXml(string $template, int $projectId): ?string
    {
        $default = JPATH_COMPONENT_SITE . '/settings/default/' . $template . '.xml';

        if (is_file($default)) {
            return $default;
        }

        $extensions = [];

        if (!class_exists('sportsmanagementHelper')) {
            if (is_file(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php')) {
                require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
            }
        }

        try {
            $extensions = (array) \sportsmanagementHelper::getExtensions($projectId);
        } catch (\Throwable) {
        }

        foreach ($extensions as $extension) {
            $candidate = JPATH_COMPONENT_SITE . '/extensions/' . basename((string) $extension)
                . '/settings/default/' . $template . '.xml';

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $extensionRoot = JPATH_COMPONENT_SITE . '/extensions';

        if (is_dir($extensionRoot)) {
            foreach (new \DirectoryIterator($extensionRoot) as $directory) {
                if (!$directory->isDir() || $directory->isDot()) {
                    continue;
                }

                $candidate = $directory->getPathname() . '/settings/default/' . $template . '.xml';

                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }
}
