<?php
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Dispatcher;

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
        $data['params']->set(
            'layout',
            (string) $data['params']->get('which_layout', $data['params']->get('layout', 'default'))
        );

        $helper = $this->getHelperFactory()->getHelper('CalendarHelper');

        return array_merge(
            $data,
            $helper->getData($data['params'], $data['module'], $this->getApplication())
        );
    }
}
