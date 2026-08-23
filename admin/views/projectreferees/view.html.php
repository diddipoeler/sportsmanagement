<?php
/**
 * SportsManagement administrator project referees view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewprojectreferees extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->_persontype = $input->getInt('persontype');
        $this->project = $this->model->getProject($this->project_id);

        if (!$this->_persontype) {
            $this->_persontype = (int) $this->app->getUserState($this->option . '.persontype', 0);
        }

        $positionOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REFEREE_FUNCTION')),
        ];
        $projectRefPositions = $this->model->getProjectPositions(
            $this->project_id,
            $this->_persontype
        );

        if ($projectRefPositions) {
            $positionOptions = array_merge($positionOptions, $projectRefPositions);
            $this->project_position_id = $projectRefPositions;
        }

        $this->lists['project_position_id'] = $positionOptions;

        if (!$this->items) {
            $countReferees = $this->model->getProjectRefereesCount($this->project_id);

            if ($countReferees) {
                Log::add(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE2', '<i>' . $countReferees . '</i>'),
                    Log::NOTICE,
                    'jsmerror'
                );
                $seasonId = (int) $this->app->getUserState($this->option . '.season_id', 0);
                $this->app->setUserState($this->option . '.season_id', 0);
                $this->model->season_id = 0;
                $this->items = $this->model->getItems2();
                $this->app->setUserState($this->option . '.season_id', $seasonId);
                $this->model->season_id = $seasonId;
            }
        }

        if (!array_key_exists('search_mode', $this->lists)) {
            $this->lists['search_mode'] = '';
        }
    }

    protected function addToolbar()
    {
        $this->app->setUserState($this->option . '.persontype', $this->_persontype);
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id
        );
        ToolbarHelper::apply(
            'projectreferees.saveshort',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_APPLY')
        );
        sportsmanagementHelper::ToolbarButton(
            'assignpersons',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'),
            'players',
            3
        );
        parent::addToolbar();
    }
}
