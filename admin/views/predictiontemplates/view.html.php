<?php
/**
 * SportsManagement administrator prediction templates view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * sportsmanagementViewPredictionTemplates
 */
class sportsmanagementViewPredictionTemplates extends sportsmanagementView
{
    public function init()
    {
        $this->prediction_id = (int) $this->state->get('filter.prediction_id', 0);

        if ($this->prediction_id <= 0) {
            $this->prediction_id = $this->app->getInput()->post->getInt('filter_prediction_id', 0);
        }

        $predictiongame = false;

        if ($this->prediction_id > 0) {
            $this->model->checklist($this->prediction_id);
            $predictiongame = $this->model->getPredictionGame($this->prediction_id);
        }

        $this->table = Table::getInstance('predictiontemplate', 'sportsmanagementTable');
        $predictions = [
            HTMLHelper::_(
                'select.option',
                '0',
                '- ' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME') . ' -',
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
        $this->pred_id = $this->prediction_id;
        $this->predictiongame = $predictiongame;
    }

    protected function addToolBar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS');
        $this->icon = 'templates';

        parent::addToolbar();
    }
}
