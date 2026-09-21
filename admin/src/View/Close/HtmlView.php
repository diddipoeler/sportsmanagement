<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Close view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 * @subpackage com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Close;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Lightweight modal-close view used as the first modern dispatcher smoke path.
 */
class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement close view requires the Joomla administrator application.', 500);
        }

        $onlyModal = $app->getInput()->getBool('onlymodal');

        $script = $onlyModal
            ? 'if (window.parent && window.parent !== window) { window.parent.postMessage({type:"sportsmanagement:close-modal"}, "*"); }'
            : 'if (window.parent && window.parent !== window) { window.parent.location.reload(); }';

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->addInlineScript($script);
        }

        parent::display($tpl);
    }
}
