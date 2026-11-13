<?php
/**
 * Native Joomla 5/6 helper for the SportsManagement quickicon module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;

abstract class QuickIconHelper
{
    protected static array $buttons = [];

    /**
     * Find the module position of the first published administrator quickicon.
     *
     * Queries Joomla's modules table with the administrator client and
     * published state filters to avoid selecting a frontend or inactive
     * legacy module instance.
     *
     * @return string Module position name, or an empty string when unavailable.
     */
    public static function getModPosition(): string
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $database->createQuery()
            ->select($database->quoteName('position'))
            ->from($database->quoteName('#__modules'))
            ->where($database->quoteName('module') . ' = ' . $database->quote('mod_sportsmanagement_quickicon'))
            // A frontend copy or unpublished legacy instance must not decide
            // where the active administrator quick icons are rendered.
            ->where($database->quoteName('client_id') . ' = 1')
            ->where($database->quoteName('published') . ' = 1')
            ->order($database->quoteName('id') . ' ASC');
        $database->setQuery($query, 0, 1);

        return (string) $database->loadResult();
    }

    /**
     * Return the cached quickicon buttons for a module configuration.
     *
     * Uses the configured context to select default SportsManagement buttons
     * and returns the cached array by reference for legacy layout consumers.
     * Unrecognised contexts intentionally produce an empty button collection.
     *
     * @param \Joomla\Registry\Registry $params Administrator module parameters.
     * @return array<int,array<string,mixed>> Quickicon button definitions.
     */
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

    /**
     * Organise quickicon button definitions by their display group.
     *
     * Preserves each button's original fields and uses the module's default
     * translated group key when no explicit group is configured.
     *
     * @param array<int,array<string,mixed>> $buttons Quickicon definitions.
     * @return array<string,array<int,array<string,mixed>>> Buttons keyed by group.
     */
    public static function groupButtons(array $buttons): array
    {
        $grouped = [];

        foreach ($buttons as $button) {
            $group = (string) ($button['group'] ?? 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL');
            $grouped[$group][] = $button;
        }

        return $grouped;
    }

    /**
     * Resolve the quickicon heading from Joomla's current administrator language.
     *
     * Uses the configured context's translation key when available, otherwise
     * falls back to the title of the active administrator module.
     *
     * @param \Joomla\Registry\Registry $params Module display configuration.
     * @param object $module Joomla administrator module record.
     * @return string Localised heading or fallback module title.
     */
    public static function getTitle($params, $module): string
    {
        $key = (string) $params->get('context', 'mod_sportsmanagement_quickicon') . '_title';
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $language = $app->getLanguage();

        return $language->hasKey($key)
            ? Text::_($key)
            : (string) ($module->title ?? '');
    }

    /**
     * Build the default SportsManagement administrator quickicon collection.
     *
     * Provides Joomla-routed links with language keys, icons, access rules
     * and grouping metadata for the component management views.
     *
     * @return array<int,array<string,mixed>> Standard administrator buttons.
     */
    private static function defaultButtons(): array
    {
        $group = 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL';
        $access = ['core.manage', 'com_sportsmanagement'];

        return [
            [
                'link' => Route::_('index.php?option=com_sportsmanagement', false),
                'image' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                'icon' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=extensions', false),
                'image' => 'components/com_sportsmanagement/assets/icons/extensions.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/extensions.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=projects', false),
                'image' => 'components/com_sportsmanagement/assets/icons/projekte.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/projekte.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=predictiongames', false),
                'image' => 'components/com_sportsmanagement/assets/icons/tippspiele.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/tippspiele.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'),
                'access' => $access,
                'group' => $group,
            ],
            [
                'link' => Route::_('index.php?option=com_sportsmanagement&view=currentseasons', false),
                'image' => 'components/com_sportsmanagement/assets/icons/aktuellesaison.png',
                'icon' => 'components/com_sportsmanagement/assets/icons/aktuellesaison.png',
                'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'),
                'access' => $access,
                'group' => $group,
            ],
        ];
    }
}
