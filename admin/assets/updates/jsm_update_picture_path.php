<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jsm_update_picture_path.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage updates
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

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

$this->jsmdb = sportsmanagementHelper::getDBConnection();
$this->jsmquery = $this->jsmdb->getQuery(true);
$this->jsmapp = Factory::getApplication();

$this->jsmquery = $this->jsmdb->getQuery(true);
$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'placeholders', 'persons') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in person.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in person.'), 'Notice');

/**
 * ##########################################################################################
 */
 
$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_big') . " = replace(logo_big, 'placeholders', 'clubs/large') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_big') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club big.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_middle') . " = replace(logo_middle, 'placeholders', 'clubs/medium') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_middle') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club middle.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_small') . " = replace(logo_small, 'placeholders', 'clubs/small') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_small') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club small.'), 'Notice');

/**
 * ##########################################################################################
 */
 
$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_big') . " = replace(logo_big, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_big') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club big.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_middle') . " = replace(logo_middle, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_middle') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club middle.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_small') . " = replace(logo_small, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_small') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club small.'), 'Notice');


/**
 * ##########################################################################################
 */


$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_big') . " = replace(logo_big, 'com_sportsmanagement/clubs/large', 'com_sportsmanagement/database/clubs/large') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_big') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_sportsmanagement/clubs/large' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club big.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_middle') . " = replace(logo_middle, 'com_sportsmanagement/clubs/medium', 'com_sportsmanagement/database/clubs/medium') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_middle') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_sportsmanagement/clubs/medium' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club middle.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('logo_small') . " = replace(logo_small, 'com_sportsmanagement/clubs/small', 'com_sportsmanagement/database/clubs/small') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('logo_small') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_sportsmanagement/clubs/small' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club small.'), 'Notice');


$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('trikot_home') . " = replace(trikot_home, 'placeholders', 'clubs/trikot') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('trikot_home') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club trikot home.'), 'Notice');  

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('trikot_away') . " = replace(trikot_away, 'placeholders', 'clubs/trikot') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('trikot_away') . ' LIKE ' . $this->jsmdb->Quote('%' . 'placeholders' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club trikot away.'), 'Notice');

$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('trikot_home') . " = replace(trikot_home, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('trikot_home') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club trikot home.'), 'Notice');


$this->jsmquery->clear();
/**
 * Fields to update.
 */
        $fields = array(
            $this->jsmdb->quoteName('trikot_away') . " = replace(trikot_away, 'media', 'images') "
        );
/**
 * Conditions for which records should be updated.
 */
        $conditions = array(
            $this->jsmdb->quoteName('trikot_away') . ' LIKE ' . $this->jsmdb->Quote('%' . 'media' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
$this->jsmdb->execute();
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . $this->jsmdb->getAffectedRows() . ' Datensätze aktualisiert in club trikot away.'), 'Notice');


















?>