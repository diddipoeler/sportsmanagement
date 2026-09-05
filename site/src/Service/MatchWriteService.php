<?php
/**
 * Joomla 5/6 frontend alias for the shared match write service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\MatchWriteService as AdministratorMatchWriteService;

/** Frontend compatibility alias for the shared administrator-side write service. */
class_alias(AdministratorMatchWriteService::class, __NAMESPACE__ . '\\MatchWriteService');
