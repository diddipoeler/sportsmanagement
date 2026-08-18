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
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $data['componentEnabled'] = ComponentHelper::isEnabled('com_sportsmanagement', true);
        $base = Uri::base() . 'components/com_sportsmanagement/assets/icons/';
        $data['links'] = [
            ['url' => Route::_('index.php?option=com_sportsmanagement'), 'icon' => $base . 'transparent_schrift_48.png', 'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'), 'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LABEL')],
            ['url' => Route::_('index.php?option=com_sportsmanagement&view=extensions'), 'icon' => $base . 'extensions.png', 'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'), 'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LABEL')],
            ['url' => Route::_('index.php?option=com_sportsmanagement&view=projects'), 'icon' => $base . 'projekte.png', 'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'), 'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LABEL')],
            ['url' => Route::_('index.php?option=com_sportsmanagement&view=predictiongames'), 'icon' => $base . 'tippspiele.png', 'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'), 'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LABEL')],
            ['url' => Route::_('index.php?option=com_sportsmanagement&view=currentseasons'), 'icon' => $base . 'aktuellesaison.png', 'title' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'), 'label' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LABEL')],
        ];
        return $data;
    }
}
