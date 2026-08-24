<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountries;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for countries. */
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

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRIES_TITLE'), 'flag');
        ToolbarHelper::addNew('jlextcountry.add');
        ToolbarHelper::editList('jlextcountry.edit');
        ToolbarHelper::custom('jlextcountry.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
        ToolbarHelper::custom(
            'jlextcountries.importplz',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_IMPORT_PLZ'),
            true
        );
        ToolbarHelper::custom('jlextcountry.export', 'download', 'download', Text::_('JTOOLBAR_EXPORT'), false);
        ToolbarHelper::publish('jlextcountries.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('jlextcountries.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::checkin('jlextcountries.checkin');
        ToolbarHelper::trash('jlextcountries.trash');

        parent::display($tpl);
    }
}
