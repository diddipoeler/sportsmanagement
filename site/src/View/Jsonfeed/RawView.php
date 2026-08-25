<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Jsonfeed;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 raw JSON feed view. */
final class RawView extends BaseHtmlView
{
    /** @var array<int, array<string, mixed>> */
    public array $events = [];

    public int $compactMode = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();
        $events = $model && method_exists($model, 'getGoogleCalendarFeeds')
            ? $model->getGoogleCalendarFeeds()
            : [];

        $this->events = is_array($events) ? $events : [];
        $this->compactMode = Factory::getApplication()->getInput()->getInt('compact', 0);

        if ($this->compactMode === 1) {
            $this->setLayout('module');
        }

        parent::display($tpl);
    }
}
