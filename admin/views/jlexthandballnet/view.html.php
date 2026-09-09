<?php
/** SportsManagement handball.net administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlexthandballnet extends sportsmanagementView
{
    public function init()
    {
    }

    protected function addToolbar()
    {
        $this->document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.jlexthandballnet',
            'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
        parent::addToolbar();
    }
}
