<?php
/**
 * Legacy Joomla 5/6 administrator compatibility view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * SportsManagement administrator project referees view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectrefereesModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewprojectreferees extends sportsmanagementView
{
    /** Escaped country names and flags indexed by ISO alpha-3 code. */
    public array $countryPresentation = [];

    /**
     * Initialise project referee positions and country presentation.
     * Retains the historical season fallback and restores its state even
     * if the additional referee query fails.
     * @return void
     */
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
                try {
                    $this->items = $this->model->getItems2();
                } finally {
                    $this->app->setUserState($this->option . '.season_id', $seasonId);
                    $this->model->season_id = $seasonId;
                }
            }
        }

        if ($this->model instanceof ProjectrefereesModel) {
            $this->countryPresentation = CountryOptionsHelper::getListPresentation($this->model->getSportsManagementDatabase());
        }

        if (!array_key_exists('search_mode', $this->lists)) {
            $this->lists['search_mode'] = '';
        }
    }

    /**
     * Register project navigation, inline save and referee assignment actions.
     * Retains the legacy parent toolbar while the assignment workflow is migrated.
     * @return void
     */
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
