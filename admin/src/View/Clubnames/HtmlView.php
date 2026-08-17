<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Clubnames;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
LegacyBootstrap::boot();
if (!class_exists('sportsmanagementViewClubnames')) { \JLoader::import('components.com_sportsmanagement.views.clubnames.view.html', JPATH_ADMINISTRATOR); }
final class HtmlView extends \sportsmanagementViewClubnames { public function __construct($config = []) { $config['template_path'] = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/clubnames/tmpl'; parent::__construct($config); } }
