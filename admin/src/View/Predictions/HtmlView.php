<?php
/**
 * Native Joomla 5/6 administrator prediction management dashboard.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictions;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator dashboard for prediction management. */
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION_GAMES'), 'dashboard');
        parent::display($tpl);
    }
}
