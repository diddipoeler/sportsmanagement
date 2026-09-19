<?php
/**
 * Native Joomla 5/6 administrator editor view for random-quote source files.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotetxt;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
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
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement Smquotetxt view requires the Joomla administrator application.', 500);
        }

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
