<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextassociations;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for associations. */
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_TITLE'), 'share-alt');
        ToolbarHelper::addNew('jlextassociation.add');
        ToolbarHelper::editList('jlextassociation.edit');
        ToolbarHelper::custom('jlextassociations.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom('jlextassociation.export', 'download', 'download', Text::_('JTOOLBAR_EXPORT'), false);
        ToolbarHelper::publish('jlextassociations.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('jlextassociations.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('jlextassociations.checkin');
        ToolbarHelper::trash('jlextassociations.trash');

        parent::display($tpl);
    }
}
