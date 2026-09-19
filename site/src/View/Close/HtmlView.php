<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Close view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Close;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

final class HtmlView extends SportsManagementHtmlView
{
    public function display($tpl = null)
    {
        $this->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
if (window.parent && window.parent !== window) {
    window.parent.location.reload();
} else if (window.history.length > 1) {
    window.history.back();
}
JS);
    }
}
