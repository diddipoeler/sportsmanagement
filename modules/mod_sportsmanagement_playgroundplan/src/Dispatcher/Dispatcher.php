<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Playground Plan module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Dispatcher;

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

        $app = $this->getApplication();

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);
        $data['list'] = $this->getHelperFactory()
            ->getHelper('PlaygroundPlanHelper')
            ->getData($data['params'], $app, $data['module']);

        $wam = $app->getDocument()->getWebAssetManager();
        $wam->registerAndUseStyle(
            'mod_sportsmanagement_playgroundplan',
            'modules/mod_sportsmanagement_playgroundplan/css/mod_sportsmanagement_playgroundplan.css'
        );

        if ((int) $data['params']->get('mode', 0) === 0) {
            $wam->registerAndUseScript(
                'mod_sportsmanagement_playgroundplan.ticker',
                'modules/mod_sportsmanagement_playgroundplan/js/ticker.js'
            );
        }

        return $data;
    }
}
