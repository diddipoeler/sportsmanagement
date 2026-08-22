<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jsmgcalendar;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 Google Calendar edit view. */
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

        if (empty($this->item->id)) {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $this->form->setValue('username', null, $params->get('google_mail_account', ''));
            $this->form->setValue('password', null, $params->get('google_mail_password', ''));
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_D_GOOGLE_CALENDAR'), 'calendar');
        ToolbarHelper::apply('jsmgcalendar.apply');
        ToolbarHelper::save('jsmgcalendar.save');
        ToolbarHelper::save2new('jsmgcalendar.save2new');
        ToolbarHelper::cancel('jsmgcalendar.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
