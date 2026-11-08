<?php
/**
 * Native Joomla 5/6 administrator view for SportsManagement extensions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Extensions;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public array $sporttypes = [];

    /**
     * Render the native administrator extensions overview.
     * Reads the enabled SportsManagement sport types, registers the overview icon stylesheet with Joomla's WebAssetManager for HTML documents, builds the toolbar and delegates rendering to the Joomla MVC view.
     * @param string|null $tpl Optional template layout override.
     * @return void
     */
    public function display($tpl = null)
    {
        $sporttypes = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_sport_types', []);
        $this->sporttypes = is_array($sporttypes) ? array_values($sporttypes) : array_values((array) $sporttypes);

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.extensions.icons',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Build a local extension shortcut with accessible direction and link behaviour.
     * Uses the administrator language direction for icon alignment, renders a Joomla-managed icon asset, escapes visible text and link URL, and adds safe new-tab attributes when requested.
     * @param string $image Icon filename under the component assets/icons directory.
     * @param string $url Target administrator or extension URL.
     * @param string $text Translated shortcut label.
     * @param bool $newWindow Open the link in a separate tab when true.
     * @return string HTML for the extension shortcut tile.
     */
    public function addIcon(string $image, string $url, string $text, bool $newWindow = false): string
    {
        $language = self::administratorApplication()->getLanguage();
        $float = $language->isRTL() ? 'right' : 'left';
        $target = $newWindow ? ' target="_blank" rel="noopener noreferrer"' : '';
        $icon = HTMLHelper::_(
            'image',
            'administrator/components/com_sportsmanagement/assets/icons/' . $image,
            '',
            ['loading' => 'lazy']
        );

        return '<div style="float:' . $float . ';"><div class="icon"><a href="'
            . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $target . '>'
            . $icon . '<span>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span></a></div></div>';
    }

    /**
     * Resolve the active Joomla administrator application from the service container.
     * Uses the Joomla 5/6 container instead of deprecated global application access and rejects attempts to render the administrator extensions view on the site client.
     * @return AdministratorApplication Validated administrator application.
     * @throws \RuntimeException When the current application is not administrator.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement extensions view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

    /**
     * Configure navigation and title for the SportsManagement extensions page.
     * Adds a back action to the component control panel and the translated manager title using Joomla's supported administrator toolbar helper.
     * @return void
     */
    private function addToolbar(): void
    {
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'extensions');
    }
}
