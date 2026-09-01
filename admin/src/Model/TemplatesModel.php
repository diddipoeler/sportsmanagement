<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for project template settings. */
final class TemplatesModel extends SportsManagementListModel
{
    private const LEGACY_TEMPLATES = [
        'frontpage',
        'do_tipsl',
        'tipranking',
        'tipresults',
        'user',
        'tippentry',
        'tippoverall',
        'tippranking',
        'tippresults',
        'tipprules',
        'tippusers',
        'predictionentry',
        'predictionoverall',
        'predictionranking',
        'predictionresults',
        'predictionrules',
        'predictionusers',
    ];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'tmpl.template', 'template',
            'tmpl.title', 'title',
            'tmpl.id', 'id',
            'tmpl.ordering', 'ordering',
            'tmpl.checked_out_time', 'checked_out_time',
            'tmpl.checked_out', 'checked_out',
            'tmpl.modified', 'modified',
            'tmpl.modified_by', 'modified_by',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'tmpl.template', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid') ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->setState('filter.pid', $projectId);

        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
        }
    }

    public function getProjectId(): int
    {
        return (int) $this->getState('filter.pid', 0);
    }

    public function getProject(): ?object
    {
        $projectId = $this->getProjectId();

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

    public function getMasterTemplates(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('master_template') . ' = 0')
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Return master-project templates not already present in the current project. */
    public function getMasterTemplatesList($getAll = 0): array
    {
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $currentQuery = $db->getQuery(true)
            ->select($db->quoteName('template'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($currentQuery);
        $current = array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));

        $query = $db->getQuery(true);

        if ((int) $getAll === 1) {
            $query->select([
                $db->quoteName('t') . '.*',
                '1 AS ' . $db->quoteName('isMaster'),
            ]);
        } else {
            $query->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.title', 'text'),
                $db->quoteName('t.template', 'template'),
            ]);
        }

        $query->select($db->quoteName('u1.username'))
            ->from($db->quoteName('#__sportsmanagement_template_config', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'pm')
                . ' ON ' . $db->quoteName('pm.id') . ' = ' . $db->quoteName('t.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.master_template') . ' = ' . $db->quoteName('pm.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('t.modified_by')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId)
            ->where(
                $db->quoteName('t.template') . ' NOT IN ('
                . implode(',', array_map([$db, 'quote'], self::LEGACY_TEMPLATES)) . ')'
            );

        if ($current) {
            $query->where(
                $db->quoteName('t.template') . ' NOT IN ('
                . implode(',', array_map([$db, 'quote'], $current)) . ')'
            );
        }

        $query->order($db->quoteName('t.title') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getMasterName()
    {
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('master.name'))
            ->from($db->quoteName('#__sportsmanagement_project', 'master'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.master_template') . ' = ' . $db->quoteName('master.id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadResult() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    protected function getListQuery()
    {
        $projectId = $this->getProjectId();
        $this->checklist($projectId);

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tmpl.template'),
                $db->quoteName('tmpl.title'),
                $db->quoteName('tmpl.id'),
                $db->quoteName('tmpl.checked_out'),
                $db->quoteName('u.name', 'editor'),
                '0 AS ' . $db->quoteName('isMaster'),
                $db->quoteName('tmpl.checked_out_time'),
                $db->quoteName('tmpl.modified'),
                $db->quoteName('tmpl.modified_by'),
                $db->quoteName('u1.username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_template_config', 'tmpl'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('tmpl.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u1')
                . ' ON ' . $db->quoteName('u1.id') . ' = ' . $db->quoteName('tmpl.modified_by')
            )
            ->where($db->quoteName('tmpl.project_id') . ' = ' . $projectId)
            ->where(
                $db->quoteName('tmpl.template') . ' NOT IN ('
                . implode(',', array_map([$db, 'quote'], self::LEGACY_TEMPLATES)) . ')'
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $needle = $db->quote('%' . $db->escape(mb_strtolower($search), true) . '%', false);
            $query->where(
                '(LOWER(' . $db->quoteName('tmpl.title') . ') LIKE ' . $needle
                . ' OR LOWER(' . $db->quoteName('tmpl.template') . ') LIKE ' . $needle . ')'
            );
        }

        $orderMap = [
            'tmpl.template' => $db->quoteName('tmpl.template'),
            'template' => $db->quoteName('tmpl.template'),
            'tmpl.title' => $db->quoteName('tmpl.title'),
            'title' => $db->quoteName('tmpl.title'),
            'tmpl.id' => $db->quoteName('tmpl.id'),
            'id' => $db->quoteName('tmpl.id'),
            'tmpl.ordering' => $db->quoteName('tmpl.ordering'),
            'ordering' => $db->quoteName('tmpl.ordering'),
            'tmpl.checked_out_time' => $db->quoteName('tmpl.checked_out_time'),
            'checked_out_time' => $db->quoteName('tmpl.checked_out_time'),
            'tmpl.checked_out' => $db->quoteName('tmpl.checked_out'),
            'checked_out' => $db->quoteName('tmpl.checked_out'),
            'tmpl.modified' => $db->quoteName('tmpl.modified'),
            'modified' => $db->quoteName('tmpl.modified'),
            'tmpl.modified_by' => $db->quoteName('tmpl.modified_by'),
            'modified_by' => $db->quoteName('tmpl.modified_by'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'tmpl.template');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['tmpl.template']) . ' ' . $direction);

        return $query;
    }

    /**
     * Ensure every active non-prediction settings XML has a project template row.
     */
    public function checklist($project_id): bool
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            $projectQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('master_template'),
                    $db->quoteName('extension'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project'))
                ->where($db->quoteName('id') . ' = ' . $projectId);
            $db->setQuery($projectQuery, 0, 1);
            $project = $db->loadObject();

            if (!$project || !empty($project->master_template)) {
                return true;
            }

            $recordQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('template'),
                ])
                ->from($db->quoteName('#__sportsmanagement_template_config'))
                ->where($db->quoteName('project_id') . ' = ' . $projectId);
            $db->setQuery($recordQuery);
            $rows = $db->loadObjectList() ?: [];
            $records = [];

            foreach ($rows as $row) {
                $records[(string) $row->template] = (int) $row->id;
            }

            $directories = [JPATH_COMPONENT_SITE . '/settings/default'];

            foreach ($this->getActiveExtensions($projectId) as $extension) {
                $directory = JPATH_COMPONENT_SITE . '/extensions/' . basename((string) $extension) . '/settings/default';

                if (is_dir($directory)) {
                    $directories[] = $directory;
                }
            }

            $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
            $viewsPath = JPATH_COMPONENT_SITE . '/views';

            foreach (array_values(array_unique($directories)) as $directory) {
                if (!is_dir($directory)) {
                    continue;
                }

                foreach (new DirectoryIterator($directory) as $fileInfo) {
                    if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'xml') {
                        continue;
                    }

                    $template = $fileInfo->getBasename('.xml');

                    if ($template === 'do_tipsl' || str_starts_with(strtolower($template), 'prediction')) {
                        continue;
                    }

                    $title = $this->getTemplateTitle($viewsPath, $template);
                    $recordId = $records[$template] ?? 0;

                    if ($recordId <= 0) {
                        $form = $formFactory->createForm($template, ['control' => '']);

                        if (!$form->loadFile($fileInfo->getPathname())) {
                            continue;
                        }

                        $params = [];

                        foreach ($form->getFieldsets() as $fieldset) {
                            foreach ($form->getFieldset($fieldset->name) as $field) {
                                $params[$field->name] = $field->value;
                            }
                        }

                        $record = (object) [
                            'template' => $template,
                            'title' => $title,
                            'params' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                            'project_id' => $projectId,
                        ];
                        $db->insertObject('#__sportsmanagement_template_config', $record, 'id');
                        $records[$template] = (int) ($record->id ?? 0);
                        continue;
                    }

                    $record = (object) [
                        'id' => $recordId,
                        'title' => $title,
                    ];
                    $db->updateObject('#__sportsmanagement_template_config', $record, 'id', true);
                }
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    private function getActiveExtensions(int $projectId): array
    {
        if (!class_exists('sportsmanagementHelper')) {
            if (is_file(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php')) {
                require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
            }
        }

        try {
            return array_values(array_filter(array_map(
                'strval',
                (array) \sportsmanagementHelper::getExtensions($projectId)
            )));
        } catch (\Throwable) {
            return [];
        }
    }

    private function getTemplateTitle(string $viewsPath, string $template): string
    {
        $metaFile = $viewsPath . '/' . $template . '/tmpl/default.xml';

        if (!is_file($metaFile)) {
            return '';
        }

        $xml = @simplexml_load_file($metaFile);

        if (!$xml || !isset($xml->layout)) {
            return '';
        }

        return (string) ($xml->layout->attributes()->title ?? '');
    }
}
