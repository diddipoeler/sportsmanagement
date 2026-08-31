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

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $data['list'] = $this->getHelperFactory()
            ->getHelper('CountRekordHelper')
            ->getData($data['params'], $data['module'], $database);

        return $data;
    }
}
