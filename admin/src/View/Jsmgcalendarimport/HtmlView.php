<?php
/**
 * Native Joomla 5/6 administrator Google Calendar import login view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendarimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 Google Calendar import login view. */
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $this->setLayout('login');

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_D_GOOGLE_CALENDAR'), 'calendar');
        ToolbarHelper::cancel('jsmgcalendar.cancel', 'JTOOLBAR_CANCEL');

        parent::display($tpl);
    }
}
