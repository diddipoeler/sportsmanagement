<?php
namespace Diddipoeler\Module\SportsManagementMatchesSlider\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $data['slidermatches'] = $this->getHelperFactory()
            ->getHelper('MatchesSliderHelper')
            ->getData($data['params'], $data['module'], $app);

        $wam = $app->getDocument()->getWebAssetManager();
        $wam->registerAndUseScript(
            'mod_sportsmanagement_matchesslider',
            'modules/mod_sportsmanagement_matchesslider/assets/js/matchesslider.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
        $wam->registerAndUseStyle(
            'mod_sportsmanagement_matchesslider',
            'modules/mod_sportsmanagement_matchesslider/assets/css/mod_sportsmanagement_matchesslider.css',
            ['version' => 'auto']
        );

        return $data;
    }
}
