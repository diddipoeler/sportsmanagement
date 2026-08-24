<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountry;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a country. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Country form could not be loaded.', 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'JTOOLBAR_NEW' : 'JTOOLBAR_EDIT') . ': ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRIES_TITLE'),
            'flag'
        );
        ToolbarHelper::apply('jlextcountry.apply');
        ToolbarHelper::save('jlextcountry.save');
        ToolbarHelper::save2new('jlextcountry.save2new');
        ToolbarHelper::save2copy('jlextcountry.save2copy');
        ToolbarHelper::cancel('jlextcountry.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
