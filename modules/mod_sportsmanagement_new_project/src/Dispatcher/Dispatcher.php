<?php
namespace Diddipoeler\Module\SportsManagementNewProject\Site\Dispatcher;

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
        $helper = $this->getHelperFactory()->getHelper('NewProjectHelper');
        $data['list'] = $helper->getData($data['params'], $this->getApplication());
        $data['canCreateArticles'] = $helper->canCreateArticles($data['params'], $this->getApplication());

        return $data;
    }
}
