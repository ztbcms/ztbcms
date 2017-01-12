<?php

// +----------------------------------------------------------------------
// | 内容拼音转换函数
// +----------------------------------------------------------------------

define('CODETABLEDIR', APP_PATH . 'Content/Data/');

/**
 * gbk转拼音
 * @param $txt
 * @return string
 */
function gbk_to_pinyin($txt) {
	$l = strlen($txt);
	$i = 0;
	$pyarr = array();
	$py = array();
	$filename = CODETABLEDIR . 'gb-pinyin.table';
	$fp = fopen($filename, 'r');
	while (!feof($fp)) {
		$p = explode("-", fgets($fp, 32));
		$pyarr[intval($p[1])] = trim($p[0]);
	}
	fclose($fp);
	ksort($pyarr);
	while ($i < $l) {
		$tmp = ord($txt[$i]);
		if ($tmp >= 128) {
			$asc = abs($tmp * 256 + ord($txt[$i + 1]) - 65536);
			$i = $i + 1;
		} else {
			$asc = $tmp;
		}

		$py[] = asc_to_pinyin($asc, $pyarr);
		$i++;
	}
	return $py;
}

/**
 * Ascii转拼音
 * @param $asc
 * @param $pyarr
 * @return string
 */
function asc_to_pinyin($asc, &$pyarr) {
	if ($asc < 128) {
		return chr($asc);
	} elseif (isset($pyarr[$asc])) {
		return $pyarr[$asc];
	} else {
		foreach ($pyarr as $id => $p) {
			if ($id >= $asc) {
				return $p;
			}
		}
	}
}
