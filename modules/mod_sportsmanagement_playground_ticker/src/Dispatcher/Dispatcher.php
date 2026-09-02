<?php
namespace Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Dispatcher;

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

        $helper = $this->getHelperFactory()->getHelper('PlaygroundTickerHelper');
        $app = $this->getApplication();

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $data['playgrounds'] = $helper->getData($data['params'], $app);
        $data['module']->picture_server = $helper->getPictureServer($data['params'], $app);

        $assets = $app->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_playground_ticker',
            'modules/mod_sportsmanagement_playground_ticker/css/mod_sportsmanagement_playground_ticker.css',
            ['version' => 'auto']
        );

        if (strtoupper((string) $data['params']->get('mode', 'L')) === 'B') {
            $assets->useScript('bootstrap.carousel');
        }

        return $data;
    }
}
