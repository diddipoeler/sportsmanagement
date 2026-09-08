<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRanking\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
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

        if (!$app instanceof SiteApplication) {
            throw new \RuntimeException('SportsManagement Ranking requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_ranking', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

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
