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
        $this->setState(
            'filter.prediction_id',
            $app->getUserStateFromRequest(
                $this->context . '.filter.prediction_id',
                'filter_prediction_id',
                0,
                'int'
            )
        );
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

        $orderMap = [
            'tmpl.title' => $db->quoteName('tmpl.title'),
            'title' => $db->quoteName('tmpl.title'),
            'tmpl.template' => $db->quoteName('tmpl.template'),
            'template' => $db->quoteName('tmpl.template'),
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
