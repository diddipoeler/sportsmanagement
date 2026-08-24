<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquote;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a SportsManagement quote. */
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
            throw new \RuntimeException('Quote form could not be loaded.', 500);
        }

        $isNew = empty($this->item->id);
        $author = (string) ($this->item->author ?? '');
        $this->item->name = $author;
        $app->setUserState('com_sportsmanagement.itemname', $author);

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_ADD_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_EDIT'),
            'quote'
        );
        ToolbarHelper::apply('smquote.apply');
        ToolbarHelper::save('smquote.save');
        ToolbarHelper::save2new('smquote.save2new');
        ToolbarHelper::save2copy('smquote.save2copy');
        ToolbarHelper::cancel('smquote.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
