<?php
/**
 * Native Joomla 5/6 administrator SportsManagement GitHubinstall view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Githubinstall;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for the GitHub update download flow. */
final class HtmlView extends BaseHtmlView
{
    public string $github_link = '';
    public bool $isLegacyUpdateSource = false;

    /** @deprecated Kept for third-party template compatibility. */
    public array $_success_text = [];

    /**
     * Render the protected GitHub package confirmation screen.
     *
     * Only component administrators can prepare an update. The configured
     * archive source is displayed before any installation POST request.
     *
     * @param string|null $tpl Optional Joomla layout name.
     * @return void
     */
    public function display($tpl = null)
    {
        $identity = Factory::getApplication()->getIdentity();

        if ($identity === null || (int) ($identity->id ?? 0) < 1
            || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $configuredUrl = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_update_server_file', '');
        $this->github_link = is_string($configuredUrl) && strlen($configuredUrl) <= 2048
            ? trim($configuredUrl) : '';
        $this->isLegacyUpdateSource = str_contains($this->github_link, '/sportsmanagement/archive/master.zip')
            || str_contains($this->github_link, '/sportsmanagement/archive/refs/heads/master.zip');
        $this->_success_text = [];

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_GITHUBINSTALL'), 'download');
        parent::display($tpl);
    }
}
