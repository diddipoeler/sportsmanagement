<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextfederations;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for federations. */
final class HtmlView extends BaseHtmlView
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE'), 'flag');
        ToolbarHelper::addNew('jlextfederation.add');
        ToolbarHelper::editList('jlextfederation.edit');
        ToolbarHelper::custom('jlextfederation.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom('jlextfederation.export', 'download', 'download', Text::_('JTOOLBAR_EXPORT'), false);
        ToolbarHelper::publish('jlextfederations.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('jlextfederations.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('jlextfederations.checkin');
        ToolbarHelper::trash('jlextfederations.trash');

        parent::display($tpl);
    }
}
