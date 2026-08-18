<?php
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Dispatcher;

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
        $data['list'] = $this->getHelperFactory()->getHelper('RandomPlayerHelper')->getData(
            $data['params'],
            $this->getApplication()
        );

        return $data;
    }
}
