<?php
/**
 * @version        $Id: helper.php 16385 2010-04-23 10:44:15Z ian $
 * @package        Joomla
 * @subpackage     Installation
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\User\UserHelper;
use Joomla\Data\DataObject;
use Joomla\CMS\Filesystem\Path;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * @package        Joomla
 * @subpackage     Installation
 */
class JInstallationHelper
{


	/**
	 * @param   array
	 *
	 * @return string
	 */
	function errors2string(&$errors)
	{
		$buffer = '';
		foreach ($errors as $error)
		{
			$buffer .= 'SQL=' . $error['msg'] . ":\n- - - - - - - - - -\n" . $error['sql'] . "\n= = = = = = = = = =\n\n";
		}

		return $buffer;
	}

	/**
	 * Creates a new database
	 *
	 * @param   object Database connector
	 * @param   string Database name
	 * @param   boolean utf-8 support
	 * @param   string Selected collation
	 *
	 * @return boolean success
	 */
	function createDatabase(&$db, $DBname, $DButfSupport)
	{
		if ($DButfSupport)
		{
			$sql = "CREATE DATABASE `$DBname` CHARACTER SET `utf8`";
		}
		else
		{
			$sql = "CREATE DATABASE `$DBname`";
		}

		$db->setQuery($sql);
		$db->execute();
		$result = $db->getErrorNum();

		if ($result != 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * Sets character set of the database to utf-8 with selected collation
	 * Used in instances of pre-existing database
	 *
	 * @param   object Database object
	 * @param   string Database name
	 * @param   string Selected collation
	 *
	 * @return boolean success
	 */
	function setDBCharset(&$db, $DBname)
	{
		if ($db->hasUTF())
		{
			$sql = "ALTER DATABASE `$DBname` CHARACTER SET `utf8`";
			$db->setQuery($sql);
			$db->execute();
			$result = $db->getErrorNum();
			if ($result != 0)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Backs up existing tables
	 *
	 * @param   object Database connector
	 * @param   array An array of errors encountered
	 */
	function backupDatabase(&$db, $DBname, $DBPrefix, &$errors)
	{
		// Initialize backup prefix variable
		// TODO: Should this be user-defined?
		$BUPrefix = 'bak_';

		$query = "SHOW TABLES FROM `$DBname`";
		$db->setQuery($query);
		$errors = array();
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				if (strpos($table, $DBPrefix) === 0)
				{
					$butable = str_replace($DBPrefix, $BUPrefix, $table);
					$query   = "DROP TABLE IF EXISTS `$butable`";
					$db->setQuery($query);
					$db->execute();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
					$query = "RENAME TABLE `$table` TO `$butable`";
					$db->setQuery($query);
					$db->execute();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}

	/**
	 * Deletes all database tables
	 *
	 * @param   object Database connector
	 * @param   array An array of errors encountered
	 */
	function deleteDatabase(&$db, $DBname, $DBPrefix, &$errors)
	{
		$query = "SHOW TABLES FROM `$DBname`";
		$db->setQuery($query);
		$errors = array();
		if ($tables = $db->loadResultArray())
		{
			foreach ($tables as $table)
			{
				if (strpos($table, $DBPrefix) === 0)
				{
					$query = "DROP TABLE IF EXISTS `$table`";
					$db->setQuery($query);
					$db->execute();
					if ($db->getErrorNum())
					{
						$errors[$db->getQuery()] = $db->getErrorMsg();
					}
				}
			}
		}

		return count($errors);
	}

	/**
	 * Calculates the file/dir permissions mask
	 */
	function getFilePerms($input, $type = 'file')
	{
		$perms = '';
		if (ArrayHelper::getValue($input, $type . 'PermsMode', 0))
		{
			$action = ($type == 'dir') ? 'Search' : 'Execute';
			$perms  = '0' . (ArrayHelper::getValue($input, $type . 'PermsUserRead', 0) * 4 + ArrayHelper::getValue($input, $type . 'PermsUserWrite', 0) * 2 + ArrayHelper::getValue($input, $type . 'PermsUser' . $action, 0)) . (ArrayHelper::getValue($input, $type . 'PermsGroupRead', 0) * 4 + ArrayHelper::getValue($input, $type . 'PermsGroupWrite', 0) * 2 + ArrayHelper::getValue($input, $type . 'PermsGroup' . $action, 0)) . (ArrayHelper::getValue($input, $type . 'PermsWorldRead', 0) * 4 + ArrayHelper::getValue($input, $type . 'PermsWorldWrite', 0) * 2 + ArrayHelper::getValue($input, $type . 'PermsWorld' . $action, 0));
		}

		return $perms;
	}

	/**
	 * Creates the admin user
	 */
	function createAdminUser(&$vars)
	{
		$DBtype     = ArrayHelper::getValue($vars, 'DBtype', 'mysql');
		$DBhostname = ArrayHelper::getValue($vars, 'DBhostname', '');
		$DBuserName = ArrayHelper::getValue($vars, 'DBuserName', '');
		$DBpassword = ArrayHelper::getValue($vars, 'DBpassword', '');
		$DBname     = ArrayHelper::getValue($vars, 'DBname', '');
		$DBPrefix   = ArrayHelper::getValue($vars, 'DBPrefix', '');

		$adminPassword = ArrayHelper::getValue($vars, 'adminPassword', '');
		$adminEmail    = ArrayHelper::getValue($vars, 'adminEmail', '');

		jimport('joomla.user.helper');

		// Create random salt/password for the admin user
		$salt      = UserHelper::genRandomPassword(32);
		$crypt     = UserHelper::getCryptedPassword($adminPassword, $salt);
		$cryptpass = $crypt . ':' . $salt;

		$vars['adminLogin'] = 'admin';

		$db = &JInstallationHelper::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, $DBname, $DBPrefix);

		// create the admin user
		$installdate = date('Y-m-d H:i:s');
		$nullDate    = $db->getNullDate();
		$query       = "INSERT INTO #__users VALUES (62, 'Administrator', 'admin', " . $db->Quote($adminEmail) . ", " . $db->Quote($cryptpass) . ", 'Super Administrator', 0, 1, 25, '$installdate', '$nullDate', '', '')";
		$db->setQuery($query);
		if (!$db->execute())
		{
			// is there already and existing admin in migrated data
			if ($db->getErrorNum() == 1062)
			{
				$vars['adminLogin']    = Text::_('Admin login in migrated content was kept');
				$vars['adminPassword'] = Text::_('Admin password in migrated content was kept');

				return;
			}
			else
			{
				echo $db->getErrorMsg();

				return;
			}
		}

		// add the ARO (Access Request Object)
		$query = "INSERT INTO #__core_acl_aro VALUES (10,'users','62',0,'Administrator',0)";
		$db->setQuery($query);
		if (!$db->execute())
		{
			echo $db->getErrorMsg();

			return;
		}

		// add the map between the ARO and the Group
		$query = "INSERT INTO #__core_acl_groups_aro_map VALUES (25,'',10)";
		$db->setQuery($query);
		if (!$db->execute())
		{
			echo $db->getErrorMsg();

			return;
		}
	}

	function & getDBO($driver, $host, $user, $password, $database, $prefix, $select = true)
	{
		static $db;

		if (!$db)
		{
			jimport('joomla.database.database');
			$options = array('driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix, 'select' => $select);
			$db      = &JDatabase::getInstance($options);
		}

		return $db;
	}

	/**
	 * Check the webserver user permissions for writing files/folders
	 *
	 * @static
	 * @return boolean    True if correct permissions exist
	 * @since  1.5
	 */
	function fsPermissionsCheck()
	{
		if (!is_writable(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp'))
		{
			return false;
		}
		if (!mkdir(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test', 0755))
		{
			return false;
		}
		if (!copy(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'index.html', JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'index.html'))
		{
			return false;
		}
		if (!chmod(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'index.html', 0777))
		{
			return false;
		}
		if (!unlink(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'index.html'))
		{
			return false;
		}
		if (!rmdir(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'test'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Find the ftp filesystem root for a given user/pass pair
	 *
	 * @static
	 *
	 * @param   string  $user  Username of the ftp user to determine root for
	 * @param   string  $pass  Password of the ftp user to determine root for
	 *
	 * @return string    Filesystem root for given FTP user
	 * @since  1.5
	 */
	function findFtpRoot($user, $pass, $host = '127.0.0.1', $port = '21')
	{

		$ftpPaths = array();

		// Connect and login to the FTP server (using binary transfer mode to be able to compare files)
		$ftp = FtpClient::getInstance($host, $port, array('type' => FTP_BINARY));
		if (!$ftp->isConnected())
		{
			return Log::add('NOCONNECT', Log::ERROR, 'jsmerror');
		}
		if (!$ftp->login($user, $pass))
		{
			return Log::add('NOLOGIN', Log::ERROR, 'jsmerror');
		}

		// Get the FTP CWD, in case it is not the FTP root
		$cwd = $ftp->pwd();
		if ($cwd === false)
		{
			return Log::add('NOPWD', Log::ERROR, 'jsmerror');
		}
		$cwd = rtrim($cwd, '/');

		// Get list of folders in the CWD
		$ftpFolders = $ftp->listDetails(null, 'folders');
		if ($ftpFolders === false || count($ftpFolders) == 0)
		{
			return Log::add('NODIRECTORYLISTING', Log::ERROR, 'jsmerror');
		}
		for ($i = 0, $n = count($ftpFolders); $i < $n; $i++)
		{
			$ftpFolders[$i] = $ftpFolders[$i]['name'];
		}

		// Check if Joomla! is installed at the FTP CWD
		$dirList = array('administrator', 'components', 'installation', 'language', 'libraries', 'plugins');
		if (count(array_diff($dirList, $ftpFolders)) == 0)
		{
			$ftpPaths[] = $cwd . '/';
		}

		// Process the list: cycle through all parts of JPATH_SITE, beginning from the end
		$parts   = explode(DS, JPATH_SITE);
		$tmpPath = '';
		for ($i = count($parts) - 1; $i >= 0; $i--)
		{
			$tmpPath = '/' . $parts[$i] . $tmpPath;
			if (in_array($parts[$i], $ftpFolders))
			{
				$ftpPaths[] = $cwd . $tmpPath;
			}
		}

		// Check all possible paths for the real Joomla! installation
		$checkValue = file_get_contents(JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'version.php');
		foreach ($ftpPaths as $tmpPath)
		{
			$filePath = rtrim($tmpPath, '/') . '/libraries/joomla/version.php';
			$buffer   = null;
			@$ftp->read($filePath, $buffer);
			if ($buffer == $checkValue)
			{
				$ftpPath = $tmpPath;
				break;
			}
		}

		// Close the FTP connection
		$ftp->quit();

		// Return the FTP root path
		if (isset($ftpPath))
		{
			return $ftpPath;
		}
		else
		{
			return Log::add('Unable to autodetect the FTP root folder', Log::ERROR, 'jsmerror');
		}
	}

	/**
	 * Verify the FTP configuration values are valid
	 *
	 * @static
	 *
	 * @param   string  $user  Username of the ftp user to determine root for
	 * @param   string  $pass  Password of the ftp user to determine root for
	 *
	 * @return mixed    Boolean true on success or JError object on fail
	 * @since  1.5
	 */
	function FTPVerify($user, $pass, $root, $host = '127.0.0.1', $port = '21')
	{
		$ftp = FtpClient::getInstance($host, $port);

		// Since the root path will be trimmed when it gets saved to configuration.php, we want to test with the same value as well
		$root = rtrim($root, '/');

		// Verify connection
		if (!$ftp->isConnected())
		{
			return Log::add('NOCONNECT', Log::ERROR, 'jsmerror');
		}

		// Verify username and password
		if (!$ftp->login($user, $pass))
		{
			return Log::add('NOLOGIN', Log::ERROR, 'jsmerror');
		}

		// Verify PWD function
		if ($ftp->pwd() === false)
		{
			return Log::add('NOPWD', Log::ERROR, 'jsmerror');
		}

		// Verify root path exists
		if (!$ftp->chdir($root))
		{
			return Log::add('NOROOT', Log::ERROR, 'jsmerror');
		}

		// Verify NLST function
		if (($rootList = $ftp->listNames()) === false)
		{
			return Log::add('NONLST', Log::ERROR, 'jsmerror');
		}

		// Verify LIST function
		if ($ftp->listDetails() === false)
		{
			return Log::add('NOLIST', Log::ERROR, 'jsmerror');
		}

		// Verify SYST function
		if ($ftp->syst() === false)
		{
			return Log::add('NOSYST', Log::ERROR, 'jsmerror');
		}

		// Verify valid root path, part one
		$checkList = array('CHANGELOG.php', 'COPYRIGHT.php', 'index.php', 'INSTALL.php', 'LICENSE.php');
		if (count(array_diff($checkList, $rootList)))
		{
			return Log::add('INVALIDROOT', Log::ERROR, 'jsmerror');
		}

		// Verify RETR function
		$buffer = null;
		if ($ftp->read($root . '/libraries/joomla/version.php', $buffer) === false)
		{
			return Log::add('NORETR', Log::ERROR, 'jsmerror');
		}

		// Verify valid root path, part two
		$checkValue = file_get_contents(JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'version.php');
		if ($buffer !== $checkValue)
		{
			return Log::add('INVALIDROOT', Log::ERROR, 'jsmerror');
		}

		// Verify STOR function
		if ($ftp->create($root . '/ftp_testfile') === false)
		{
			return Log::add('NOSTOR', Log::ERROR, 'jsmerror');
		}

		// Verify DELE function
		if ($ftp->delete($root . '/ftp_testfile') === false)
		{
			return Log::add('NODELE', Log::ERROR, 'jsmerror');
		}

		// Verify MKD function
		if ($ftp->mkdir($root . '/ftp_testdir') === false)
		{
			return Log::add('NOMKD', Log::ERROR, 'jsmerror');
		}

		// Verify RMD function
		if ($ftp->delete($root . '/ftp_testdir') === false)
		{
			return Log::add('NORMD', Log::ERROR, 'jsmerror');
		}

		$ftp->quit();

		return true;
	}

	/**
	 * Set default folder permissions
	 *
	 * @param   string  $path    The full file path
	 * @param   string  $buffer  The buffer to write
	 *
	 * @return boolean True on success
	 * @since  1.5
	 */
	function setDirPerms($dir, &$srv)
	{
		jimport('joomla.filesystem.path');

		/*
		 * Initialize variables
		 */
		$ftpFlag = false;
		$ftpRoot = $srv['ftpRoot'];

		/*
		 * First we need to determine if the path is chmodable
		 */
		if (!Path::canChmod(Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $dir)))
		{
			$ftpFlag = true;
		}

		// Do NOT use ftp if it is not enabled
		if (!$srv['ftpEnable'])
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			$ftp = FtpClient::getInstance($srv['ftpHost'], $srv['ftpPort']);
			$ftp->login($srv['ftpUser'], $srv['ftpPassword']);

			//Translate path for the FTP account
			$path = Path::clean($ftpRoot . "/" . $dir);

			/*
			 * chmod using ftp
			 */
			if (!$ftp->chmod($path, '0755'))
			{
				$ret = false;
			}

			$ftp->quit();
			$ret = true;
		}
		else
		{

			$path = Path::clean(JPATH_SITE . DIRECTORY_SEPARATOR . $dir);

			if (!@ chmod($path, octdec('0755')))
			{
				$ret = false;
			}
			else
			{
				$ret = true;
			}
		}

		return $ret;
	}

	function findMigration(&$args)
	{
		jexit();
	}

	/**
	 * Uploads a sql script and executes it. Script can be text file or zip/gz packed
	 *
	 * @static
	 *
	 * @param   array The installation variables
	 * @param   boolean true if the script is a migration script
	 *
	 * @return string Success or error messages
	 * @since  1.5
	 */
	function uploadSql(&$args, $migration = false, $preconverted = false)
	{
		global $app;
		$archive = '';
		$script  = '';

		/*
		 * Check for iconv
		 */
		if ($migration && !$preconverted && !function_exists('iconv'))
		{
			return Text::_('WARNICONV');
		}


		/*
		 * Get the uploaded file information
		 */
		if ($migration)
		{
			$sqlFile = Factory::getApplication()->input->getVar('migrationFile', '', 'files', 'array');
		}
		else
		{
			$sqlFile = Factory::getApplication()->input->getVar('sqlFile', '', 'files', 'array');
		}

		/*
		 * Make sure that file uploads are enabled in php
		 */
		if (!(bool) ini_get('file_uploads'))
		{
			return Text::_('WARNINSTALLFILE');
		}

		/*
		 * Make sure that zlib is loaded so that the package can be unpacked
		 */
		if (!extension_loaded('zlib'))
		{
			return Text::_('WARNINSTALLZLIB');
		}

		/*
		 * If there is no uploaded file, we have a problem...
		 */
		if (!is_array($sqlFile) || $sqlFile['size'] < 1)
		{
			return Text::_('WARNNOFILE');
		}

		/*
		 * Move uploaded file
		 */
		// Set permissions for tmp dir
		JInstallationHelper::_chmod(JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp', 0777);
		jimport('joomla.filesystem.file');
		$uploaded = File::upload($sqlFile['tmp_name'], JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $sqlFile['name']);
		if (!$uploaded)
		{
			return Text::_('WARNUPLOADFAILURE');
		}

		if (!preg_match('#\.sql$#i', $sqlFile['name']))
		{
			$archive = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $sqlFile['name'];
		}
		else
		{
			$script = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $sqlFile['name'];
		}

		// unpack archived sql files
		if ($archive)
		{
			$package = JInstallationHelper::unpack($archive, $args);
			if ($package === false)
			{
				return Text::_('WARNUNPACK');
			}
			$script = $package['folder'] . DIRECTORY_SEPARATOR . $package['script'];
		}

		$db = &JInstallationHelper::getDBO($args['DBtype'], $args['DBhostname'], $args['DBuserName'], $args['DBpassword'], $args['DBname'], $args['DBPrefix']);

		/**
		 * If migration perform manipulations on script file before population
		 */
		if ($migration)
		{
			$script = JInstallationHelper::preMigrate($script, $args, $db);
			if ($script == false)
			{
				return Text::_('Script operations failed');
			}
		}

		$errors = array();
		$msg    = '';
		$result = JInstallationHelper::populateDatabase($db, $script, $errors);

		/**
		 * If migration, perform post population manipulations (menu table construction)
		 */
		$migErrors = null;
		if ($migration)
		{
			$migResult = JInstallationHelper::postMigrate($db, $migErrors, $args);

			if ($migResult != 0)
			{
				/**
				 * Merge populate and migrate processing errors
				 */
				if ($result == 0)
				{
					$result = $migResult;
					$errors = $migErrors;
				}
				else
				{
					$result += $migResult;
					$errors = array_merge($errors, $migErrors);
				}
			}
		}


		/**
		 * prepare sql error messages if returned from populate and migrate
		 */
		if (!is_null($errors))
		{
			foreach ($errors as $error)
			{
				$msg .= stripslashes($error['msg']);
				$msg .= chr(13) . "-------------" . chr(13);
				$txt = '<textarea cols="40" rows="4" name="instDefault" readonly="readonly" >' . Text::_("Database Errors Reported") . chr(13) . $msg . '</textarea>';
			}
		}
		else
		{
			// consider other possible errors from populate
			$msg = $result == 0 ? Text::_('SQL script installed successfully') : Text::_('Error installing SQL script');
			$txt = '<input size="50" value="' . $msg . '" readonly="readonly" />';
		}

		/**
		 * Clean up
		 */
		if ($archive)
		{
			File::delete($archive);
			Folder::delete($package['folder']);
		}
		else
		{
			File::delete($script);
		}

		return $txt;
	}

	/**
	 * JInstallationHelper::_chmod()
	 * 
	 * @param mixed $path
	 * @param mixed $mode
	 * @return
	 */
	function _chmod($path, $mode)
	{
		global $app;
		$ret = false;

		// Initialize variables
		$ftpFlag = true;
		$ftpRoot = $app->getCfg('ftp_root');

		// Do NOT use ftp if it is not enabled
		if ($app->getCfg('ftp_enable') != 1)
		{
			$ftpFlag = false;
		}

		if ($ftpFlag == true)
		{
			// Connect the FTP client
			$ftp = FtpClient::getInstance($app->getCfg('ftp_host'), $app->getCfg('ftp_port'));
			$ftp->login($app->getCfg('ftp_user'), $app->getCfg('ftp_pass'));

			//Translate the destination path for the FTP account
			$path = Path::clean(str_replace(JPATH_SITE, $ftpRoot, $path), '/');

			// do the ftp chmod
			if (!$ftp->chmod($path, $mode))
			{
				// FTP connector throws an error
				return false;
			}
			$ftp->quit();
			$ret = true;
		}
		else
		{
			$ret = @ chmod($path, $mode);
		}

		return $ret;
	}

	/**
	 * Unpacks a compressed script file either as zip or gz/ Assumes single file in archive
	 *
	 * @static
	 *
	 * @param   string  $p_filename  The uploaded package filename or install directory
	 *
	 * @return unpacked filename on success, False on error
	 * @since  1.5
	 */
	function unpack($p_filename, &$vars)
	{

		/*
		 * Initialize variables
		 */
		// Path to the archive
		$archivename = $p_filename;
		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');


		// Clean the paths to use for archive extraction
		$extractdir  = Path::clean(dirname($p_filename) . DIRECTORY_SEPARATOR . $tmpdir);
		$archivename = Path::clean($archivename);
		jimport('joomla.filesystem.archive');
		$archive = new \Joomla\Archive\Archive;
		$result = $archive->extract($archivename, $extractdir);

		if ($result === false)
		{
			return false;
		}


		/*
		 * return the file found in the extract folder and also folder name
		 */
		if ($handle = opendir($extractdir))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..")
				{
					$script = $file;
					continue;
				}
			}
			closedir($handle);
		}
		$retval['script'] = $script;
		$retval['folder'] = $extractdir;

		return $retval;

	}

	/**
	 * Performs pre-populate conversions on a migration script
	 *
	 * @static
	 *
	 * @param   string  $scriptName  The uploaded / unpacked script file
	 *                               $param array $args The installation varibables
	 *
	 * @return converted filename on success, False on error
	 * @since  1.5
	 */
	function preMigrate($scriptName, &$args, $db)
	{
		$maxread = 0;
		jimport('joomla.filesystem.file');
		if (function_exists('memory_get_usage'))
		{
			$memlimit = JInstallationHelper::return_bytes(ini_get('memory_limit'));
			$maxread  = $memlimit / 16;     // Read only a eigth of our max amount of memory, we could be up to a lot by now
			// By default this pegs us at 0.5MB
		}
		$buffer    = '';
		$newPrefix = $args['DBPrefix'];
		/**
		 * search and replace table prefixes
		 */
		$oldPrefix   = trim($args['oldPrefix']);
		$oldPrefix   = rtrim($oldPrefix, '_') . '_';
		$srcEncoding = $args['srcEncoding'];
		if (!is_file($scriptName))
		{
			return false; // not a file?
		}
		$newFile   = dirname($scriptName) . DIRECTORY_SEPARATOR . 'converted.sql';
		$tfilesize = filesize($scriptName);
		if ($maxread > 0 && $tfilesize > 0 && $maxread < $tfilesize)
		{
			$parts = ceil($tfilesize / $maxread);
			file_put_contents($newFile, ''); // cleanse the file first
			for ($i = 0; $i < $parts; $i++)
			{
				$buffer = File::read($scriptName, false, $maxread, $maxread, ($i * $maxread));
				// Lets try and read a portion of the file
				JInstallationHelper::replaceBuffer($buffer, $oldPrefix, $newPrefix, $srcEncoding);
				JInstallationHelper::appendFile($buffer, $newFile);
				unset($buffer);
			}
			File::delete($scriptName);
		}
		else
		{
			/**
			 * read script file into buffer
			 */
			if (is_file($scriptName))
			{
				$buffer = file_get_contents($scriptName);
			}
			else
			{
				return false;
			}

			if ($buffer == false)
			{
				return false;
			}
			JInstallationHelper::replaceBuffer($buffer, $oldPrefix, $newPrefix, $srcEncoding);

			/**
			 * write to file
			 */
			//$newFile = dirname( $scriptName ).DIRECTORY_SEPARATOR.'converted.sql';
			$ret = file_put_contents($newFile, $buffer);
			unset($buffer); // Release the memory used by the buffer
			jimport('joomla.filesystem.file');
			File::delete($scriptName);
		}

		/**
		 * Create two empty temporary tables
		 */

		$query = 'DROP TABLE IF EXISTS ' . $newPrefix . 'modules_migration';
		$db->setQuery($query);
		$db->execute();

		$query = 'DROP TABLE IF EXISTS ' . $newPrefix . 'menu_migration';
		$db->setQuery($query);
		$db->execute();

		$query = 'CREATE TABLE ' . $newPrefix . 'modules_migration SELECT * FROM ' . $newPrefix . 'modules WHERE 0';
		$db->setQuery($query);
		$db->execute();

		$query = 'CREATE TABLE ' . $newPrefix . 'modules_migration_menu SELECT * FROM ' . $newPrefix . 'modules_menu WHERE 0';
		$db->setQuery($query);
		$db->execute();

		$query = 'CREATE TABLE ' . $newPrefix . 'menu_migration SELECT * FROM ' . $newPrefix . 'menu WHERE 0';
		$db->setQuery($query);
		$db->execute();

		return $newFile;
	}

	/**
	 * JInstallationHelper::return_bytes()
	 * 
	 * @param mixed $val
	 * @return
	 */
	function return_bytes($val)
	{
		$val  = trim($val);
		/*
		$last = strtolower($val{strlen($val) - 1});
		switch ($last)
		{
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
*/
		return $val;
	}

	/**
	 * JInstallationHelper::replaceBuffer()
	 * 
	 * @param mixed $buffer
	 * @param mixed $oldPrefix
	 * @param mixed $newPrefix
	 * @param mixed $srcEncoding
	 * @return void
	 */
	function replaceBuffer(&$buffer, $oldPrefix, $newPrefix, $srcEncoding)
	{

		$buffer = str_replace($oldPrefix, $newPrefix, $buffer);

		/*
		 * give temp name to menu and modules tables
		 */
		$buffer = str_replace($newPrefix . 'modules', $newPrefix . 'modules_migration', $buffer);
		$buffer = str_replace($newPrefix . 'menu', $newPrefix . 'menu_migration', $buffer);

		/*
		 * convert to utf-8
		 */
		if (function_exists('iconv'))
		{
			$buffer = iconv($srcEncoding, 'utf-8//TRANSLIT', $buffer);
		}
	}

	/**
	 * JInstallationHelper::appendFile()
	 * 
	 * @param mixed $buffer
	 * @param mixed $filename
	 * @return void
	 */
	function appendFile(&$buffer, $filename)
	{
		$fh = fopen($filename, 'a');
		fwrite($fh, $buffer);
		fclose($fh);
	}

	
	/**
	 * JInstallationHelper::populateDatabase()
	 * 
	 * @param mixed $db
	 * @param mixed $sqlfile
	 * @param mixed $errors
	 * @param string $nexttask
	 * @return
	 */
	static function populateDatabase(&$db, $sqlfile, $errors = array() , $nexttask = 'mainconfig')
	{
		//$return = true;
		$buffer = file_get_contents($sqlfile);
		/*
		if (!($buffer = file_get_contents($sqlfile)))
		{
			return false;
		}
*/
		$queries = JInstallationHelper::splitSql($buffer);

		foreach ($queries as $query)
		{
			$query = trim($query);
			// If the query isn't empty and is not a MySQL or PostgreSQL comment, execute it.
			//if (!empty($query) && ($query[0] != '#') && ($query[0] != '-'))
			if ($query != '' && $query[0] != '#')
			{
			 try{
				$db->setQuery($query);
				$db->execute();
                }
		catch (Exception $e)
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
		}

				//JInstallationHelper::getDBErrors($errors, $db);
			}
		}

		return true;
	}

	/**
	 * @param   string
	 *
	 * @return array
	 */
	static function splitSql($sql)
	{
		$sql       = trim($sql);
		$sql       = preg_replace("/\n\#[^\n]*/", '', "\n" . $sql);
		$buffer    = array();
		$ret       = array();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i++)
		{
			if ($sql[$i] == ";" && !$in_string)
			{
				$ret[] = substr($sql, 0, $i);
				$sql   = substr($sql, $i + 1);
				$i     = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}
			if (isset($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty($sql))
		{
			$ret[] = $sql;
		}

		return ($ret);
	}

	/**
	 * JInstallationHelper::getDBErrors()
	 * 
	 * @param mixed $errors
	 * @param mixed $db
	 * @return void
	 */
	static function getDBErrors(&$errors, $db)
	{
		if ($db->getErrorNum() > 0)
		{
			$errors[] = array('msg' => $db->getErrorMsg(), 'sql' => $db->_sql);
		}
	}

	/**
	 * Performs post-populate conversions after importing a migration script
	 * These include constructing an appropriate menu table for core content items
	 * and adding core modules from old site to the modules table
	 *
	 * @static
	 *
	 * @param   JDatabase
	 * @param   array errors (by ref)
	 *
	 * @return error count
	 * @since  1.5
	 */
	function postMigrate($db, &$errors, &$args)
	{

		$newPrefix = $args['DBPrefix'];

		/*
		 * Check to see if migration is from 4.5.1
		 */
		$query = 'SELECT id FROM ' . $newPrefix . 'users WHERE usertype = "superadministrator"';
		$db->setQuery($query);
		$rows = $db->loadRowList();
		JInstallationHelper::getDBErrors($errors, $db);

		/*
		 * if it is, then fill usertype field with correct values from aro_group
		 */
		if (count($rows) > 0)
		{
			$query = 'UPDATE ' . $newPrefix . 'users AS u, ' . $newPrefix . 'core_acl_aro_groups AS g' .
				' SET u.usertype = g.value' .
				' WHERE u.gid = g.id';
			$db->setQuery($query);
			$db->execute();
			JInstallationHelper::getDBErrors($errors, $db);
		}

		/*
		 * Construct the menu table based on old table references to core items
		 */
		// Component - change all
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `type` = "component" WHERE `type` = "components";';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// Component Item Link
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = SUBSTRING(link, 1, LOCATE("&Itemid=", link) -1), `type` = "component" WHERE `type` = "component_item_link";';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// get com_contact id
		$query = 'SELECT `id` FROM `' . $newPrefix . 'components` WHERE `option`="com_contact" AND `parent` = 0';
		$db->setQuery($query);
		JInstallationHelper::getDBErrors($errors, $db);
		$compId = $db->loadResult();

		// contact category table
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = INSERT(link, LOCATE("catid=", link), 0, "view=category&"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "contact_category_table"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// contact item link
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = INSERT(link, LOCATE("task=view", link), 20, "view=contact&id"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "contact_item_link"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// fix up standalone contact
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = "index.php?option=com_contact&view=category" WHERE `link` = "index.php?option=com_contact"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// get com_content id
		$query = 'SELECT `id` FROM `' . $newPrefix . 'components` WHERE `option`="com_content" AND `parent` = 0';
		$db->setQuery($query);

		$compId = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);

		// front page
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = "index.php?option=com_content&view=frontpage", `type` = "component", `componentid` = ' . $compId . ' WHERE `link` LIKE "%option=com_frontpage%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content archive category or section
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = "index.php?option=com_content&view=archive", `type` = "component", `componentid` = ' . $compId . ' WHERE (`type` = "content_archive_category" OR `type` = "content_archive_section")';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content blog category
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = INSERT(link, LOCATE("task=blogcat", link), 17, "view=category&layout=blog"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "content_blog_category"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content blog section
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("task=blogsec", link), 16, "view=section&layout=blog"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "content_blog_section";';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content category
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), LOCATE("&id=", link) - LOCATE("task=", link), "view=category"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "content_category"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content item link and typed content
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = INSERT(link, LOCATE("task=", link), 9, "view=article"), `type` = "component", `componentid` = ' . $compId . ' WHERE (`type` = "content_item_link" OR `type` = "content_typed")';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// content section
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET  `link` = INSERT(link, LOCATE("task=", link), 12, "view=section"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "content_section"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// get com_newsfeeds id
		$query = 'SELECT `id` FROM `' . $newPrefix . 'components` WHERE `option`="com_newsfeeds" AND `parent` = 0';
		$db->setQuery($query);
		$compId = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);


		// newsfeed categories
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = CONCAT(link, "&view=categories"), `componentid` = ' . $compId . ' WHERE `type` = "component" AND link LIKE "%option=com_newsfeeds%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// newsfeed category table
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("catid=", link), 5, "view=category&catid"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "newsfeed_category_table"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// newsfeed link
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), 9, "view=newsfeed"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "newsfeed_link"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// user checkin items
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("CheckIn", link), 7, "checkin") WHERE `type` = "url" AND link LIKE "%option=com_user&task=CheckIn%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// user edit details
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("UserDetails", link), 11, "edit") WHERE `type` = "url" AND link LIKE "%option=com_user&task=UserDetails%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// get com_weblinks id
		$query = 'SELECT `id` FROM `' . $newPrefix . 'components` WHERE `option`="com_weblinks" AND `parent` = 0';
		$db->setQuery($query);
		$compId = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);

		// weblinks categories
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = CONCAT(link, "&view=categories"), `componentid` = ' . $compId . ' WHERE `type` = "component" AND link LIKE "%option=com_weblinks%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// weblinks category table
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("catid=", link), 5, "view=category&catid"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "weblink_category_table"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// weblinks submit new item
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = INSERT(link, LOCATE("task=", link), 8, "view=weblink&layout=form") WHERE `type` = "url" AND link LIKE "%option=com_weblinks%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// get com_wrapper id
		$query = 'SELECT `id` FROM `' . $newPrefix . 'components` WHERE `option`="com_wrapper" AND `parent` = 0';
		$db->setQuery($query);
		JInstallationHelper::getDBErrors($errors, $db);
		$compId = $db->loadResult();

		// wrapper
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = CONCAT(link, "&view=wrapper"), `type` = "component", `componentid` = ' . $compId . ' WHERE `type` = "wrapper"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// set default to lowest ordering published on mainmenu
		$query = 'SELECT MIN( `ordering` ) FROM `' . $newPrefix . 'menu_migration` WHERE `published` = 1 AND `parent` = 0 AND `menutype` = "mainmenu"';
		$db->setQuery($query);
		$minorder = $db->loadResult();
		if (!$minorder)
		{
			$minorder = 0;
		}
		JInstallationHelper::getDBErrors($errors, $db);
		$query = 'SELECT `id` FROM `' . $newPrefix . 'menu_migration` WHERE `published` = 1 AND `parent` = 0 AND `menutype` = "mainmenu" AND `ordering` = ' . $minorder;
		$db->setQuery($query);
		$menuitemid = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);
		if (!$menuitemid)
		{
			$menuitemid = 1;
		}
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `home` = 1 WHERE `id` = ' . $menuitemid;
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// login and log out; component id and link update
		$query = 'SELECT id FROM `' . $newPrefix . 'components` WHERE link like "option=com_user"';
		$db->setQuery($query);
		$componentid = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET componentid = ' . $componentid . ' WHERE link = "index.php?option=com_login"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET link = "index.php?option=com_user&view=login" WHERE link = "index.php?option=com_login"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);


		// Search - Component ID Update
		$query = 'SELECT id FROM `' . $newPrefix . 'components` WHERE link like "option=com_search"';
		$db->setQuery($query);
		$componentid = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET componentid = ' . $componentid . ' WHERE link like "index.php?option=com_search%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		// tidy up urls with Itemids
		$query = 'UPDATE `' . $newPrefix . 'menu_migration` SET `link` = SUBSTRING(`link`,1,LOCATE("&Itemid",`link`)-1) WHERE `type` = "url" AND `link` LIKE "%&Itemid=%"';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);
		$query = 'SELECT DISTINCT `option` FROM ' . $newPrefix . 'components WHERE `option` != ""';
		$db->setQuery($query);
		$lookup = $db->loadResultArray();
		JInstallationHelper::getDBErrors($errors, $db);
		$lookup[] = 'com_user&';

		// prepare to copy across
		$query = 'SELECT * FROM ' . $newPrefix . 'menu_migration';
		$db->setQuery($query);
		$oldMenuItems = $db->loadObjectList();
		JInstallationHelper::getDBErrors($errors, $db);


		$query = 'DELETE FROM ' . $newPrefix . 'menu WHERE 1';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);
		$query = 'SELECT * FROM ' . $newPrefix . 'menu';
		$db->setQuery($query);

		$newMenuItems = $db->loadObjectList();
		JInstallationHelper::getDBErrors($errors, $db);

		// filter out links to 3pd components
		foreach ($oldMenuItems as $item)
		{
			if ($item->type == 'url' && !strpos($item->link, 'com_'))
			{
				$newMenuItems[] = $item;
			}
			else if ($item->type == 'url' && JInstallationHelper::isValidItem($item->link, $lookup))
			{
				$newMenuItems[] = $item;
			}
			else if ($item->type == 'component') //&& JInstallationHelper::isValidItem( $item->link, $lookup ))
			{
				// unpublish components that don't exist yet
				if (!JInstallationHelper::isValidItem($item->link, $lookup))
				{
					$item->published = 0;
				}
				$newMenuItems[] = $item;
			}
		}

		// build the menu table
		foreach ($newMenuItems as $item)
		{
			$db->insertObject($newPrefix . 'menu', $item);
			JInstallationHelper::getDBErrors($errors, $db);
		}

		// fix possible orphaned sub menu items
		$query = 'UPDATE  `' . $newPrefix . 'menu` AS c LEFT OUTER JOIN `' . $newPrefix . 'menu` AS p ON c.parent = p.id SET c.parent = 0 WHERE c.parent <> 0 AND p.id IS NULL';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		/*
		 * Construct the menu_type table base on new menu table types
		 */
		$query = 'SELECT DISTINCT `menutype` FROM ' . $newPrefix . 'menu WHERE 1';
		$db->setQuery($query);
		JInstallationHelper::getDBErrors($errors, $db);
		$menuTypes = $db->loadResultArray();
		$query     = 'TRUNCATE TABLE ' . $newPrefix . 'menu_types';
		$db->setQuery($query);

		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		foreach ($menuTypes as $mType)
		{
			$query = 'INSERT INTO ' . $newPrefix . 'menu_types ( menutype, title ) VALUES ("' . $mType . '", "' . $mType . '");';
			$db->setQuery($query);
			$db->execute();
			JInstallationHelper::getDBErrors($errors, $db);
		}

		/*
		 * Add core client modules from old site to modules table as unpublished
		 */
		$query = 'SELECT id FROM ' . $newPrefix . 'modules_migration WHERE client_id = 0 ';
		$db->setQuery($query);
		$lookup = $db->loadResultArray();
		JInstallationHelper::getDBErrors($errors, $db);

		$query = 'SELECT MAX(id) FROM ' . $newPrefix . 'modules ';
		$db->setQuery($query);
		$nextId = $db->loadResult();
		JInstallationHelper::getDBErrors($errors, $db);
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		foreach ($lookup as $module)
		{
			$qry = 'SELECT * FROM ' . $newPrefix . 'modules_migration WHERE id = "' . $module . '" AND client_id = 0';
			$db->setQuery($qry);
			if ($row = $db->loadObject())
			{
				if ($row->module == '')
				{
					$row->module = 'mod_custom';
				}
				if (Folder::exists(JPATH_SITE . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $row->module))
				{
					$nextId++;
					$oldid          = $row->id;
					$row->id        = $nextId;
					$row->published = 0;
					if ($db->insertObject($newPrefix . 'modules', $row))
					{
						// Grab the old modules menu links and put them in too!
						$qry = 'SELECT * FROM ' . $newPrefix . 'modules_migration_menu WHERE moduleid = ' . $oldid;
						$db->setQuery($qry);
						$entries = $db->loadObjectList();
						JInstallationHelper::getDBErrors($errors, $db);

						foreach ($entries as $entry)
						{
							$entry->moduleid = $nextId;
							$db->insertObject($newPrefix . 'modules_menu', $entry);
							JInstallationHelper::getDBErrors($errors, $db);
						}
					}
					else
					{
						JInstallationHelper::getDBErrors($errors, $db);
					}
				} // else the module doesn't exist?
			}
			else
			{
				JInstallationHelper::getDBErrors($errors, $db);
			}
		}

		// Put in breadcrumb module as per sample data
		$query = "INSERT INTO `" . $newPrefix . "modules` VALUES (0, 'Breadcrumbs', '', 1, 'breadcrumb', 0, '0000-00-00 00:00:00', 1, 'mod_breadcrumbs', 0, 0, 1, 'moduleclass_sfx=\ncache=0\nshowHome=1\nhomeText=Home\nshowComponent=1\nseparator=\n\n', 1, 0, '');";
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		/*
		 * Clean up
		 */

		$query = 'DROP TABLE IF EXISTS ' . $newPrefix . 'modules_migration';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		$query = 'DROP TABLE IF EXISTS ' . $newPrefix . 'modules_migration_menu';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		$query = 'DROP TABLE IF EXISTS ' . $newPrefix . 'menu_migration';
		$db->setQuery($query);
		$db->execute();
		JInstallationHelper::getDBErrors($errors, $db);

		return count($errors);
	}

	/**
	 * JInstallationHelper::isValidItem()
	 * 
	 * @param mixed $link
	 * @param mixed $lookup
	 * @return
	 */
	function isValidItem($link, $lookup)
	{
		foreach ($lookup as $component)
		{
			if (strpos($link, $component) != false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Inserts ftp variables to mainframe registry
	 * Needed to activate ftp layer for file operations in safe mode
	 *
	 * @param   array The post values
	 */
	function setFTPCfg($vars)
	{
		global $app;
		$arr               = array();
		$arr['ftp_enable'] = $vars['ftpEnable'];
		$arr['ftp_user']   = $vars['ftpUser'];
		$arr['ftp_pass']   = $vars['ftpPassword'];
		$arr['ftp_root']   = $vars['ftpRoot'];
		$arr['ftp_host']   = $vars['ftpHost'];
		$arr['ftp_port']   = $vars['ftpPort'];

		$app->setCfg($arr, 'config');
	}

	/**
	 *
	 * Borrowed from http://au.php.net/manual/en/ini.core.php comments
	 */
	function let_to_num($v)
	{
		//This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
		$l   = substr($v, -1);
		$ret = substr($v, 0, -1);
		switch (strtoupper($l))
		{
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
				break;
		}

		return $ret;
	}
}

?>
