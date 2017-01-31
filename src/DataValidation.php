<?php

namespace Kholenkov;

class DataValidation {

	/**
	 * @param string $bik
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateBik($bik, &$error_message = null, &$error_code = null) {
		$result = false;
		$bik = (string) $bik;
		if (!$bik) {
			$error_code = 1;
			$error_message = 'БИК пуст';
		} elseif (preg_match('/[^0-9]/', $bik)) {
			$error_code = 2;
			$error_message = 'БИК может состоять только из цифр';
		} elseif (strlen($bik) !== 9) {
			$error_code = 3;
			$error_message = 'БИК может состоять только из 9 цифр';
		} else {
			$result = true;
		}
		return $result;
	}

	/**
	 * @param string $inn
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateInn($inn, &$error_message = null, &$error_code = null) {
		$result = false;
		$inn = (string) $inn;
		if (!$inn) {
			$error_code = 1;
			$error_message = 'ИНН пуст';
		} elseif (preg_match('/[^0-9]/', $inn)) {
			$error_code = 2;
			$error_message = 'ИНН может состоять только из цифр';
		} elseif (!in_array($inn_length = strlen($inn), [10, 12])) {
			$error_code = 3;
			$error_message = 'ИНН может состоять только из 10 или 12 цифр';
		} else {
			$check_digit = function($inn, $coefficients) {
				$n = 0;
				foreach ($coefficients as $i => $k) {
					$n += $k * (int) $inn{$i};
				}
				return $n % 11 % 10;
			};
			switch ($inn_length) {
				case 10:
					$n10 = $check_digit($inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]);
					if ($n10 === (int) $inn{9}) {
						$result = true;
					}
					break;
				case 12:
					$n11 = $check_digit($inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
					$n12 = $check_digit($inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
					if (($n11 === (int) $inn{10}) && ($n12 === (int) $inn{11})) {
						$result = true;
					}
					break;
			}
			if (!$result) {
				$error_code = 4;
				$error_message = 'Неправильное контрольное число';
			}
		}
		return $result;
	}

	/**
	 * @param string $kpp
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateKpp($kpp, &$error_message = null, &$error_code = null) {
		$result = false;
		$kpp = (string) $kpp;
		if (!$kpp) {
			$error_code = 1;
			$error_message = 'КПП пуст';
		} elseif (strlen($kpp) !== 9) {
			$error_code = 2;
			$error_message = 'КПП может состоять только из 9 знаков (цифр или заглавных букв латинского алфавита от A до Z)';
		} elseif (!preg_match('/^[0-9]{4}[0-9A-Z]{2}[0-9]{3}$/', $kpp)) {
			$error_code = 3;
			$error_message = 'Неправильный формат КПП';
		} else {
			$result = true;
		}
		return $result;
	}

	/**
	 * @param string $ks
	 * @param string $bik
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateKs($ks, $bik, &$error_message = null, &$error_code = null) {
		$result = false;
		if (self::validateBik($bik, $error_message, $error_code)) {
			$ks = (string) $ks;
			if (!$ks) {
				$error_code = 1;
				$error_message = 'К/С пуст';
			} elseif (preg_match('/[^0-9]/', $ks)) {
				$error_code = 2;
				$error_message = 'К/С может состоять только из цифр';
			} elseif (strlen($ks) !== 20) {
				$error_code = 3;
				$error_message = 'К/С может состоять только из 20 цифр';
			} else {
				$bik_ks = '0' . substr((string) $bik, -5, 2) . $ks;
				$checksum = 0;
				foreach ([7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1] as $i => $k) {
					$checksum += $k * ((int) $bik_ks{$i} % 10);
				}
				if ($checksum % 10 === 0) {
					$result = true;
				} else {
					$error_code = 4;
					$error_message = 'Неправильное контрольное число';
				}
			}
		}
		return $result;
	}

	/**
	 * @param string $ogrn
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateOgrn($ogrn, &$error_message = null, &$error_code = null) {
		$result = false;
		$ogrn = (string) $ogrn;
		if (!$ogrn) {
			$error_code = 1;
			$error_message = 'ОГРН пуст';
		} elseif (preg_match('/[^0-9]/', $ogrn)) {
			$error_code = 2;
			$error_message = 'ОГРН может состоять только из цифр';
		} elseif (strlen($ogrn) !== 13) {
			$error_code = 3;
			$error_message = 'ОГРН может состоять только из 13 цифр';
		} else {
			$n13 = (int) substr(bcsub(substr($ogrn, 0, -1), bcmul(bcdiv(substr($ogrn, 0, -1), '11', 0), '11')), -1);
			if ($n13 === (int) $ogrn{12}) {
				$result = true;
			} else {
				$error_code = 4;
				$error_message = 'Неправильное контрольное число';
			}
		}
		return $result;
	}

	/**
	 * @param string $ogrnip
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateOgrnip($ogrnip, &$error_message = null, &$error_code = null) {
		$result = false;
		$ogrnip = (string) $ogrnip;
		if (!$ogrnip) {
			$error_code = 1;
			$error_message = 'ОГРНИП пуст';
		} elseif (preg_match('/[^0-9]/', $ogrnip)) {
			$error_code = 2;
			$error_message = 'ОГРНИП может состоять только из цифр';
		} elseif (strlen($ogrnip) !== 15) {
			$error_code = 3;
			$error_message = 'ОГРНИП может состоять только из 15 цифр';
		} else {
			$n15 = (int) substr(bcsub(substr($ogrnip, 0, -1), bcmul(bcdiv(substr($ogrnip, 0, -1), '13', 0), '13')), -1);
			if ($n15 === (int) $ogrnip{14}) {
				$result = true;
			} else {
				$error_code = 4;
				$error_message = 'Неправильное контрольное число';
			}
		}
		return $result;
	}

	/**
	 * @param string $rs
	 * @param string $bik
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateRs($rs, $bik, &$error_message = null, &$error_code = null) {
		$result = false;
		if (self::validateBik($bik, $error_message, $error_code)) {
			$rs = (string) $rs;
			if (!$rs) {
				$error_code = 1;
				$error_message = 'Р/С пуст';
			} elseif (preg_match('/[^0-9]/', $rs)) {
				$error_code = 2;
				$error_message = 'Р/С может состоять только из цифр';
			} elseif (strlen($rs) !== 20) {
				$error_code = 3;
				$error_message = 'Р/С может состоять только из 20 цифр';
			} else {
				$bik_rs = substr((string) $bik, -3) . $rs;
				$checksum = 0;
				foreach ([7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1, 3, 7, 1] as $i => $k) {
					$checksum += $k * ((int) $bik_rs{$i} % 10);
				}
				if ($checksum % 10 === 0) {
					$result = true;
				} else {
					$error_code = 4;
					$error_message = 'Неправильное контрольное число';
				}
			}
		}
		return $result;
	}

	/**
	 * @param string $snils
	 * @param mixed $error_message
	 * @param mixed $error_code
	 * @return boolean
	 */
	public static function validateSnils($snils, &$error_message = null, &$error_code = null) {
		$result = false;
		$snils = (string) $snils;
		if (!$snils) {
			$error_code = 1;
			$error_message = 'СНИЛС пуст';
		} elseif (preg_match('/[^0-9]/', $snils)) {
			$error_code = 2;
			$error_message = 'СНИЛС может состоять только из цифр';
		} elseif (strlen($snils) !== 11) {
			$error_code = 3;
			$error_message = 'СНИЛС может состоять только из 11 цифр';
		} else {
			$sum = 0;
			for ($i = 0; $i < 9; $i++) {
				$sum += (int) $snils{$i} * (9 - $i);
			}
			$check_digit = 0;
			if ($sum < 100) {
				$check_digit = $sum;
			} elseif ($sum > 101) {
				$check_digit = $sum % 101;
				if ($check_digit === 100) {
					$check_digit = 0;
				}
			}
			if ($check_digit === (int) substr($snils, -2)) {
				$result = true;
			} else {
				$error_code = 4;
				$error_message = 'Неправильное контрольное число';
			}
		}
		return $result;
	}

}
