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

    /**
     * Generate the ranking module context using Joomla's native layout.
     *
     * Selects the native template, loads component translations, registers
     * the optional stylesheet when present, and fetches ranking data from
     * the helper. Validates the site client before changing the layout
     * settings. Missing results become an empty iterable list so the
     * frontend template can render without PHP type errors.
     *
     * @return array|false Ranking template variables, or false if skipped.
     * @throws \RuntimeException If the dispatcher runs outside the site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Ranking requires the Joomla site application.', 500);
        }

        $data['params']->set('layout', 'native');

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
            $this->useRankingStylesheet($document, $moduleName, $style);
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
    /**
     * Activate the optional Ranking stylesheet without duplicate registration.
     *
     * Several Ranking module instances can share a Joomla HTML document.
     * Register the fixed stylesheet only if the asset name is absent from
     * the WebAssetManager registry, but mark the asset as used on every
     * render. This preserves native Joomla 5/6 asset ordering and cache tags
     * without duplicate-asset exceptions.
     *
     * @param HtmlDocument $document Current Joomla HTML document.
     * @param string $assetName Shared module asset identifier.
     * @param string $style Site-relative CSS asset path already checked on disk.
     * @return void
     */
    private function useRankingStylesheet(HtmlDocument $document, string $assetName, string $style): void
    {
        $assets = $document->getWebAssetManager();

        if (!$assets->assetExists('style', $assetName)) {
            $assets->registerStyle($assetName, $style, ['version' => 'auto']);
        }

        $assets->useStyle($assetName);
    }


}
