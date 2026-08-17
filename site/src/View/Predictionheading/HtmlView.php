<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionheading;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/predictionheading/tmpl';
        parent::__construct($config);
    }
}
