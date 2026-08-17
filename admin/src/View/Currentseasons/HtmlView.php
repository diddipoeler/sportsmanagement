<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Currentseasons;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Read-only current-seasons view for the Joomla 5/6 migration smoke path.
 */
class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $pagination;
    public $state;

    public function display($tpl = null)
    {
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'), 'calendar');

        parent::display($tpl);
    }
}
