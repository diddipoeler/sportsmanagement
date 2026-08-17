<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Eventtype;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 event-type edit view. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_EVENTTYPE_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_EVENTTYPE_EDIT'),
            'pencil-alt'
        );
        ToolbarHelper::apply('eventtype.apply');
        ToolbarHelper::save('eventtype.save');
        ToolbarHelper::save2new('eventtype.save2new');
        ToolbarHelper::save2copy('eventtype.save2copy');
        ToolbarHelper::cancel('eventtype.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
