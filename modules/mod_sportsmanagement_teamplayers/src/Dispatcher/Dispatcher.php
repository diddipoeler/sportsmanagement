<?php
namespace Diddipoeler\Module\SportsManagementTeamPlayers\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $data['params']->set('layout', 'native');

        $app = $this->getApplication();
        $language = $app->getLanguage();
        $language->load('com_sportsmanagement', JPATH_SITE, null, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $payload = $this->getHelperFactory()
            ->getHelper('TeamPlayersHelper')
            ->getData($data['params'], $database);

        $data['project'] = $payload['project'];
        $data['players'] = $payload['players'];
        $data['roster'] = $payload['roster'];

        $assets = $app->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_teamplayers.native',
            'modules/mod_sportsmanagement_teamplayers/css/native.css'
        );

        if ((string) $data['params']->get('template', 'L') === 'C') {
            $assets->registerAndUseScript(
                'mod_sportsmanagement_teamplayers.native',
                'modules/mod_sportsmanagement_teamplayers/js/native.js'
            );
        }

        return $data;
    }
}
