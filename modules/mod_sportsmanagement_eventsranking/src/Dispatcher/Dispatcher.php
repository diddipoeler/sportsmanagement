<?php
/**
 * Native Joomla 5/6 dispatcher for the events ranking module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Dispatcher;

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

        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $data['rankingData'] = $this->getHelperFactory()
            ->getHelper('EventsRankingHelper')
            ->getData($data['params'], $app, $database);

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_eventsranking',
            'modules/mod_sportsmanagement_eventsranking/css/mod_sportsmanagement_eventsranking.css',
            ['version' => 'auto']
        );

        return $data;
    }
}
