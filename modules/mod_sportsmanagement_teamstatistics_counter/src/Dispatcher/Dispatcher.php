<?php
namespace Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $data['data'] = $this->getHelperFactory()
            ->getHelper('TeamStatisticsCounterHelper')
            ->getData($data['params']);

        $document = $this->getApplication()->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_teamstatistics_counter',
                'modules/mod_sportsmanagement_teamstatistics_counter/css/mod_sportsmanagement_teamstatistics_counter.css'
            );
        }

        return $data;
    }
}
