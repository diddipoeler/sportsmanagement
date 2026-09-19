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

    public function display($tpl = null)
    {
        /** @var EventModel $model */
        $model = $this->getModel();
        $this->event = $model->getGCalendar();

        if ($this->event && trim((string) ($this->event['title'] ?? '')) !== '') {
            $this->getDocument()->setTitle((string) $this->event['title']);
        }

        parent::display($tpl);
    }
}
