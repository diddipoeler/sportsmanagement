<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Google Calendar module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
