<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Databasetools;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Native Joomla 5/6 database-tools dashboard.
 */
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE'), 'databases');
    }
}
