<?php
/** SportsManagement SIS import administrator view. */
\defined('_JEXEC') or die('Restricted access');

class sportsmanagementViewjlextsisimport extends sportsmanagementView
{
    public function init(): void
    {
        if (in_array($this->getLayout(), ['default', 'default_3', 'default_4'], true)) {
            $this->_displayDefault();
            return;
        }

        $this->revisionDate = '2011-04-28 - 12:00';
    }

    public function _displayDefault(): void
    {
        $input = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');

        $this->project = $this->app->getUserState($option . 'project');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
    }

    public function _displayDefaultUpdate(): void
    {
        $input = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        $this->project = $this->app->getUserState($option . 'project');
        $this->uploadArray = $this->app->getUserState($option . 'uploadArray', []);
        $this->importData = $model->getUpdateData();
    }

    protected function addToolbar(): void
    {
        parent::addToolbar();
    }
}
