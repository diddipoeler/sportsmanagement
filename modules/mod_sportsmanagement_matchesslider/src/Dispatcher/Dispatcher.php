<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement matches slider module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatchesSlider\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Prepare the matches slider data and associated frontend assets.
     *
     * Retrieves match data from the helper, normalises missing results and registers the slider stylesheet and deferred JavaScript for HTML documents.
     * The method rejects administrator execution and preserves Joomla's
     * original layout context when rendering is skipped.
     *
     * @return array|false Template variables, or false when skipped.
     * @throws \RuntimeException When the site context or database is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches Slider requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $database = $this->joomlaDatabase();
        $matches = $this->getHelperFactory()
            ->getHelper('MatchesSliderHelper')
            ->getData($data['params'], $data['module'], $app, $database);
        $data['slidermatches'] = is_array($matches) ? $matches : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $wam = $document->getWebAssetManager();
            $wam->registerAndUseScript(
                'mod_sportsmanagement_matchesslider',
                'modules/mod_sportsmanagement_matchesslider/assets/js/matchesslider.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
            $wam->registerAndUseStyle(
                'mod_sportsmanagement_matchesslider',
                'modules/mod_sportsmanagement_matchesslider/assets/css/mod_sportsmanagement_matchesslider.css',
                ['version' => 'auto']
            );
        }

        return $data;
    }

    /**
     * Obtain a valid Joomla database service for Matches Slider queries.
     *
     * Retrieves the registered DatabaseInterface from the service container
     * and rejects a missing or incompatible instance before helper execution.
     *
     * @return DatabaseInterface Database connection used by the module.
     * @throws \RuntimeException When the Joomla database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database service is unavailable.', 500);
        }

        return $database;
    }
}
