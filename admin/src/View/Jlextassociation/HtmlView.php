<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextassociation;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for an association. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Association form could not be loaded.', 500);
        }

        $this->normaliseDates();
        $app->setUserState('com_sportsmanagement.itemname', (string) ($this->item->name ?? ''));

        $isNew = empty($this->item->id);
        ToolbarHelper::title(
            Text::_($isNew ? 'JTOOLBAR_NEW' : 'JTOOLBAR_EDIT') . ': ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_TITLE'),
            'share-alt'
        );
        ToolbarHelper::apply('jlextassociation.apply');
        ToolbarHelper::save('jlextassociation.save');
        ToolbarHelper::save2new('jlextassociation.save2new');
        ToolbarHelper::save2copy('jlextassociation.save2copy');
        ToolbarHelper::cancel('jlextassociation.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

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
