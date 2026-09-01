<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;

/**
 * Native Joomla 5/6 administrator list model for prediction templates.
 */
final class PredictiontemplatesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'tmpl.title', 'title',
            'tmpl.template', 'template',
            'tmpl.published', 'published', 'state',
            'tmpl.id', 'id',
            'tmpl.ordering', 'ordering',
            'tmpl.modified', 'modified',
            'tmpl.modified_by', 'modified_by',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'tmpl.title', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $filters = $input->get('filter', [], 'array');
        $predictionId = max(0, (int) $this->state->get('filter.prediction_id', 0));
        $legacyPredictionId = $input->getInt('filter_prediction_id', -1);

        if (array_key_exists('prediction_id', $filters)) {
            $predictionId = max(0, (int) $filters['prediction_id']);
        } elseif ($legacyPredictionId >= 0) {
            $predictionId = $legacyPredictionId;
        }

        if (array_key_exists('search', $filters)) {
            $this->setState('filter.search', trim((string) $filters['search']));
        }

        if (array_key_exists('state', $filters)) {
            $this->setState('filter.state', (string) $filters['state']);
        }

        $this->setState('filter.prediction_id', $predictionId);
        $app->setUserState($this->context . '.filter.prediction_id', $predictionId);
        $app->setUserState('com_sportsmanagement.filter.prediction_id', $predictionId);
        $app->setUserState('com_sportsmanagement.prediction_id', $predictionId);
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tmpl') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('u1.username'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_template', 'tmpl'))
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
            ->where($db->quoteName('tmpl.prediction_id') . ' = ' . (int) $this->getState('filter.prediction_id', 0));

        $search = trim((string) $this->getState('filter.search', ''));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape(mb_strtolower($search), true) . '%', false);
            $query->where(
                '(LOWER(' . $db->quoteName('tmpl.template') . ') LIKE ' . $token
                . ' OR LOWER(' . $db->quoteName('tmpl.title') . ') LIKE ' . $token . ')'
            );
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('tmpl.published') . ' = ' . (int) $state);
        }

        $orderMap = [
            'tmpl.title' => $db->quoteName('tmpl.title'),
            'title' => $db->quoteName('tmpl.title'),
            'tmpl.template' => $db->quoteName('tmpl.template'),
            'template' => $db->quoteName('tmpl.template'),
            'tmpl.published' => $db->quoteName('tmpl.published'),
            'published' => $db->quoteName('tmpl.published'),
            'state' => $db->quoteName('tmpl.published'),
            'tmpl.id' => $db->quoteName('tmpl.id'),
            'id' => $db->quoteName('tmpl.id'),
            'tmpl.ordering' => $db->quoteName('tmpl.ordering'),
            'ordering' => $db->quoteName('tmpl.ordering'),
            'tmpl.modified' => $db->quoteName('tmpl.modified'),
            'modified' => $db->quoteName('tmpl.modified'),
            'tmpl.modified_by' => $db->quoteName('tmpl.modified_by'),
            'modified_by' => $db->quoteName('tmpl.modified_by'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'tmpl.title');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['tmpl.title']) . ' ' . $direction);

        return $query;
    }

    /**
     * Return prediction games for the administrator selector.
     */
    public function getPredictionGames(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    /**
     * Return one prediction game for the template header.
     */
    public function getPredictionGame($prediction_id)
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $predictionId);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: false;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Return inherited master templates that do not yet have a local override.
     */
    public function getAvailableMasterTemplates(int $predictionId): array
    {
        $game = $this->getPredictionGame($predictionId);
        $masterId = (int) ($game->master_template ?? 0);

        if ($masterId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('master.id', 'value'),
                $db->quoteName('master.title', 'text'),
                $db->quoteName('master.template'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_template', 'master'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_template', 'local')
                . ' ON ' . $db->quoteName('local.prediction_id') . ' = ' . (int) $predictionId
                . ' AND ' . $db->quoteName('local.template') . ' = ' . $db->quoteName('master.template')
            )
            ->where($db->quoteName('master.prediction_id') . ' = ' . $masterId)
            ->where($db->quoteName('local.id') . ' IS NULL')
            ->order($db->quoteName('master.title') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());

            return [];
        }
    }

    /**
     * Copy one inherited master row into the selected prediction game as an override.
     */
    public function createMasterOverride(int $sourceTemplateId, int $predictionId)
    {
        if ($sourceTemplateId <= 0 || $predictionId <= 0) {
            $this->setError('A prediction game and master template must be selected.');

            return false;
        }

        $game = $this->getPredictionGame($predictionId);
        $masterId = (int) ($game->master_template ?? 0);

        if ($masterId <= 0 || $masterId === $predictionId) {
            $this->setError('The selected prediction game has no valid master template.');

            return false;
        }

        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__sportsmanagement_prediction_template'))
                ->where($db->quoteName('id') . ' = ' . $sourceTemplateId)
                ->where($db->quoteName('prediction_id') . ' = ' . $masterId);
            $db->setQuery($query, 0, 1);
            $source = $db->loadObject();

            if (!$source) {
                throw new \RuntimeException('The selected master template is unavailable.');
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_prediction_template'))
                ->where($db->quoteName('prediction_id') . ' = ' . $predictionId)
                ->where($db->quoteName('template') . ' = ' . $db->quote((string) $source->template));
            $db->setQuery($query, 0, 1);
            $existingId = (int) $db->loadResult();

            if ($existingId > 0) {
                return $existingId;
            }

            $db->transactionStart();
            $transactionStarted = true;

            $record = (object) [
                'prediction_id' => $predictionId,
                'template' => (string) $source->template,
                'title' => (string) $source->title,
                'params' => (string) $source->params,
                'published' => (int) ($source->published ?? 1),
                'ordering' => (int) ($source->ordering ?? 0),
                'checked_out' => 0,
                'checked_out_time' => $db->getNullDate(),
                'modified' => Factory::getDate()->toSql(),
                'modified_by' => (int) Factory::getApplication()->getIdentity()->id,
            ];

            $db->insertObject('#__sportsmanagement_prediction_template', $record, 'id');
            $db->transactionCommit();

            return (int) ($record->id ?? 0);
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original import failure.
                }
            }

            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Ensure every default prediction settings XML has a database template row.
     */
    public function checklist($prediction_id)
    {
        $predictionId = (int) $prediction_id;

        if ($predictionId <= 0) {
            return true;
        }

        $db = $this->getDatabase();
        $defaultPath = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'settings' . DIRECTORY_SEPARATOR . 'default';
        $viewsPath = JPATH_SITE . '/components/com_sportsmanagement/views';

        try {
            $query = $db->getQuery(true)
                ->select($db->quoteName('master_template'))
                ->from($db->quoteName('#__sportsmanagement_prediction_game'))
                ->where($db->quoteName('id') . ' = ' . $predictionId);
            $db->setQuery($query);
            $game = $db->loadObject();

            if (!$game || !empty($game->master_template)) {
                return true;
            }

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('template'),
                ])
                ->from($db->quoteName('#__sportsmanagement_prediction_template'))
                ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
            $records = [];

            foreach ($rows as $row) {
                $records[(string) $row->template] = (int) $row->id;
            }

            if (!is_dir($defaultPath)) {
                return true;
            }

            $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);

            foreach (new DirectoryIterator($defaultPath) as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $filename = $fileInfo->getFilename();

                if (strtolower($fileInfo->getExtension()) !== 'xml'
                    || !str_starts_with(strtolower($filename), 'prediction')) {
                    continue;
                }

                $template = $fileInfo->getBasename('.xml');
                $title = $this->getTemplateTitle($viewsPath, $template);
                $recordId = $records[$template] ?? 0;

                if ($recordId <= 0) {
                    $form = $formFactory->createForm($filename);

                    if (!$form->loadFile($fileInfo->getPathname())) {
                        continue;
                    }

                    $params = new Registry();

                    foreach ($form->getFieldsets() as $fieldset) {
                        foreach ($form->getFieldset($fieldset->name) as $field) {
                            $params->set($field->name, $field->value);
                        }
                    }

                    $record = (object) [
                        'template' => $template,
                        'title' => $title !== '' ? $title : $filename,
                        'params' => json_encode($params->toArray()),
                        'prediction_id' => $predictionId,
                    ];
                    $db->insertObject('#__sportsmanagement_prediction_template', $record, 'id');
                    $records[$template] = (int) ($record->id ?? 0);
                    continue;
                }

                $record = (object) [
                    'id' => $recordId,
                    'title' => $title,
                ];
                $db->updateObject('#__sportsmanagement_prediction_template', $record, 'id', true);
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
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
