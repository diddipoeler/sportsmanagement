<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement GCalendar module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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

        $moduleId = (int) ($data['module']->id ?? 0);
        $document = $this->getApplication()->getDocument();
        $assets = $document->getWebAssetManager();

        $assets->registerAndUseStyle(
            'mod_sportsmanagement_gcalendar.calendar',
            'modules/mod_sportsmanagement_gcalendar/tmpl/gcalendar.css',
            ['version' => 'auto']
        );
        $assets->registerAndUseScript(
            'mod_sportsmanagement_gcalendar.calendar',
            'modules/mod_sportsmanagement_gcalendar/js/gcalendar.js',
            ['version' => 'auto'],
            ['defer' => true]
        );

        $document->addScriptOptions(
            'mod_sportsmanagement_gcalendar.' . $moduleId,
            $data['calendarConfig'] ?? []
        );

        $data['calendarOptionsKey'] = 'mod_sportsmanagement_gcalendar.' . $moduleId;

        return $data;
    }
}
