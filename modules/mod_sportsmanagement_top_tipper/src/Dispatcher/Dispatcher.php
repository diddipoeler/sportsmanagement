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
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Build the Top Tipper template context for the site application.
     *
     * Load translations, register the stylesheet, and merge prediction
     * ranking data with the Joomla module context. Only the documented
     * template variables are copied from helper output, keeping all Joomla
     * framework-owned layout variables intact.
     *
     * @return array|false Layout data, or false if rendering is skipped.
     * @throws \RuntimeException Outside Joomla's site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Top Tipper requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_top_tipper', JPATH_SITE . '/modules/mod_sportsmanagement_top_tipper', $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_top_tipper',
                'modules/mod_sportsmanagement_top_tipper/css/mod_sportsmanagement_top_tipper.css'
            );
        }

        $helper = $this->getHelperFactory()->getHelper('TopTipperHelper');

        $payload = $helper->getData($data['params'], $data['module'], $app);

        // Some legacy helpers return no data; only arrays may be merged.
        $payload = is_array($payload) ? $payload : [];

        // Expose only the keys supplied by TopTipperHelper::getData().
        // Prevent helper output from overriding Joomla framework context.
        foreach ([
            'predictionGame', 'predictionProjects', 'predictionProject',
            'projectId', 'roundId', 'fromRound', 'toRound', 'roundOptions',
            'rankingType', 'rankingRows', 'currentMemberId', 'topTipperConfig',
            'databaseSelector', 'rankingUrl', 'resultsUrl', 'typeOptions',
        ] as $key) {
            if (array_key_exists($key, $payload)) {
                $data[$key] = $payload[$key];
            }
        }

        return $data;
    }
}
