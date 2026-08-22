<?php
namespace Diddipoeler\Module\SportsManagementTopTipper\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE);
        $app->getLanguage()->load('mod_sportsmanagement_top_tipper', JPATH_SITE . '/modules/mod_sportsmanagement_top_tipper');

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_top_tipper',
            'modules/mod_sportsmanagement_top_tipper/css/mod_sportsmanagement_top_tipper.css'
        );

        $helper = $this->getHelperFactory()->getHelper('TopTipperHelper');

        return array_merge(
            $data,
            $helper->getData($data['params'], $data['module'], $app)
        );
    }
}
