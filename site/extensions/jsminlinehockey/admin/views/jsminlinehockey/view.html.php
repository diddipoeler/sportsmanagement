<?php
/**
 * SportsManagement Inline Hockey administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

class sportsmanagementViewjsminlinehockey extends sportsmanagementView
{
    /**
     * Initialise a selected project and its Inline Hockey match provider URL.
     *
     * Finds a positive project ID in Joomla input or persisted component
     * state; avoids querying the provider for invalid or missing projects.
     * Sets the translated toolbar title and normalises legacy layout aliases.
     *
     * @return void The view's project ID, match link and layout are updated.
     */
    public function init(): void
    {
        $this->projectid = max(0, $this->jinput->getInt('pid', 0));

        if ($this->projectid === 0) {
            $this->projectid = max(0, (int) $this->app->getUserState($this->option . '.pid', 0));
        }

        $this->matchlink = '';

        if ($this->projectid > 0) {
            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $this->matchlink = (new InlineHockeyProjectService($db))->getMatchLink($this->projectid);
        } else {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_JSMINLINEHOCKEY_PROJECT_SELECT'), 'notice');
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_JSMINLINEHOCKEY_TITLE'), 'install');

        if (in_array($this->getLayout(), ['default', 'default_3', 'default_4'], true)) {
            $this->setLayout('default');
        }
    }

    /**
     * Add the match-import action only when a project is selected.
     *
     * The save toolbar button triggers the token-protected Inline Hockey
     * import task and must not appear for an unset project identifier.
     *
     * @return void
     */
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
