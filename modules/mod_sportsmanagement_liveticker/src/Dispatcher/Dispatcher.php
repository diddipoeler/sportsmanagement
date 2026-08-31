<?php
namespace Diddipoeler\Module\SportsManagementLiveticker\Site\Dispatcher;

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

        $result = $this->getHelperFactory()
            ->getHelper('LivetickerHelper')
            ->getData($data['params'], $data['module'], $this->getApplication());

        return array_merge($data, $result);
    }
}
