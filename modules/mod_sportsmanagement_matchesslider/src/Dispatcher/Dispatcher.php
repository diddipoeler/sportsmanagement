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

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

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

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Matches Slider requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        /** @var DatabaseInterface $database */
        $database = $app->getContainer()->get(DatabaseInterface::class);
        $data['slidermatches'] = $this->getHelperFactory()
            ->getHelper('MatchesSliderHelper')
            ->getData($data['params'], $data['module'], $app, $database);

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
}
