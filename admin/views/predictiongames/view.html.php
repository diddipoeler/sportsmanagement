<?php
/**
 * SportsManagement administrator prediction games view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewPredictionGames
 */
class sportsmanagementViewPredictionGames extends sportsmanagementView
{
    public function init()
    {
        $this->predictionProjects = [];
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);

        if ($this->prediction_id <= 0) {
            $this->prediction_id = $this->app->getInput()->getInt('prediction_id', 0);
        }

        $this->table = Table::getInstance('predictiongame', 'sportsmanagementTable');

        if (!$this->items) {
            $this->app->enqueueMessage(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_GAMES'),
                'error'
            );
        }

        $predictions = [
            HTMLHelper::_(
                'select.option',
                '0',
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'),
                'value',
                'text'
            ),
        ];

        $predictionGames = $this->model->getPredictionGames();

        if ($predictionGames) {
            $predictions = array_merge($predictions, $predictionGames);
            $this->prediction_ids = $predictionGames;
        }

        $this->lists = [
            'predictions' => HTMLHelper::_(
                'select.genericlist',
                $predictions,
                'filter_prediction_id',
                'class="inputbox" onChange="this.form.submit();" ',
                'value',
                'text',
                $this->prediction_id
            ),
        ];
        $this->dPredictionID = $this->prediction_id;

        if ($this->prediction_id > 0) {
            $this->predictionProjects = $this->model->getChilds($this->prediction_id);
        }

        $this->pred_project = $this->model->getPredictionGame($this->prediction_id);

        // The historical template expects two helper-model properties. Keep the
        // template contract while routing both counters through this native model.
        $relations = $this->predictionProjects;
        $nativeModel = $this->model;
        $this->modelpredround = new class($nativeModel, $relations) {
            private $model;
            private array $relations;

            public function __construct($model, array $relations)
            {
                $this->model = $model;
                $this->relations = $relations;
            }

            public function getActivePredictionRoundsCount($relationId): int
            {
                $relation = $this->relations[(int) $relationId] ?? null;
                $predictionId = (int) ($relation['prediction_id'] ?? 0);

                return $this->model->getActivePredictionRoundsCount($predictionId);
            }
        };
        $this->modelround = new class($nativeModel, $relations) {
            private $model;
            private array $relations;

            public function __construct($model, array $relations)
            {
                $this->model = $model;
                $this->relations = $relations;
            }

            public function getRoundsCount($relationId): int
            {
                $relation = $this->relations[(int) $relationId] ?? null;
                $projectId = (int) ($relation['project_id'] ?? 0);

                return $this->model->getProjectRoundsCount($projectId);
            }
        };
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_TITLE');
        $this->icon = 'pred-cpanel';

        ToolbarHelper::publish('predictiongames.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('predictiongames.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::divider();
        ToolbarHelper::editList('predictiongame.edit');
        ToolbarHelper::addNew('predictiongame.add');
        ToolbarHelper::custom('predictiongame.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('predictiongame.export', Text::_('JTOOLBAR_EXPORT'));

        parent::addToolbar();
    }
}
