<?php
namespace Diddipoeler\Module\SportsManagementCalendar\Site\Dispatcher;

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

        $requestedLayout = (string) $data['params']->get(
            'which_layout',
            $data['params']->get('layout', 'default_jsm')
        );

        // The historical Arrobe and TOAST UI layouts depended on legacy jQuery/
        // Bootstrap 4/TUI runtimes. Keep their stored parameter values working,
        // but render them through the native Joomla 5/6 calendar runtime.
        $layout = match ($requestedLayout) {
            'default_arrobefr', 'default_tuicalendar' => 'default_jsm',
            default => $requestedLayout,
        };

        $data['params']->set('which_layout', $layout);
        $data['params']->set('layout', $layout);

        $helper = $this->getHelperFactory()->getHelper('CalendarHelper');

        return array_merge(
            $data,
            $helper->getData($data['params'], $data['module'], $this->getApplication())
        );
    }
}
