<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Event view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Event;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

final class HtmlView extends SportsManagementHtmlView
{
    /** @var array<string, mixed>|null */
    public ?array $event = null;

    /**
     * Render one accessible Google Calendar event with Joomla's native view.
     *
     * Requires EventModel from the site MVC factory instead of assuming
     * that any legacy model exposes getGCalendar(). A nonempty event title
     * becomes the browser document title; absent events remain renderable
     * so Joomla can show the configured empty-state layout.
     *
     * @param string|null $tpl Optional view template suffix.
     * @return void
     * @throws \RuntimeException When Joomla cannot resolve the event model.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EventModel) {
            throw new \RuntimeException('Event view requires EventModel.', 500);
        }

        $this->event = $model->getGCalendar();

        if ($this->event && trim((string) ($this->event['title'] ?? '')) !== '') {
            $this->getDocument()->setTitle((string) $this->event['title']);
        }

        parent::display($tpl);
    }
}
