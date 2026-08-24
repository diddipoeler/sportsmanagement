<?php
/** SportsManagement Inline Hockey administrator view. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

class sportsmanagementViewjsminlinehockey extends sportsmanagementView
{
    public function init(): void
    {
        $this->projectid = $this->jinput->getInt('pid', 0);

        if (!$this->projectid) {
            $this->projectid = (int) $this->app->getUserState($this->option . '.pid', 0);
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->matchlink = (new InlineHockeyProjectService($db))->getMatchLink($this->projectid);
        $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_JSMINLINEHOCKEY_PROJECT_SELECT'), 'notice');

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMINLINEHOCKEY_TITLE'), 'install');

        if (in_array($this->getLayout(), ['default', 'default_3', 'default_4'], true)) {
            $this->setLayout('default');
        }
    }

    protected function addToolbar(): void
    {
        if ($this->projectid) {
            ToolbarHelper::save(
                'jsminlinehockey.getmatches',
                'COM_SPORTSMANAGEMENT_JSMINLINEHOCKEY_GET_MATCHES'
            );
        }
    }
}
