<?php
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Dispatcher;

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

        $this->getApplication()->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $overview = $this->getHelperFactory()
            ->getHelper('FirstLeagueOverviewHelper')
            ->getData($data['params']);

        $data['firstleagueoverview'] = $overview['projects'];
        $data['federations'] = $overview['federations'];

        $this->getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'mod_sportsmanagement_firstleagueoverview',
                'modules/mod_sportsmanagement_firstleagueoverview/css/mod_sportsmanagement_firstleagueoverview.css'
            );

        return $data;
    }
}
