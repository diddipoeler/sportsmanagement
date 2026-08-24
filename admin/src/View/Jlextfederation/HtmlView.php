<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextfederation;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a federation. */
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
            throw new \RuntimeException('Federation form could not be loaded.', 500);
        }

        $this->normaliseDates();

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'JTOOLBAR_NEW' : 'JTOOLBAR_EDIT') . ': ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE'),
            'flag'
        );
        ToolbarHelper::apply('jlextfederation.apply');
        ToolbarHelper::save('jlextfederation.save');
        ToolbarHelper::save2new('jlextfederation.save2new');
        ToolbarHelper::save2copy('jlextfederation.save2copy');
        ToolbarHelper::cancel('jlextfederation.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function normaliseDates(): void
    {
        $id = (int) ($this->item->id ?? 0);

        foreach (['founded', 'dissolved'] as $field) {
            $value = (string) ($this->item->{$field} ?? '');

            if ($id <= 0 || $value === '0000-00-00' || str_starts_with($value, '0000-00-00')) {
                $this->item->{$field} = '';
                $this->form->setValue($field, null, '');
            }
        }

        if (empty($this->item->founded_year)) {
            $this->item->founded_year = 'kein';
            $this->form->setValue('founded_year', null, 'kein');
        }
    }
}
