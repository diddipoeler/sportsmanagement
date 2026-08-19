<?php
/** SportsManagement administrator quote text-files view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewsmquotestxt extends sportsmanagementView
{
    public function init()
    {
        $this->files = $this->model->getTXTFiles();
        $this->option = 'com_sportsmanagement';
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS');
        ToolbarHelper::back(
            Text::_('JPREV'),
            Route::_('index.php?option=com_sportsmanagement&view=smquotes')
        );
        parent::addToolbar();
    }
}
