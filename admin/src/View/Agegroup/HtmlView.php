<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Agegroup;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for an age group. */
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
            throw new \RuntimeException('Age group form could not be loaded.', 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPE_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPE_EDIT'),
            'agegroup'
        );
        ToolbarHelper::apply('agegroup.apply');
        ToolbarHelper::save('agegroup.save');
        ToolbarHelper::save2new('agegroup.save2new');
        ToolbarHelper::save2copy('agegroup.save2copy');
        ToolbarHelper::cancel('agegroup.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
