<?php
/**
 * SportsManagement administrator prediction game view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongameModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongamesModel;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewPredictionGame
 */
class sportsmanagementViewPredictionGame extends sportsmanagementView
{
    public function init()
    {
        $this->pred_admins = PredictiongamesModel::getAdmins($this->item->id);
        $this->pred_projects = $this->model->getPredictionProjectIDs($this->item->id);

        $this->form->setValue('user_ids', null, $this->pred_admins);
        $this->form->setValue('project_ids', null, $this->pred_projects);
        $this->form->setValue('s', null, PredictiongameModel::$seasonid);
    }

    protected function addToolBar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        $this->title = $this->item->id
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_EDIT')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PGAME_NEW');
        $this->icon = 'pgame';

        parent::addToolbar();
    }
}
