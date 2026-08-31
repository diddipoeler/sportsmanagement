<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquotestxt;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for random-quote source files. */
final class HtmlView extends BaseHtmlView
{
    public array $files = [];

    public function display($tpl = null): void
    {
        $model = $this->getModel();
        $this->files = method_exists($model, 'getTXTFiles') ? $model->getTXTFiles() : [];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TXT_EDITORS'), 'quote');
        ToolbarHelper::back(
            Text::_('JPREV'),
            Route::_('index.php?option=com_sportsmanagement&view=smquotes')
        );

        parent::display($tpl);
    }
}
