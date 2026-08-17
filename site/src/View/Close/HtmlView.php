<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Close;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

final class HtmlView extends SportsManagementHtmlView
{
    public function display($tpl = null)
    {
        $this->getDocument()->addScriptDeclaration(<<<'JS'
if (window.parent && window.parent !== window) {
    window.parent.location.reload();
} else if (window.history.length > 1) {
    window.history.back();
}
JS);
    }
}
