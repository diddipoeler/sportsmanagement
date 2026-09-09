<?php
/**
 * SportsManagement DBB import administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextdbbimport extends sportsmanagementView
{
    public function init(): void
    {
        if ($this->getLayout() === 'default') {
            $this->_displayDefault();
            return;
        }

        $this->request_url = $this->uri->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
    }

    public function _displayDefault(): void
    {
        $input = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');

        $this->project = $this->app->getUserState($option . 'project');
        $this->request_url = $this->uri->toString();
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
        $this->document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.jlextdbbimport',
            'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
        parent::addToolbar();
    }
}
