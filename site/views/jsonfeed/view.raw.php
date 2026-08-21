<?php
/**
 * Joomla 5/6 raw JSON view for SportsManagement Google Calendar feeds.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

class sportsmanagementViewJSONFeed extends HtmlView
{
    /** @var array<int, array<string, mixed>> */
    public array $events = [];

    public int $compactMode = 0;

    public function display($tpl = null)
    {
        $events = $this->get('GoogleCalendarFeeds');
        $this->events = is_array($events) ? $events : [];
        $this->compactMode = Factory::getApplication()->getInput()->getInt('compact', 0);

        if ($this->compactMode === 1) {
            $this->setLayout('module');
        }

        parent::display($tpl);
    }
}
