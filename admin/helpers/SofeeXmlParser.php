<?php
/*

  +----------------------------------------------------------------------+
  | Sofee Framework For PHP4                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 2004-2005 The Sofee Development Team                   |
  +----------------------------------------------------------------------+
  | This source file is subject to the GNU Lesser Public License (LGPL), |
  | that is bundled with this package in the file LICENSE, and is        |
  | available at through the world-wide-web at                           |
  | http://www.fsf.org/copyleft/lesser.html                              |
  | If you did not receive a copy of the LGPL and are unable to          |
  | obtain it through the world-wide-web, you can get it by writing the  |
  | Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, |
  | MA 02111-1307, USA.                                                  |
  +----------------------------------------------------------------------+
  | Author: Justin Wu <ezdevelop@gmail.com>                              |
  +----------------------------------------------------------------------+
*/

// $Id: SofeeXmlParser.php,v 1.3 2005/05/30 06:30:14 wenlong Exp $


/**
 * Sofee XML Parser class - This is an XML parser based on PHP's "xml" extension.
 *
 * The SofeeXmlParser class provides a very simple and easily usable toolset to convert XML
 * to an array that can be processed with array iterators.
 *
 * @package   SofeeFramework
 * @access    public
 * @version   $Revision: 1.1 $
 * @author    Justin Wu <wenlong@php.net>
 * @homepage  http://www.sofee.cn
 * @copyright Copyright (c) 2004-2005 Sofee Development Team.(http://www.sofee.cn)
 * @since     2005-05-30
 * @see       PEAR:XML_Parser | SimpleXML extension
 */

defined('_JEXEC') or die('Restricted access');

class SofeeXmlParser
{
	/**
	 * XML parser handle
	 *
	 * @var resource
	 * @see xml_parser_create()
	 */
	var $parser;

	/**
	 * source encoding
	 *
	 * @var string
	 */
	var $srcenc;

	/**
	 * target encoding
	 *
	 * @var string
	 */
	var $dstenc;

	/**
	 * the original struct
	 *
	 * @access private
	 * @var    array1
	 */
	var $_struct = array();

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param   mixed        [ $srcenc] source encoding
	 * @param   mixed        [ $dstenc] target encoding
	 *
	 * @return void
	 * @since
	 */
	function SofeeXmlParser($srcenc = null, $dstenc = null)
	{
		$this->srcenc = $srcenc;
		$this->dstenc = $dstenc;

		// Initialize the variable.
		$this->parser  = null;
		$this->_struct = array();
	}

	/**
	 * Parses the XML file
	 *
	 * @access public
	 *
	 * @param   string        [ $file] the XML file name
	 *
	 * @return void
	 * @since
	 */
	function parseFile($file)
	{
		$data = @file_get_contents($file) or die("Can't open file $file for reading!");
		$this->parseString($data);
	}

	/**
	 * Parses a string.
	 *
	 * @access public
	 *
	 * @param   string        [ $data] XML data
	 *
	 * @return void
	 */
	function parseString($data)
	{
		if ($this->srcenc === null)
		{
			$this->parser = @xml_parser_create() or die('Unable to create XML parser resource.');
		}
		else
		{
			$this->parser = @xml_parser_create($this->srcenc) or die('Unable to create XML parser resource with ' . $this->srcenc . ' encoding.');
		}

		if ($this->dstenc !== null)
		{
			@xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->dstenc) or die('Invalid target encoding');
		}

		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);    // Lowercase tags
		xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);        // skip empty tags

		if (!xml_parse_into_struct($this->parser, $data, $this->_struct))
		{
			printf(
				"XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->parser)),
				xml_get_current_line_number($this->parser)
			);

			$this->free();
			exit();
		}

		$this->_count = count($this->_struct);
		$this->free();
	}

	/**
	 * Free the resources
	 *
	 * @access public
	 * @return void
	 **/
	function free()
	{
		if (isset($this->parser) && is_resource($this->parser))
		{
			xml_parser_free($this->parser);
			unset($this->parser);
		}
	}

	/**
	 * return the data struction
	 *
	 * @access public
	 * @return array
	 */
	function getTree()
	{
		$i    = 0;
		$tree = array();

		$tree = $this->addNode(
			$tree,
			$this->_struct[$i]['tag'],
			(isset($this->_struct[$i]['value'])) ? $this->_struct[$i]['value'] : '',
			(isset($this->_struct[$i]['attributes'])) ? $this->_struct[$i]['attributes'] : '',
			$this->getChild($i)
		);

		unset($this->_struct);

		return ($tree);
	}

	/**
	 * Appends some values to an array
	 *
	 * @access public
	 *
	 * @param   array        [  $target]
	 * @param   string        [ $key]
	 * @param   string        [ $value]
	 * @param   array        [  $attributes]
	 * @param   array        [  $child]      the children
	 *
	 * @return void
	 * @since
	 */
	function addNode($target, $key, $value = '', $attributes = '', $child = '')
	{
		if (!isset($target[$key]['value']) && !isset($target[$key][0]))
		{
			if ($child != '')
			{
				$target[$key] = $child;
			}

			if ($attributes != '')
			{
				foreach ($attributes as $k => $v)
				{
					$target[$key][$k] = $v;
				}
			}

			$target[$key]['value'] = $value;
		}
		else
		{
			if (!isset($target[$key][0]))
			{
				// Is string or other
				$oldvalue        = $target[$key];
				$target[$key]    = array();
				$target[$key][0] = $oldvalue;
				$index           = 1;
			}
			else
			{
				// Is array
				$index = count($target[$key]);
			}

			if ($child != '')
			{
				$target[$key][$index] = $child;
			}

			if ($attributes != '')
			{
				foreach ($attributes as $k => $v)
				{
					$target[$key][$index][$k] = $v;
				}
			}

			$target[$key][$index]['value'] = $value;
		}

		return $target;
	}

	/**
	 * recursion the children node data
	 *
	 * @access public
	 *
	 * @param   integer        [ $i] the last struct index
	 *
	 * @return array
	 */
	function getChild(&$i)
	{
		// Contain node data
		$children = array();

		// Loop
		while (++$i < $this->_count)
		{
			// Node tag name
			$tagname    = $this->_struct[$i]['tag'];
			$value      = isset($this->_struct[$i]['value']) ? $this->_struct[$i]['value'] : '';
			$attributes = isset($this->_struct[$i]['attributes']) ? $this->_struct[$i]['attributes'] : '';

			switch ($this->_struct[$i]['type'])
			{
				case 'open':
					// Node has more children
					$child = $this->getChild($i);

					// Append the children data to the current node
					$children = $this->addNode($children, $tagname, $value, $attributes, $child);
					break;
				case 'complete':
					// At end of current branch
					$children = $this->addNode($children, $tagname, $value, $attributes);
					break;
				case 'cdata':
					// Node has CDATA after one of it's children
					$children['value'] .= $value;
					break;
				case 'close':
					// End of node, return collected data
					return $children;
					break;
			}
		}

		// Return $children;
	}

}
