<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Close;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Lightweight modal-close view used as the first modern dispatcher smoke path.
 */
class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $input = $this->getModel() ? null : null;
        $app = \Joomla\CMS\Factory::getApplication();
        $onlyModal = $app->getInput()->getBool('onlymodal');

        $script = $onlyModal
            ? 'if (window.parent && window.parent !== window) { window.parent.postMessage({type:"sportsmanagement:close-modal"}, "*"); }'
            : 'if (window.parent && window.parent !== window) { window.parent.location.reload(); }';

        $app->getDocument()->getWebAssetManager()->addInlineScript($script);

        parent::display($tpl);
    }
}
