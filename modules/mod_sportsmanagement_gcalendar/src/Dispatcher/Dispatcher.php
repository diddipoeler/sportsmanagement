<?php
namespace Diddipoeler\Module\SportsManagementGcalendar\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $data = array_merge(
            $data,
            $this->getHelperFactory()
                ->getHelper('GcalendarHelper')
                ->getData($data['params'], $data['module'], $this->getApplication())
        );

        return $data;
    }
}
