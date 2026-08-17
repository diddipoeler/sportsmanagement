<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
LegacyBootstrap::bootForView('about');
if (!class_exists('sportsmanagementModelAbout')) { \JLoader::import('components.com_sportsmanagement.models.about', JPATH_SITE); }
final class AboutModel extends \sportsmanagementModelAbout {}
