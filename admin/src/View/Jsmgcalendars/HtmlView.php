<?php
/**
 * Native Joomla 5/6 administrator list view for Google calendars.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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

        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();
        $this->state = $model->getState();

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_D_GOOGLE_CALENDAR'), 'calendar');

        // Never download and extract third-party PHP libraries during a GET
        // request. Provisioning is an explicit administrator POST task.
        ToolbarHelper::custom(
            'jsmgcalendars.installgoogleapi',
            'download',
            'download',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALL_GOOGLE_API'),
            false
        );
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
