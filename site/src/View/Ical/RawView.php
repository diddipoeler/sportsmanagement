<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Ical;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EventModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 raw iCal event view. */
final class RawView extends BaseHtmlView
{
    public ?array $event = null;

    public function display($tpl = null)
    {
        $model = new EventModel();
        $model->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
        $this->setModel($model, true);
        $this->event = $model->getGCalendar();

        parent::display($tpl);
    }
}
