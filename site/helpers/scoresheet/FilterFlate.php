<?php
/**
 * FPDM Flate stream filter.
 *
 * @version    2.10
 * @author     Olivier Plathey
 * @copyright  Copyright (C) Olivier Plathey
 * @license    FPDF License
 */
\defined('_JEXEC') or die;

//
//  FPDM - Filter Flate
//  NOTE: requires ZLIB >= 1.0.9!
//

// Preserve the legacy alias check while avoiding obsolete PHP 4/5 branches.
if (!class_exists('FilterFlateDecode', false) && !class_exists('FilterFlate', false))
{


	if (isset($FPDM_FILTERS)) array_push($FPDM_FILTERS, "FlateDecode");

	class FilterFlate
	{

		var $data = null;
		var $dataLength = 0;

		/**
		 * Stop PDF stream processing after a Flate stream error.
		 * Preserves the legacy filter's terminal error handling for invalid PDF content.
		 * @param string $msg Human-readable decompression error message.
		 * @return never Terminates execution instead of continuing with bad data.
		 */
		function error($msg)
		{
			die($msg);
		}

		/**
		 * Decompress a zlib/Flate-encoded PDF stream.
		 *
		 * Delegates to gzuncompress(), treating only the boolean false result
		 * as an error; valid compressed empty streams must decode to an empty
		 * string rather than triggering a fatal PDF error on PHP 8.
		 *
		 * @param string $data Binary Flate-compressed stream data.
		 * @return string Decompressed bytes, including a valid empty string.
		 */
		function decode($data)
		{

			$this->data       = $data;
			$this->dataLength = strlen($data);

			// uncompress
			$data = gzuncompress($data);

			if ($data === false) $this->error("FilterFlateDecode: invalid stream data.");

			return $data;
		}


		/**
		 * Compress raw PDF stream bytes using the zlib Flate format.
		 * Uses level-nine compression and returns the compressed binary stream.
		 * @param string $in Uncompressed PDF stream data.
		 * @return string|false Compressed stream, or false on a zlib failure.
		 */
		function encode($in)
		{
			return gzcompress($in, 9);
		}
	}

}
//unset $__tmp;
?>
