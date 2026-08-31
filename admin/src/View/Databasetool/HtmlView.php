<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Databasetool;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the database tool. */
final class HtmlView extends BaseHtmlView
{
    public string $request_url = '';
    public string $task = '';
    public int $step = 0;
    public int $totals = 0;
    public string $work_table = '';
    public int $bar_value = 100;

    public function display($tpl = null)
    {
        $this->request_url = Uri::getInstance()->toString();
        $this->task = $this->getApplication()->getInput()->getCmd('task');

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE'), 'database');

        parent::display($tpl);
    }
}
