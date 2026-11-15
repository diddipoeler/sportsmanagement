<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Site\View\Ical;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 raw view for one authorised calendar event.
 *
 * Keeps the historical EventModel data contract while using Joomla's
 * container-resolved database connection rather than legacy global services.
 */
final class RawView extends BaseHtmlView
{
    public ?array $event = null;

    /**
     * Render an individual Google Calendar event via the Joomla 5/6 raw view.
     *
     * The iCal event endpoint historically uses EventModel rather than the
     * fixture-oriented IcalModel. It requires a typed database service from
     * Joomla's public DI container, attaches the event model to this view
     * and exposes its authorised event data to the selected layout.
     * A missing service fails with a stable server error instead of a PHP
     * type error or a partially initialised view.
     *
     * @param string|null $tpl Optional raw layout template suffix.
     * @return void
     * @throws \RuntimeException When Joomla's database service is invalid.
     */
    public function display($tpl = null)
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement iCal event database service is unavailable.', 500);
        }

        $model = new EventModel();
        $model->setDatabase($database);
        $this->setModel($model, true);
        $this->event = $model->getGCalendar();

        parent::display($tpl);
    }
}
