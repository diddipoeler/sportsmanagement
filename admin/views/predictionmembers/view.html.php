<?php
/** SportsManagement administrator prediction members view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewPredictionMembers extends sportsmanagementView
{
    public function init()
    {
        $this->prediction_id = (int) $this->state->get('filter.prediction_id');
        $layout = $this->getLayout();

        if (in_array($layout, ['default', 'default_3', 'default_4'], true)) {
            $this->app->setUserState($this->option . '.prediction_id', $this->prediction_id);
            $this->displayList();

            return;
        }

        if (in_array($layout, ['editlist', 'editlist_3', 'editlist_4'], true)) {
            $this->displayEditList();
        }
    }

    private function displayList(): void
    {
        $predictions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PRED_GAME'), 'value', 'text'),
        ];
        $games = $this->model->getPredictionGames();

        if ($games) {
            $predictions = array_merge($predictions, $games);
            $this->prediction_ids = $games;
        } else {
            $this->prediction_ids = [];
        }

        $this->lists = [
            'predictions' => HTMLHelper::_(
                'select.genericlist',
                $predictions,
                'filter_prediction_id',
                'class="form-select" onchange="this.form.submit();"',
                'value',
                'text',
                $this->prediction_id
            ),
        ];
    }

    private function displayEditList(): void
    {
        $this->prediction_id = (int) $this->app->getUserState($this->option . '.prediction_id', 0);
        $this->prediction_name = $this->model->getPredictionProjectName($this->prediction_id);
        $predictionMembers = $this->model->getPredictionMembers($this->prediction_id);
        $joomlaMembers = $this->model->getJLUsers($this->prediction_id);

        $this->lists = [
            'prediction_members' => $predictionMembers
                ? HTMLHelper::_(
                    'select.genericlist',
                    $predictionMembers,
                    'prediction_members[]',
                    'class="form-select" multiple size="15"',
                    'value',
                    'text'
                )
                : '<select name="prediction_members[]" id="prediction_members" class="form-select" multiple size="15"></select>',
            'members' => $joomlaMembers
                ? HTMLHelper::_(
                    'select.genericlist',
                    $joomlaMembers,
                    'members[]',
                    'class="form-select" multiple size="15"',
                    'value',
                    'text'
                )
                : '<select name="members[]" id="members" class="form-select" multiple size="15"></select>',
        ];

        $this->setLayout('editlist');
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_TITLE');
        ToolbarHelper::custom(
            'predictionmembers.reminder',
            'mail',
            'mail-2',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'),
            true
        );
        ToolbarHelper::divider();

        if ($this->prediction_id) {
            sportsmanagementHelper::ToolbarButton(
                'editlist',
                'new',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_BUTTON_ASSIGN')
            );
            ToolbarHelper::publishList(
                'predictionmembers.publish',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVE')
            );
            ToolbarHelper::unpublishList(
                'predictionmembers.unpublish',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REJECT')
            );
            ToolbarHelper::deleteList('', 'predictionmembers.remove');
        }

        ToolbarHelper::checkin('predictionmembers.checkin');
        parent::addToolbar();
    }
}
