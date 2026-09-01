<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotes;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for SportsManagement quotes. */
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE'), 'quote');
        ToolbarHelper::addNew('smquote.add');
        ToolbarHelper::editList('smquote.edit');
        ToolbarHelper::custom('smquote.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom('smquotes.edittxt', 'edit', 'edit', Text::_('JTOOLBAR_EDIT'), false);
        $this->getDocument()->getToolbar('toolbar')->appendButton(
            'Link',
            'info',
            Text::_('JCATEGORY'),
            'index.php?option=com_categories&extension=com_sportsmanagement'
        );
        ToolbarHelper::custom('smquote.export', 'download', 'download', Text::_('JTOOLBAR_EXPORT'), false);
        ToolbarHelper::publish('smquotes.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('smquotes.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('smquotes.checkin');
        ToolbarHelper::trash('smquotes.trash');

        parent::display($tpl);
    }
}
