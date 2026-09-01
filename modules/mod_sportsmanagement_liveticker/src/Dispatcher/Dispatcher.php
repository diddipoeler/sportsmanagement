<?php
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Uri\Uri;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $result = $this->getHelperFactory()
            ->getHelper('LivetickerHelper')
            ->getData($data['params'], $data['module'], $this->getApplication());

        $assets = $this->getApplication()->getDocument()->getWebAssetManager();
        $assets->registerAndUseScript(
            'mod_sportsmanagement_liveticker',
            'modules/mod_sportsmanagement_liveticker/js/turtushout.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        $cssFile = basename((string) ($result['cssFile'] ?? ''));
        if ($cssFile !== '') {
            $assets->registerAndUseStyle(
                'mod_sportsmanagement_liveticker',
                'modules/mod_sportsmanagement_liveticker/css/' . $cssFile,
                ['version' => 'auto']
            );
        }

        $result['refreshUrl'] = rtrim((string) Uri::base(), '/')
            . '/index.php?option=com_ajax&module=sportsmanagement_liveticker&method=refresh&format=raw';

        return array_merge($data, $result);
    }
}
