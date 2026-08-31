<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditors;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for extended XML/PHP files. */
final class HtmlView extends BaseHtmlView
{
    public array $files = [];

    public function display($tpl = null): void
    {
        $model = $this->getModel();
        $this->files = method_exists($model, 'getXMLFiles') ? $model->getXMLFiles() : [];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EDITORS'), 'xml-edits');
        ToolbarHelper::back(
            Text::_('JPREV'),
            Route::_('index.php?option=com_sportsmanagement&view=cpanel')
        );

        parent::display($tpl);
    }
}
