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

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
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

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Ranking requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_ranking', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        // The module directory is fixed; never derive a filesystem path from module data.
        $moduleName = 'mod_sportsmanagement_ranking';
        $style = 'modules/' . $moduleName . '/css/' . $moduleName . '.css';
        $document = $app->getDocument();

        if ($document instanceof HtmlDocument && is_file(JPATH_ROOT . '/' . $style)) {
            $document->getWebAssetManager()->registerAndUseStyle(
                $moduleName,
                $style,
                ['version' => 'auto']
            );
        }

        $ranking = $this->getHelperFactory()->getHelper('RankingHelper')->getData(
            $data['params'],
            $data['module'],
            $app
        );

        // A missing ranking must still be safe for Joomla 5/6 layouts to iterate.
        $data['list'] = is_array($ranking) ? $ranking : [];

        return $data;
    }
}
