<?php
/**
 * SportsManagement administrator extra-fields list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewextrafields extends sportsmanagementView
{
    public function init()
    {
        // Preserve the historic view contract: templates expect a club table here.
        $this->table = new ClubTable($this->model->getDatabase());
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXTRAFIELDS_TITLE');
        ToolbarHelper::addNew('extrafield.add');
        ToolbarHelper::editList('extrafield.edit');
        ToolbarHelper::custom('extrafield.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('extrafield.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
