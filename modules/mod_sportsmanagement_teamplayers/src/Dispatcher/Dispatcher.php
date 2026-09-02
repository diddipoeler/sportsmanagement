<?php
/**
 * Native Joomla 5/6 dispatcher for the TeamPlayers module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamPlayers\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $data['params']->set('layout', 'native');

        $app = $this->getApplication();
        $language = $app->getLanguage();
        $language->load('com_sportsmanagement', JPATH_SITE, null, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $payload = $this->getHelperFactory()
            ->getHelper('TeamPlayersHelper')
            ->getData($data['params'], $database);

        $data['project'] = $payload['project'];
        $data['players'] = $payload['players'];
        $data['roster'] = $payload['roster'];

        $assets = $app->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'mod_sportsmanagement_teamplayers.native',
            'modules/mod_sportsmanagement_teamplayers/css/native.css',
            ['version' => 'auto']
        );

        if ((string) $data['params']->get('template', 'L') === 'C') {
            $assets->registerAndUseScript(
                'mod_sportsmanagement_teamplayers.native',
                'modules/mod_sportsmanagement_teamplayers/js/native.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
        }

        return $data;
    }
}
