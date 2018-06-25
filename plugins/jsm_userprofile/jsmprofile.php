<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('JPATH_BASE') or die;
 
/**
 * plgUserjsmprofile
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class plgUserjsmprofile extends JPlugin
{
    /**
     * plgUserjsmprofile::onContentPrepareData()
     * 
     * @param mixed $context
     * @param mixed $data
     * @return
     */
    function onContentPrepareData($context, $data)
    {
        $app = JFactory::getApplication();
        // Check we are manipulating a valid form.
        //if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile')))
        if (!in_array($context, array('com_users.user','com_admin.profile')))
        {
            return true;
        }
 
        $userId = isset($data->id) ? $data->id : 0;
 
        // Load the profile data from the database.
        $db = JFactory::getDbo();
        $db->setQuery(
            'SELECT profile_key, profile_value FROM #__user_profiles' .
            ' WHERE user_id = '.(int) $userId .
            ' AND profile_key LIKE \'jsmprofile.%\'' .
            ' ORDER BY ordering'
        );
        $results = $db->loadRowList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' results<br><pre>'.print_r($results,true).'</pre>'),'');
        /*
Array
(
    [0] => Array
        (
            [0] => jsmprofile.databaseaccess
            [1] => 0
        )

)
        */
 
        // Check for a database error.
        if ($db->getErrorNum()) {
            $this->_subject->setError($db->getErrorMsg());
            return false;
        }
 
        // Merge the profile data.
        $data->jsmprofile = array();
        foreach ($results as $v) {
            $k = str_replace('jsmprofile.', '', $v[0]);
            $data->jsmprofile[$k] = $v[1];
        }
 
        return true;
    }
 
    /**
     * plgUserjsmprofile::onContentPrepareForm()
     * 
     * @param mixed $form
     * @param mixed $data
     * @return
     */
    function onContentPrepareForm($form, $data)
    {
        $app = JFactory::getApplication();
        // Load user_profile plugin language
        $lang = JFactory::getLanguage();
        $lang->load('plg_user_jsmprofile', JPATH_ADMINISTRATOR);
 
        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }
        // Check we are manipulating a valid form.
        //if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile'))) {
            if (!in_array($form->getName(), array('com_users.user','com_admin.profile'))) {
            return true;
        }

        // Add the profile fields to the form.
        JForm::addFormPath(dirname(__FILE__).'/profiles');
        $form->loadFile('profile', false);
    }
 
    /**
     * plgUserjsmprofile::onUserAfterSave()
     * 
     * @param mixed $data
     * @param mixed $isNew
     * @param mixed $result
     * @param mixed $error
     * @return
     */
    function onUserAfterSave($data, $isNew, $result, $error)
    {
        $app = JFactory::getApplication();
        $userId    = JArrayHelper::getValue($data, 'id', 0, 'int');
 
        if ($userId && $result && isset($data['jsmprofile']) && (count($data['jsmprofile'])))
        {
            try
            {
                $db = JFactory::getDbo();
                $db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$userId.' AND profile_key LIKE \'jsmprofile.%\'');
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
 
                $tuples = array();
                $order    = 1;
                foreach ($data['jsmprofile'] as $k => $v) {
                    $tuples[] = '('.$userId.', '.$db->quote('jsmprofile.'.$k).', '.$db->quote($v).', '.$order++.')';
                }
 
                $db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
            }
            catch (JException $e) {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }
 
        return true;
    }
 
    /**
     * plgUserjsmprofile::onUserAfterDelete()
     * 
     * @param mixed $user
     * @param mixed $success
     * @param mixed $msg
     * @return
     */
    function onUserAfterDelete($user, $success, $msg)
    {
        $app = JFactory::getApplication();
        if (!$success) {
            return false;
        }
 
        $userId    = JArrayHelper::getValue($user, 'id', 0, 'int');
 
        if ($userId)
        {
            try
            {
                $db = JFactory::getDbo();
                $db->setQuery(
                    'DELETE FROM #__user_profiles WHERE user_id = '.$userId .
                    " AND profile_key LIKE 'jsmprofile.%'"
                );
 
                if (!$db->query()) {
                    throw new Exception($db->getErrorMsg());
                }
            }
            catch (JException $e)
            {
                $this->_subject->setError($e->getMessage());
                return false;
            }
        }
 
        return true;
    }
  
}
?>