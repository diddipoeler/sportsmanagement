<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubname;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_EDIT'),
            'address'
        );
        ToolbarHelper::apply('clubname.apply');
        ToolbarHelper::save('clubname.save');
        ToolbarHelper::save2new('clubname.save2new');
        ToolbarHelper::save2copy('clubname.save2copy');
        ToolbarHelper::cancel('clubname.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
