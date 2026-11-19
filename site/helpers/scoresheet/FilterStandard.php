<?php
/**
 * FPDM raw stream compatibility filter.
 *
 * @version    2.10
 * @author     Olivier Plathey
 * @copyright  Copyright (C) Olivier Plathey
 * @license    FPDF License
 */
\defined('_JEXEC') or die;

//
//  FPDM - Filter Standard
//  NOTE: dummy filter for unfiltered streams!
//

if (isset($FPDM_FILTERS)) array_push($FPDM_FILTERS, "Standard");

class FilterStandard
{

	/**
	 * Return the original PDF bytes for a stream without a compression filter.
	 * The Standard filter is an identity transform: it must preserve binary input unchanged.
	 * @param string $data Original uncompressed PDF stream bytes.
	 * @return string The same bytes without modification.
	 */
	function decode($data)
	{
		return $data;
	}

	/**
	 * Pass through raw PDF stream bytes without introducing a compression layer.
	 * Allows callers to use the same filter interface for compressed and uncompressed content.
	 * @param string $data Raw PDF stream bytes.
	 * @return string The identical input stream.
	 */
	function encode($data)
	{
		return $data;
	}
}

?>
