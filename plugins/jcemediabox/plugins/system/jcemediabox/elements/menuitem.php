<?php
/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2012 Ryan Demmer. All rights reserved.
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die;

/**
 * Supports an HTML grouped select list of menu item grouped by menu
 */
class WFElementMenuItem extends WFElement {

    /**
     * Element name
     *
     * @access	protected
     * @var		string
     */
    var $_name = 'MenuItem';

    function fetchElement($name, $value, &$node, $control_name) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $menuType = $this->_parent->get('menu_type');
        $where = array();
        
        if (!empty($menuType)) {
            $where[] = 'menutype = ' . $db->Quote($menuType);
        } else {
            $where[] = '1';
        }

        // Load the list of menu types
        // TODO: move query to model
        $query->select('menutype, title')->from('#__menu_types')->order('title');
        $db->setQuery($query);
        $menuTypes = $db->loadObjectList();
        
        // get state if set
        $state = (string) $node->attributes()->state;

        // only get published menu items
        $where[] = 'published = 1';

        $query = $db->getQuery(true);
        // load the list of menu items
        // TODO: move query to model
        $query->select('id, parent_id, title, menutype, type')->from('#__menu')->where($where)->order('menutype, parent_id');

        $db->setQuery($query);
        $menuItems = $db->loadObjectList();

        // Establish the hierarchy of the menu
        // TODO: use node model
        $children = array();

        if ($menuItems) {
            // First pass - collect children
            foreach ($menuItems as $v) {
                $pt = $v->parent_id;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        // Second pass - get an indent list of the items
        $list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

        // Assemble into menutype groups
        $n = count($list);
        $groupedList = array();
        foreach ($list as $k => $v) {
            $groupedList[$v->menutype][] = &$list[$k];
        }

        // Assemble menu items to the array
        $options = array();
        $options[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_MENU_ITEM'));

        foreach ($menuTypes as $type) {
            if ($menuType == '') {
                //$options[] = JHtml::_('select.option', '0', '&#160;', 'value', 'text', true);
                $options[] = JHtml::_('select.option', $type->menutype, $type->title . ' - ' . JText::_('JGLOBAL_TOP'), 'value', 'text', true);
            }
            if (isset($groupedList[$type->menutype])) {
                $n = count($groupedList[$type->menutype]);
                for ($i = 0; $i < $n; $i++) {
                    $item = &$groupedList[$type->menutype][$i];

                    // If menutype is changed but item is not saved yet, use the new type in the list
                    if (JRequest::getString('option', '', 'get') == 'com_menus') {
                        $currentItemArray = JRequest::getVar('cid', array(0), '', 'array');
                        $currentItemId = (int) $currentItemArray[0];
                        $currentItemType = JRequest::getString('type', $item->type, 'get');
                        if ($currentItemId == $item->id && $currentItemType != $item->type) {
                            $item->type = $currentItemType;
                        }
                    }
                    
                    $disable = false;
                    
                    if ($item->type) {
                        $disable = strpos((string) $node->attributes()->disable, $item->type) !== false ? true : false;
                    }

                    $options[] = JHtml::_('select.option', $item->id, '&#160;&#160;&#160;' . $item->treename, 'value', 'text', $disable);
                }
            }
        }
        
        $id      = $control_name . $name;
        $name    = $control_name . '[' . $name . ']';
        
        $attribs = array('class="inputbox"');
        
        if ($multiple = (string) $node->attributes()->multiple) {                        
            
            $attribs[]   = 'multiple="multiple"';
            $attribs[]   = 'size="' . (int) $node->attributes()->size . '"';
            $name       .= '[]';
        }

        return JHtml::_(
            'select.genericlist', $options, $name, array('id' => $id, 'list.attr' => implode(' ', $attribs), 'list.select' => $value)
        );
    }

}