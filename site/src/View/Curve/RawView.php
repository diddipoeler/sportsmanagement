<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Curve;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\CurveModel;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Native Joomla 5/6 raw view for the curve endpoint.
 *
 * The historic raw view only initialized curve data and emitted no response
 * body. Keep that observable behaviour while removing its legacy base-view
 * and static project-model dependencies.
 */
final class RawView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof CurveModel) {
            throw new \RuntimeException('Curve raw view requires CurveModel.', 500);
        }

        // Intentionally no output: this preserves the legacy raw endpoint.
    }
}
