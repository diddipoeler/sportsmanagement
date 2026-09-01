<?php
namespace Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Dispatcher;

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
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE);
        $app->getLanguage()->load(
            'mod_sportsmanagement_teamstats_ranking',
            JPATH_SITE . '/modules/mod_sportsmanagement_teamstats_ranking'
        );

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_teamstats_ranking',
            'modules/mod_sportsmanagement_teamstats_ranking/css/mod_sportsmanagement_teamstats_ranking.css'
        );

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $helper = $this->getHelperFactory()->getHelper('TeamStatsRankingHelper');

        return array_merge(
            $data,
            $helper->getData($data['params'], $joomlaDatabase)
        );
    }
}
