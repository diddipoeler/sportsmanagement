<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

/**
 * Script file of the jQuery Easy plugin
 */
class plgsystemjqueryeasyInstallerScript
{		
	/**
	 * Called before an install/update/uninstall method
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent) {
		
	}
	
	/**
	 * Called after an install/update/uninstall method
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent) 
	{	
		echo '<dl>';
		echo '    <dt>Change log</dt>';
		echo '    <dd>FIXED: remaining scripts and stylesheets are not always removed</dd>';
		echo '    <dd>FIXED: minor report issues</dd>';
		echo '    <dd>MODIFIED: message field to handle labels</dd>';
		echo '    <dd>MODIFIED: moved \'disable captions\' under the \'MooTools\' section</dd>';
		echo '    <dd>MODIFIED: moved non-jquery advanced parameters out of the \'jQuery\' section</dd>';
		echo '    <dd>MODIFIED: some labels (Compression...)</dd>';
		echo '    <dd>MODIFIED: help page</dd>';
		echo '    <dd>ADDED: en-US language</dd>';
		echo '    <dd>ADDED: link to jQuery Easy Profiles page</dd>';
		echo '    <dd>ADDED: jQuery versions 2.1 and 1.11</dd>';
		echo '</dl>';
		
		return true;
	}
	
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent) {
		
	}
	
	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent) {
		
	}
	
	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall($parent) {
		
	}
}
?>
