<?php
/**
 * SportsManagement administrator sports-types list view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\SportstypeTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewSportsTypes extends sportsmanagementView
{
    public function init()
    {
        $options = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_TEAM')),
            HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_SINGLE')),
        ];

        $this->table = new SportstypeTable($this->model->getDatabase());
        $this->lists = ['sportart' => $options];
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_TITLE');
        ToolbarHelper::addNew('sportstype.add');
        ToolbarHelper::editList('sportstype.edit');
        ToolbarHelper::custom('sportstype.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::archiveList('sportstype.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
