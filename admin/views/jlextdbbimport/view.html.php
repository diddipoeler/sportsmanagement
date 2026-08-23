<?php
/** SportsManagement DBB import administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjlextdbbimport extends sportsmanagementView
{
    public function init(): void
    {
        if ($this->getLayout() === 'default') {
            $this->_displayDefault();
            return;
        }

        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
    }

    public function _displayDefault(): void
    {
        $input = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');

        $this->project = $this->app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
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
        $stylelink = '<link rel="stylesheet" href="'
            . Uri::root()
            . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css"
            . ' type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
        parent::addToolbar();
    }
}
