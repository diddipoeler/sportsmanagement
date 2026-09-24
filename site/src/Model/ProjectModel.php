<?php
/**
 * Native Joomla 5/6 frontend project model.
 *
 * This concrete model exposes the shared project data API implemented by
 * SportsManagementProjectModel so legacy static callers can migrate without
 * duplicating Joomla database access.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class ProjectModel extends SportsManagementProjectModel
{
}
