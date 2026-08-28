<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlexports;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator XML export view. */
final class HtmlView extends BaseHtmlView
{
    public string $exportSystem = '';
    public string $request_url = '';
    public string $table_data_div = '';

    public function display($tpl = null)
    {
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);

        $app = Factory::getApplication();
        $this->exportSystem = (string) $app->get('sitename', '');
        $this->request_url = Uri::getInstance()->toString();

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_TITLE'), 'download');
        ToolbarHelper::custom(
            'jlxmlexports.export',
            'upload',
            'upload',
            Text::_('JTOOLBAR_EXPORT'),
            false
        );
        ToolbarHelper::divider();
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }
}
