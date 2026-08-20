<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService as AdministratorMatchWriteService;

/** Frontend compatibility alias for the shared administrator-side write service. */
class_alias(AdministratorMatchWriteService::class, __NAMESPACE__ . '\\MatchWriteService');
