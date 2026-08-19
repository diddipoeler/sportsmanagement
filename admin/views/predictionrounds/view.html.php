<?php
/**
 * SportsManagement administrator prediction rounds view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewPredictionRounds
 */
class sportsmanagementViewPredictionRounds extends sportsmanagementView
{
    public function init()
    {
        $this->prediction_id = (int) $this->state->get('filter.prediction_id');
        $this->table = Table::getInstance('predictionround', 'sportsmanagementTable');

        if (!$this->items) {
            if ($this->prediction_id === 0) {
                $this->app->enqueueMessage(
                    Text::sprintf(
                        'COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_ID',
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME')
                    ),
                    'error'
                );
            } else {
                $this->app->enqueueMessage(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAMES_NO_PREDICTION_TIPPROUNDS'),
                    'error'
                );
            }
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
            'rien_ne_va_plus' => [
                HTMLHelper::_('select.option', 'FIRSTMATCH_OF_TIPPGAME', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPGAME')),
                HTMLHelper::_('select.option', 'FIRSTMATCH_OF_TIPPROUND', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_FIRSTMATCH_OF_TIPPROUND')),
                HTMLHelper::_('select.option', 'BEGIN_OF_MATCH', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_RIEN_NE_VA_PLUS_BEGIN_OF_MATCH')),
            ],
        ];
        $this->pred_project = $this->model->getPredictionGame($this->prediction_id);
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_TITLE');

        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=predictiongames');
        ToolbarHelper::publishList('predictionrounds.publish');
        ToolbarHelper::unpublishList('predictionrounds.unpublish');
        ToolbarHelper::divider();
        ToolbarHelper::apply('predictionrounds.saveshort');
        ToolbarHelper::divider();
        ToolbarHelper::addNew(
            'predictionrounds.populateFromProjectRounds',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_IMPORT_BUTTON')
        );
        ToolbarHelper::divider();
        ToolbarHelper::deleteList('', 'predictionrounds.delete', 'JTOOLBAR_DELETE');

        parent::addToolbar();
    }
}
