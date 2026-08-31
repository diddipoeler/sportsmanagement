<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotetxt;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator editor view for random-quote source files. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $source;
    public $state;
    public string $file_name = '';

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->file_name = $app->getInput()->getString('file_name');
        $this->form = $this->get('Form');
        $this->source = $this->get('Source');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Quote source form could not be loaded.', 500);
        }

        if ($this->file_name === '' && !empty($this->source->filename)) {
            $this->file_name = (string) $this->source->filename;
        }

        ToolbarHelper::title(
            Text::_($this->file_name !== ''
                ? 'COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_EDIT'
                : 'COM_SPORTSMANAGEMENT_ADMIN_SMQUOTE_ADD_NEW'),
            'quote'
        );
        ToolbarHelper::apply('smquotetxt.apply');
        ToolbarHelper::save('smquotetxt.save');
        ToolbarHelper::cancel('smquotetxt.cancel', 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
