<?php
namespace Diddipoeler\Module\SportsManagementProjectMap\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        try {
            /** @var DatabaseInterface $db */
            $db = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
            $seasonIds = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $mapData = $this->getHelperFactory()
                ->getHelper('ProjectMapHelper')
                ->getMapData($seasonIds, $db);

            $data['projects'] = $mapData['projects'];

            $assets = $app->getDocument()->getWebAssetManager();
            $assetName = 'mod_sportsmanagement_projectmap.worldmap';
            $assets->registerAndUseScript(
                $assetName,
                'modules/mod_sportsmanagement_projectmap/htmlworldmap/worldmap.js'
            );
            $assets->addInlineScript(
                $mapData['javascript'],
                ['position' => 'before'],
                [],
                [$assetName]
            );
        } catch (\Throwable $exception) {
            $data['projects'] = [];
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage('SportsManagement Project Map: ' . $exception->getMessage(), 'warning');
        }

        return $data;
    }
}
