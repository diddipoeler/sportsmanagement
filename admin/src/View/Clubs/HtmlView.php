<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubs;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Native Joomla 5/6 clubs list view.
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_TITLE'), 'users');
        ToolbarHelper::addNew('club.add');
        ToolbarHelper::editList('club.edit');

        parent::display($tpl);
    }
}
