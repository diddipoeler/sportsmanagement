<?php
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Dispatcher;

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

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $data['list'] = $this->getHelperFactory()
            ->getHelper('CountRekordHelper')
            ->getData($data['params'], $data['module'], $database);

        return $data;
    }
}
