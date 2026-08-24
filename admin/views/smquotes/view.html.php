<?php
/**
 * SportsManagement administrator quotes list view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewsmquotes extends sportsmanagementView
{
    public $filterForm;
    public $activeFilters;

    public function init()
    {
        try {
            $this->filterForm = $this->model->getFilterForm();
            $this->activeFilters = $this->model->getActiveFilters();
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' ' . $e->getCode(), Log::ERROR, 'jsmerror');
            Log::add(__METHOD__ . ' ' . $e->getMessage(), Log::ERROR, 'jsmerror');
        }
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE');
        ToolbarHelper::addNew('smquote.add');
        ToolbarHelper::editList('smquote.edit');
        ToolbarHelper::custom('smquote.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom('smquotes.edittxt', 'featured.png', 'featured_f2.png', Text::_('JTOOLBAR_EDIT'), false);
        $bar = Toolbar::getInstance('toolbar');
        $bar->appendButton('Link', 'info', 'Kategorie', 'index.php?option=com_categories&extension=com_sportsmanagement');
        ToolbarHelper::archiveList('smquote.export', Text::_('JTOOLBAR_EXPORT'));
        parent::addToolbar();
    }
}
