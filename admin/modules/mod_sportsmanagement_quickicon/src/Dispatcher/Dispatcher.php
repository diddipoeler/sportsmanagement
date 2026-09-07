<?php
namespace Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

final class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();
        $identity = $app->getIdentity();

        $data['componentEnabled'] = ComponentHelper::isEnabled('com_sportsmanagement', true);
        $data['canManage'] = $identity->authorise('core.manage', 'com_sportsmanagement');

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
