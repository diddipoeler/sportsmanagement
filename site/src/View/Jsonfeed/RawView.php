<?php
/**
 * Native Joomla 5/6 JSON calendar-feed view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\View\Jsonfeed;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Prepare calendar events for the native default and compact JSON layouts.
 *
 * Requires the native MVC model and reads the current site request through
 * the Joomla 5/6 application resolver instead of static Joomla factories.
 */
final class RawView extends BaseHtmlView
{
    /** @var array<int, array<string, mixed>> */
    public array $events = [];

    public int $compactMode = 0;

    /**
     * Load selected calendar events and dispatch the native JSON layout.
     *
     * A missing MVC model is a server configuration error rather than an
     * empty feed. Only the explicit compact=1 request selects the smaller
     * module layout; all other values render the default calendar feed.
     *
     * @param string|null $tpl Optional Joomla layout template suffix.
     * @return void
     * @throws \RuntimeException When the JsonfeedModel cannot be resolved.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof JsonfeedModel) {
            throw new \RuntimeException('JSON feed view requires JsonfeedModel.', 500);
        }

        $this->events = $model->getGoogleCalendarFeeds();
        $this->compactMode = SportsManagementSiteApplicationResolver::resolve()
            ->getInput()->getInt('compact', 0) === 1 ? 1 : 0;

        if ($this->compactMode === 1) {
            $this->setLayout('module');
        }

        parent::display($tpl);
    }
}
