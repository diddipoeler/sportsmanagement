<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

final class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * Build the Joomla 5/6 administrator quickicon layout context.
     *
     * Retains the framework's original module parameters and layout data,
     * loads the module and component translations, and creates links to the
     * component's management screens. The current user's core.manage
     * permission controls whether the shortcuts may be displayed. Missing
     * Joomla identities are treated as unauthorised rather than causing a
     * PHP 8 method call on null; a skipped module remains skipped.
     *
     * @return array|false Prepared quickicon view variables, or false when
     *                     Joomla decides that the module should not render.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();
        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_quickicon', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $identity = $app->getIdentity();

        $data['componentEnabled'] = ComponentHelper::isEnabled('com_sportsmanagement', true);
        $data['canManage'] = $identity !== null
            && $identity->authorise('core.manage', 'com_sportsmanagement');

        $base = rtrim((string) Uri::base(), '/') . '/components/com_sportsmanagement/assets/icons/';
        $data['links'] = [
            [
                'url' => Route::_('index.php?option=com_sportsmanagement', false),
                'icon' => $base . 'transparent_schrift_48.png',
                'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'),
                'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LABEL'),
            ],
            [
                'url' => Route::_('index.php?option=com_sportsmanagement&view=extensions', false),
                'icon' => $base . 'extensions.png',
                'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'),
                'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LABEL'),
            ],
            [
                'url' => Route::_('index.php?option=com_sportsmanagement&view=projects', false),
                'icon' => $base . 'projekte.png',
                'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'),
                'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LABEL'),
            ],
            [
                'url' => Route::_('index.php?option=com_sportsmanagement&view=predictiongames', false),
                'icon' => $base . 'tippspiele.png',
                'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'),
                'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LABEL'),
            ],
            [
                'url' => Route::_('index.php?option=com_sportsmanagement&view=currentseasons', false),
                'icon' => $base . 'aktuellesaison.png',
                'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'),
                'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LABEL'),
            ],
        ];

        return $data;
    }
}
