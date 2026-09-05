<?php
/**
 * Native Joomla 5/6 dispatcher for the first league overview module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Dispatcher;

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
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $overview = $this->getHelperFactory()
            ->getHelper('FirstLeagueOverviewHelper')
            ->getData($data['params'], $database);

        $data['firstleagueoverview'] = $overview['projects'];
        $data['federations'] = $overview['federations'];

        $wam = $app->getDocument()->getWebAssetManager();
        $wam->useScript('bootstrap.tab');
        $wam->registerAndUseStyle(
            'mod_sportsmanagement_firstleagueoverview',
            'modules/mod_sportsmanagement_firstleagueoverview/css/mod_sportsmanagement_firstleagueoverview.css',
            ['version' => 'auto']
        );

        return $data;
    }
}
