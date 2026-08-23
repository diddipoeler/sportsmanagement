<?php
/** SportsManagement administrator XML export view. */
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewJLXMLExports extends sportsmanagementView
{
    public function init(): void
    {
        $this->exportSystem = (string) $this->app->get('sitename', '');
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_TITLE'), 'generic.png');
        ToolbarHelper::custom('jlxmlexports.export', 'upload', 'upload', Text::_('JTOOLBAR_EXPORT'), false);
        ToolbarHelper::divider();
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        parent::addToolbar();
    }
}
