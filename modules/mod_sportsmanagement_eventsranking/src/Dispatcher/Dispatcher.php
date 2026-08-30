<?php
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Dispatcher;

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
        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $data['rankingData'] = $this->getHelperFactory()
            ->getHelper('EventsRankingHelper')
            ->getData($data['params'], $app, $database);

        $document = $app->getDocument();
        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_eventsranking',
                'modules/mod_sportsmanagement_eventsranking/css/mod_sportsmanagement_eventsranking.css'
            );
        }

        return $data;
    }
}
