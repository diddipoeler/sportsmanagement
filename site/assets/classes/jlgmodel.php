<?php
/**
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die;

jimport( 'joomla.application.component.model');

class JLGModel extends JModel
{
	/**
	 * Overrides method to try to load model from extension if it exists
	 */
	public static function &getInstance( $type, $prefix = '', $config = array() )
	{
		$extensions = JoomleagueHelper::getExtensions(JRequest::getInt('p'));

		foreach ($extensions as $e => $extension) {
			$modelType = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
			$modelClass	= $prefix.ucfirst($modelType).ucfirst($extension);
			$result		= false;

			if (!class_exists( $modelClass ))
			{
				jimport('joomla.filesystem.path');
				$path = JPath::find(
						JModel::addIncludePath(),
						JModel::_createFileName( 'model', array( 'name' => $modelType))
				);
				if ($path)
				{
					require_once $path;

					if (class_exists( $modelClass ))
					{
						$result = new $modelClass($config);
						return $result;
					}
				}
			}
			else {
				$result = new $modelClass($config);
				return $result;
			}
		}
		$instance = parent::getInstance($type, $prefix, $config);
		return $instance;
	}
}