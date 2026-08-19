<?php
/** SportsManagement handball.net administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjlexthandballnet extends sportsmanagementView
{
    public function init()
    {
    }

    protected function addToolbar()
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
