<?php
namespace Diddipoeler\Module\SportsManagementRanking\Site\Dispatcher;

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
        $data['params']->set('layout', 'native');
        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        $data['list'] = $this->getHelperFactory()->getHelper('RankingHelper')->getData(
            $data['params'],
            $data['module'],
            $this->getApplication()
        );

        return $data;
    }
}
