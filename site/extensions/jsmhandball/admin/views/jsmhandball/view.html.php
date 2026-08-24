<?php
/**
 * SportsManagement Handball administrator placeholder view.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjsmhandball extends sportsmanagementView
{
    public function init(): void
    {
        $this->projectid = $this->jinput->getInt('pid', 0);

        if (!$this->projectid) {
            $this->projectid = (int) $this->app->getUserState($this->option . '.pid', 0);
        }
    }

    protected function addToolbar(): void
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_JSMHANDBALL_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        parent::addToolbar();
    }
}
