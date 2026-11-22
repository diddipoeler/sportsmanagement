<?php
/**
 * Native Joomla 5/6 database-tools dashboard.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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
    /**
     * Render the Joomla administrator database-tools dashboard.
     *
     * Set up the database maintenance toolbar before handing control to
     * Joomla's base HTML view, which selects and renders the active layout.
     *
     * @param string|null $tpl Optional dashboard layout suffix.
     * @return void The base view writes the administrator page.
     */
    public function display($tpl = null)
    {
        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Set the translated database maintenance page title.
     *
     * Register the component's database-tools title and icon with the
     * Joomla administrator toolbar; no database operation is executed.
     *
     * @return void The toolbar is updated.
     */
    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE'), 'databases');
    }
}
