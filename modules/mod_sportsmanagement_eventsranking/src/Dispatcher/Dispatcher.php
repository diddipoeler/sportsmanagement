<?php
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Dispatcher;

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
        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);
        $data['rankingData'] = $this->getHelperFactory()
            ->getHelper('EventsRankingHelper')
            ->getData($data['params'], $this->getApplication());

        $document = $this->getApplication()->getDocument();
        if (method_exists($document, 'getWebAssetManager')) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_eventsranking',
                'modules/mod_sportsmanagement_eventsranking/css/mod_sportsmanagement_eventsranking.css'
            );
        }

        return $data;
    }
}
