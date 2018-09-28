<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jsm_update_picture_path.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage updates
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;

$version			= '1.0.68';
$updateFileDate		= '2018-09-3';
$updateFileTime		= '00:05';
$updateDescription	='<span style="color:orange">Update Picture Path.</span>';
$excludeFile		='false';

$maxImportTime = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=880;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){ini_set('memory_limit',$maxImportMemory);}





?>