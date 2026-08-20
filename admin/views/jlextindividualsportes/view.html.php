<?php
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\View\Jlextindividualsportes\HtmlView;

if (!class_exists('sportsmanagementViewjlextindividualsportes', false)) {
    class_alias(HtmlView::class, 'sportsmanagementViewjlextindividualsportes');
}
