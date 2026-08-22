<?php
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Dispatcher;

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
        $data['list'] = $this->getHelperFactory()
            ->getHelper('CountRekordHelper')
            ->getData($data['params'], $data['module']);

        return $data;
    }
}
