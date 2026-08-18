<?php
namespace Diddipoeler\Module\SportsManagementActSeason\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $seasonIds = $componentParams->get('current_season', []);
        $result = $this->getHelperFactory()->getHelper('ActSeasonHelper')->getData($seasonIds, $componentParams, $this->getApplication());

        $data['list'] = $result['list'];
        $data['federations'] = $result['federations'];
        $data['countriesByFederation'] = $result['countriesByFederation'];

        return $data;
    }
}
