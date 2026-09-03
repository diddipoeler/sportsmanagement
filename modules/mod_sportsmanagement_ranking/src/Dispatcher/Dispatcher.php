<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Ranking module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRanking\Site\Dispatcher;

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

        $data['params']->set('layout', 'native');
        $app = $this->getApplication();
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $moduleName = (string) ($data['module']->module ?? 'mod_sportsmanagement_ranking');
        $style = 'modules/' . $moduleName . '/css/' . $moduleName . '.css';
        $assets = $app->getDocument()->getWebAssetManager();

        if (is_file(JPATH_ROOT . '/' . $style)) {
            $assets->registerAndUseStyle(
                $moduleName,
                $style,
                ['version' => 'auto']
            );
        }

        $data['list'] = $this->getHelperFactory()->getHelper('RankingHelper')->getData(
            $data['params'],
            $data['module'],
            $app
        );

        return $data;
    }
}
