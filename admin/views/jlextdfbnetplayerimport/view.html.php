<?php
/** SportsManagement DFBnet player import administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjlextdfbnetplayerimport extends sportsmanagementView
{
    public function init(): void
    {
        if ($this->getLayout() === 'default') {
            $this->_displayDefault();
            return;
        }

        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';

        $seasons = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name'),
        ];

        $seasonsModel = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Seasons', 'Administrator', ['ignore_request' => true]);

        if (!is_object($seasonsModel) || !method_exists($seasonsModel, 'getSeasons')) {
            throw new \RuntimeException('SportsManagement Seasons model is unavailable.', 500);
        }

        $nation = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];

        if ($result = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $result);
            $this->search_nation = $result;
        }

        $allSeasons = $seasonsModel->getSeasons();
        $seasons = array_merge($seasons, is_array($allSeasons) ? $allSeasons : []);

        $this->lists = [
            'nation' => $nation,
            'nation2' => HTMLHelper::_(
                'select.genericList',
                $nation,
                'filter_nation',
                'class="inputbox" style="width:220px"',
                'value',
                'text',
                'DEU'
            ),
            'seasons' => HTMLHelper::_(
                'select.genericList',
                $seasons,
                'filter_season',
                'class="inputbox" style="width:220px"',
                'id',
                'name',
                0
            ),
        ];
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBNET_IMPORT'), 'dfbnet');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
        ToolbarHelper::preferences($this->option);
    }
}
