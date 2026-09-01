<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 compatibility entry point for the XML import workflow. */
final class HtmlView extends BaseHtmlView
{
    public $config;

    public function display($tpl = null)
    {
        $this->config = ComponentHelper::getParams('com_media');
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3'), 'upload');

        parent::display($tpl);
    }
}
