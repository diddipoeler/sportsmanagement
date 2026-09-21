<?php
/**
 * Joomla 5/6 Google Calendar administration landing page.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgooglecalendar;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Joomla 5/6 Google Calendar administration landing page. */
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title(
            Text::_('COM_SPORTSMANAGEMENT_D_GOOGLE_CALENDAR'),
            'calendar'
        );

        parent::display($tpl);
    }
}
