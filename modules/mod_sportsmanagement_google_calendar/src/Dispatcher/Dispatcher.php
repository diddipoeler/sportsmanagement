<?php
namespace Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Dispatcher;

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

        try {
            $data = array_merge(
                $data,
                $this->getHelperFactory()
                    ->getHelper('GoogleCalendarHelper')
                    ->getData($data['params'], $data['module'], $this->getApplication())
            );
        } catch (\Throwable $exception) {
            $this->getApplication()->enqueueMessage(
                'JSM Google Calendar error: ' . $exception->getMessage(),
                'error'
            );
            $data['events'] = [];
        }

        return $data;
    }
}
