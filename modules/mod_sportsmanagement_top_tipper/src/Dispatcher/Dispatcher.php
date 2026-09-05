<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Top Tipper module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTopTipper\Site\Dispatcher;

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
        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE);
        $app->getLanguage()->load('mod_sportsmanagement_top_tipper', JPATH_SITE . '/modules/mod_sportsmanagement_top_tipper');

        $app->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'mod_sportsmanagement_top_tipper',
            'modules/mod_sportsmanagement_top_tipper/css/mod_sportsmanagement_top_tipper.css'
        );

        $helper = $this->getHelperFactory()->getHelper('TopTipperHelper');

        return array_merge(
            $data,
            $helper->getData($data['params'], $data['module'], $app)
        );
    }
}
