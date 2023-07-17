<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       transifex.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;

/**
 * sportsmanagementHelperTransifex
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementHelperTransifex
{
	private static $apiUrl = 'https://www.transifex.com/api/2/project/sportsmanagement';

	private static $languages = array();


	/**
	 * sportsmanagementHelperTransifex::updatelanguage()
	 *
	 * @param   mixed   $data
	 * @param   string  $folder
	 *
	 * @return
	 */
	public static function updatelanguage($data = null, $folder = 'de-DE')
	{
		if ($folder == 'de-DE' || $folder == 'en-GB')
		{
			Factory::getApplication()->enqueueMessage(Text::_('Admin Verzeichnis ' . $folder . ' ist vorhanden!'), 'Notice');

			return $data;
		}

		$adminpath = JPATH_ADMINISTRATOR . '/language/' . $folder;

		// Verzeichnis prüfen
		if (Folder::exists($adminpath))
		{
			Factory::getApplication()->enqueueMessage(Text::_('Admin Verzeichnis ' . $folder . ' ist vorhanden!'), 'Notice');
		}
		else
		{
			Folder::create($adminpath);
			Factory::getApplication()->enqueueMessage(Text::_('Admin Verzeichnis ' . $folder . ' wurde angelegt!'), 'Notice');
		}

		$sitepath = JPATH_ROOT . '/language/' . $folder;

		// Verzeichnis prüfen
		if (Folder::exists($sitepath))
		{
			Factory::getApplication()->enqueueMessage(Text::_('Site Verzeichnis ' . $folder . ' ist vorhanden!'), 'Notice');
		}
		else
		{
			Folder::create($sitepath);
			Factory::getApplication()->enqueueMessage(Text::_('Site Verzeichnis ' . $folder . ' wurde angelegt!'), 'Notice');
		}

		foreach ($data as $key => $value)
		{
			if ($value->completed == '')
			{
			}
			else
			{
				$value->images = 'error.png';
				$path          = '';
				/**
				 *
				 * adminsprache
				 */
				if (strpos($value->file, 'admin-com_') !== false)
				{
					$mod           = str_replace('admin-', '', $value->file);
					$path          = $adminpath;
					$path          .= '/' . $folder . '.' . $mod;
					$value->folder = $path;
					$content       = self::getData('resource/' . $value->slug . '/translation/' . $value->language . '?file=1');

					try
					{
						File::write($path, $content['data']);
						$value->images = 'ok.png';
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
						$value->images = 'error.png';
					}
				}

				/**
				 *
				 * frontend
				 */
				if (strpos($value->file, 'site-com_') !== false)
				{
					$mod           = str_replace('site-', '', $value->file);
					$path          = $sitepath;
					$path          .= '/' . $folder . '.' . $mod;
					$value->folder = $path;

					$content = self::getData('resource/' . $value->slug . '/translation/' . $value->language . '?file=1');

					try
					{
						File::write($path, $content['data']);
						$value->images = 'ok.png';
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
						$value->images = 'error.png';
					}
				}

				/**
				 *
				 * module
				 */
				if (strpos($value->file, 'mod_') !== false)
				{
					$mod           = str_replace('admin-', '', $value->file);
					$path          = $sitepath;
					$path          .= '/' . $folder . '.' . $mod;
					$value->folder = $path;

					$content = self::getData('resource/' . $value->slug . '/translation/' . $value->language . '?file=1');

					try
					{
						File::write($path, $content['data']);
						$value->images = 'ok.png';
					}
					catch (Exception $e)
					{
						Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
						$value->images = 'error.png';
					}
				}
			}
		}

		return $data;
	}

	/**
	 * sportsmanagementHelperTransifex::getData()
	 *
	 * @param   mixed  $path
	 *
	 * @return
	 */
	public static function getData($path)
	{
		$url     = self::$apiUrl . '/' . $path;
		$ch      = curl_init();
		$info    = '';
		$timeout = 120;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, "diddipoeler:dp190460");
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, 400);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		// Get the data
		$data = curl_exec($ch);

		// Get info about the request
		$info = curl_getinfo($ch);

		// Close the request
		curl_close($ch);

		return array(
			'data' => $data,
			'info' => $info
		);
	}


	/**
	 * sportsmanagementHelperTransifex::getLangCode()
	 *
	 * @param   mixed  $lang
	 * @param   bool   $inverse
	 *
	 * @return
	 */
	public static function getLangCode($lang, $inverse = false, $joomla = true)
	{
		$languages = self::getLangmap($joomla);

		if ($inverse)
		{
			return array_search($lang, $languages);
		}

		if (isset($languages[$lang]))
		{
			return $languages[$lang];
		}

		return false;
	}

	/**
	 * sportsmanagementHelperTransifex::getLangmap()
	 *
	 * @return
	 */
	private static function getLangmap($joomla = true)
	{
		if (!count(self::$languages))
		{
			$langMap = explode(
				',',
				'af_ZA: af-ZA, am_ET: am-ET, ar_AE: ar-AE, ar_BH: ar-BH, ar_DZ: ar-DZ, ar_EG: ar-EG, ar_IQ: ar-IQ, ar_JO: ar-JO, ar_KW: ar-KW, ar_LB: ar-LB, ar_LY: ar-LY, ar_MA: ar-MA, ar_OM: ar-OM, ar_QA: ar-QA, ar_SA: ar-SA, ar_SY: ar-SY, ar_TN: ar-TN, ar_YE: ar-YE, arn_CL: arn-CL, as_IN: as-IN, az_AZ: az-AZ, ba_RU: ba-RU, be_BY: be-BY, bg_BG: bg-BG, bn_BD: bn-BD, bn_IN: bn-IN, bo_CN: bo-CN, br_FR: br-FR, bs_BA: bs-BA, ca_ES: ca-ES, co_FR: co-FR, cs_CZ: cs-CZ, cy_GB: cy-GB, da_DK: da-DK, de_AT: de-AT, de_CH: de-CH, de_DE: de-DE, de_LI: de-LI, de_LU: de-LU, dsb_DE: dsb-DE, dv_MV: dv-MV, el_GR: el-GR, en_AU: en-AU, en_BZ: en-BZ, en_CA: en-CA, en_GB: en-GB, en_IE: en-IE, en_IN: en-IN, en_JM: en-JM, en_MY: en-MY, en_NZ: en-NZ, en_PH: en-PH, en_SG: en-SG, en_TT: en-TT, en_US: en-US, en_ZA: en-ZA, en_ZW: en-ZW, es_AR: es-AR, es_BO: es-BO, es_CL: es-CL, es_CO: es-CO, es_CR: es-CR, es_DO: es-DO, es_EC: es-EC, es_ES: es-ES, es_GT: es-GT, es_HN: es-HN, es_MX: es-MX, es_NI: es-NI, es_PA: es-PA, es_PE: es-PE, es_PR: es-PR, es_PY: es-PY, es_SV: es-SV, es_US: es-US, es_UY: es-UY, es_VE: es-VE, et_EE: et-EE, eu_ES: eu-ES, fa_IR: fa-IR, fi_FI: fi-FI, fil_PH: fil-PH, fo_FO: fo-FO, fr_BE: fr-BE, fr_CA: fr-CA, fr_CH: fr-CH, fr_FR: fr-FR, fr_LU: fr-LU, fr_MC: fr-MC, fy_NL: fy-NL, ga_IE: ga-IE, gd_GB: gd-GB, gl_ES: gl-ES, gsw_FR: gsw-FR, gu_IN: gu-IN, ha_NG: ha-NG, he_IL: he-IL, hi_IN: hi-IN, hr_BA: hr-BA, hr_HR: hr-HR, hsb_DE: hsb-DE, hu_HU: hu-HU, hy_AM: hy-AM, id_ID: id-ID, ig_NG: ig-NG, ii_CN: ii-CN, is_IS: is-IS, it_CH: it-CH, it_IT: it-IT, iu_CA: iu-CA, ja_JP: ja-JP, ka_GE: ka-GE, kk_KZ: kk-KZ, kl_GL: kl-GL, km_KH: km-KH, kn_IN: kn-IN, ko_KR: ko-KR, kok_IN: kok-IN, ky_KG: ky-KG, lb_LU: lb-LU, lo_LA: lo-LA, lt_LT: lt-LT, lv_LV: lv-LV, mi_NZ: mi-NZ, mk_MK: mk-MK, ml_IN: ml-IN, mn_CN: mn-CN, mn_MN: mn-MN, moh_CA: moh-CA, mr_IN: mr-IN, ms_BN: ms-BN, ms_MY: ms-MY, mt_MT: mt-MT, nb_NO: nb-NO, ne_NP: ne-NP, nl_BE: nl-BE, nl_NL: nl-NL, nn_NO: nn-NO, nso_ZA: nso-ZA, oc_FR: oc-FR, or_IN: or-IN, pa_IN: pa-IN, pl_PL: pl-PL, prs_AF: prs-AF, ps_AF: ps-AF, pt_BR: pt-BR, pt_PT: pt-PT, qut_GT: qut-GT, quz_BO: quz-BO, quz_EC: quz-EC, quz_PE: quz-PE, rm_CH: rm-CH, ro_RO: ro-RO, ru_RU: ru-RU, rw_RW: rw-RW, sa_IN: sa-IN, sah_RU: sah-RU, se_FI: se-FI, se_NO: se-NO, se_SE: se-SE, si_LK: si-LK, sk_SK: sk-SK, sl_SI: sl-SI, sma_NO: sma-NO, sma_SE: sma-SE, smj_NO: smj-NO, smj_SE: smj-SE, smn_FI: smn-FI, sms_FI: sms-FI, sq_AL: sq-AL, sr_BA: sr-BA, sr_CS: sr-CS, sr_ME: sr-ME, sr_RS: sr-RS, sv_FI: sv-FI, sv_SE: sv-SE, sw_KE: sw-KE, syr_SY: syr-SY, ta_IN: ta-IN, te_IN: te-IN, tg_TJ: tg-TJ, th_TH: th-TH, tk_TM: tk-TM, tn_ZA: tn-ZA, tr_TR: tr-TR, tt_RU: tt-RU, tzm_DZ: tzm-DZ, ug_CN: ug-CN, uk_UA: uk-UA, ur_PK: ur-PK, uz_UZ: uz-UZ, vi_VN: vi-VN, wo_SN: wo-SN, xh_ZA: xh-ZA, yo_NG: yo-NG, zh_CN: zh-CN, zh_HK: zh-HK, zh_MO: zh-MO, zh_SG: zh-SG, zh_TW: zh-TW, zu_ZA: zu-ZA'
			);

			foreach ($langMap as $map)
			{
				$langCodes = explode(':', $map);

				if ($joomla)
				{
					$languages[trim($langCodes[1])] = trim($langCodes[0]);
				}
				else
				{
					$languages[trim($langCodes[0])] = trim($langCodes[1]);
				}
			}

			self::$languages = $languages;
		}

		return self::$languages;
	}

}


