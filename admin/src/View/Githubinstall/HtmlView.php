<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Githubinstall;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for the GitHub update download flow. */
final class HtmlView extends BaseHtmlView
{
    public string $github_link = '';

    /** @deprecated Kept for third-party template compatibility. */
    public array $_success_text = [];

    public function display($tpl = null)
    {
        $this->github_link = trim((string) ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_update_server_file', ''));
        $this->_success_text = [];

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'), 'download');
        parent::display($tpl);
    }
}
