<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendars;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for Google calendars. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof JsmgcalendarsModel) {
            throw new \RuntimeException('JsmgcalendarsModel could not be loaded.', 500);
        }

        // Preserve the historical bootstrap behaviour until the Google API
        // package provisioning is moved to an explicit maintenance action.
        $model->check_google_api();

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_D_GOOGLE_CALENDAR'), 'calendar');
        ToolbarHelper::addNew('jsmgcalendar.add', 'JTOOLBAR_NEW');
        ToolbarHelper::custom(
            'jsmgcalendarimport.import',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_GCALENDARS_BUTTON_IMPORT'),
            false
        );

        parent::display($tpl);
    }
}
