<?php
/** Joomla 5/6 administrator updates view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewUpdates extends sportsmanagementView
{
    public function init()
    {
        $this->app->setUserState($this->option . 'update_part', 0);
        $filterOrder = $this->app->getUserStateFromRequest(
            $this->option . 'updates_filter_order',
            'filter_order',
            'dates',
            'cmd'
        );
        $filterOrderDirection = $this->app->getUserStateFromRequest(
            $this->option . 'updates_filter_order_Dir',
            'filter_order_Dir',
            '',
            'word'
        );

        $this->versions = $this->model->getVersions();
        $this->versionhistory = $this->model->getVersionHistory();
        $this->updateFiles = $this->model->loadUpdateFiles();
        $this->request_url = Uri::getInstance()->toString();
        $this->lists = [
            'order_Dir' => $filterOrderDirection,
            'order' => $filterOrder,
        ];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE');
        $this->icon = 'updates';
        parent::addToolbar();
    }
}
