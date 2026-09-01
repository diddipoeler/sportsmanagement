<?php
namespace Diddipoeler\Module\SportsManagementActSeason\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
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

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $seasonIds = $componentParams->get('current_season', []);
        /** @var DatabaseInterface $database */
        $database = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
        $result = $this->getHelperFactory()
            ->getHelper('ActSeasonHelper')
            ->getData($seasonIds, $componentParams, $app, $database);

        $data['list'] = $result['list'];
        $data['federations'] = $result['federations'];
        $data['countriesByFederation'] = $result['countriesByFederation'];

        return $data;
    }
}
