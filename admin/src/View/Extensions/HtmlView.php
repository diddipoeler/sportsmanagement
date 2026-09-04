<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Extensions;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public array $sporttypes = [];

    public function display($tpl = null)
    {
        $sporttypes = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_sport_types', []);
        $this->sporttypes = is_array($sporttypes) ? array_values($sporttypes) : array_values((array) $sporttypes);

        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.extensions.icons',
            'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        $this->addToolbar();
        parent::display($tpl);
    }

    public function addIcon(string $image, string $url, string $text, bool $newWindow = false): string
    {
        $language = Factory::getApplication()->getLanguage();
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

    private function addToolbar(): void
    {
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'extensions');
    }
}
