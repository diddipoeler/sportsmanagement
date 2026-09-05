<?php
/**
 * Compatibility helper for the Joomla 5/6 SportsManagement quickicon module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;

abstract class ModSportsmanagementQuickIconHelper
{
    protected static array $buttons = [];

    public static function getModPosition(): string
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $database->getQuery(true)
            ->select($database->quoteName('position'))
            ->from($database->quoteName('#__modules'))
            ->where($database->quoteName('module') . ' = ' . $database->quote('mod_sportsmanagement_quickicon'))
            ->order($database->quoteName('id') . ' ASC');
        $database->setQuery($query, 0, 1);

        return (string) $database->loadResult();
    }

    public static function &getButtons($params): array
    {
        $key = (string) $params;

        if (!isset(self::$buttons[$key])) {
            $context = (string) $params->get('context', 'mod_sportsmanagement_quickicon');

            self::$buttons[$key] = $context === 'mod_sportsmanagement_quickicon'
                ? self::defaultButtons()
                : [];
        }

        return self::$buttons[$key];
    }

    public static function groupButtons(array $buttons): array
    {
        $grouped = [];

        foreach ($buttons as $button) {
            $group = (string) ($button['group'] ?? 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL');
            $grouped[$group][] = $button;
        }

        return $grouped;
    }

    public static function getTitle($params, $module): string
    {
        $key = (string) $params->get('context', 'mod_sportsmanagement_quickicon') . '_title';
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $language = $app->getLanguage();

        return $language->hasKey($key)
            ? Text::_($key)
            : (string) ($module->title ?? '');
    }

    private static function defaultButtons(): array
    {
        $group = 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL';
        $access = ['core.manage', 'com_sportsmanagement'];

        return [
            [
                'link' => Route::_('index.php?option=com_sportsmanagement'),
                'image' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                'icon' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=extensions'),
                'image' => 'components/com_sportsmanagement/assets/icons/extensions.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/extensions.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=projects'),
                'image' => 'components/com_sportsmanagement/assets/icons/projekte.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/projekte.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=predictiongames'),
                'image' => 'components/com_sportsmanagement/assets/icons/tippspiele.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/tippspiele.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=currentseasons'),
                'image' => 'components/com_sportsmanagement/assets/icons/aktuellesaison.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/aktuellesaison.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'),
                'access' => $access,
                'group' => $group,
            ],
        ];
    }
}
