<?php
/*
 * This file is part of StratumAPI <https://github.com/Contex/StratumAPI>.
 *
 * StratumAPI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * StratumAPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class CoinFunctions
{
	private static $chars = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
	private static $chars2 = '0123456789ABCDEF';

	public static function decodeHex($hex)
	{
		$hex = strtoupper($hex);
		$return = '0';
		for ($i = 0; $i < strlen($hex); $i++) {
			$current = (string) strpos(self::$chars2, $hex[$i]);
			$return = (string) bcmul($return, '16', 0);
			$return = (string) bcadd($return, $current, 0);
		}
		return $return;
	}

	public static function encodeHex($dec)
	{
		$return = '';
		while (bccomp($dec,0)==1)
		{
			$dv = (string) bcdiv($dec, '16', 0);
			$rem = (integer) bcmod($dec, '16');
			$dec = $dv;
			$return .= self::$chars2[$rem];
		}
		return strrev($return);
	}

	public static function decodeBase58($base58)
	{
		$origbase58 = $base58;
		$return = '0';
		for ($i = 0; $i < strlen($base58); $i++) {
			$current = (string) strpos(self::$chars, $base58[$i]);
			$return = (string) bcmul($return, '58', 0);
			$return = (string) bcadd($return, $current, 0);
		}
		
		$return = self::encodeHex($return);
		
		for ($i = 0; $i < strlen($origbase58) && $origbase58[$i] == '1'; $i++) {
			$return = '00' . $return;
		}
		
		if (strlen($return) %2 != 0) {
			$return = '0' . $return;
		}
		
		return $return;
	}

	public static function encodeBase58($hex)
	{
		if (strlen($hex) %2 != 0) {
			return NULL;
			die('encodeBase58: uneven number of hex characters');
		}
		$orighex = $hex;
		
		$hex = self::decodeHex($hex);
		$return = '';
		while (bccomp($hex, 0) == 1) {
			$dv = (string) bcdiv($hex, '58', 0);
			$rem = (integer) bcmod($hex, '58');
			$hex = $dv;
			$return .= self::$chars[$rem];
		}
		$return = strrev($return);
		
		for ($i = 0; $i < strlen($orighex) && substr($orighex, $i, 2) == '00'; $i += 2) {
			$return = '1' . $return;
		}
		
		return $return;
	}

	public static function coinbaseToAddress($coinbase, $address_prefix)
	{
		$coinbase = self::getCoinbaseData($coinbase);
		return self::hash160ToAddress($coinbase['pubkey_hash'], $address_prefix);
	}

	public static function getCoinbaseData($coinbase) 
	{
		if (strlen($coinbase) === 134) {
			return array(
				'end_input'       => substr($coinbase, 0, 28),
				'sequence_number' => substr($coinbase, 28, 8),
				'tx_outputs'      => substr($coinbase, 36, 2),
				'tx_amount'       => substr($coinbase, 38, 16),
				'txout_length'    => substr($coinbase, 54, 2),
				'script_start'    => substr($coinbase, 56, 2),
				'pubkey_hash'     => substr($coinbase, 58, 66),
				'script_end'      => substr($coinbase, 122, 2),
				'lock_time'       => substr($coinbase, 124, 8)
			);
		} else if (strpos($coinbase, '000000000') !== FALSE) {
			$pos = strpos($coinbase, '000000000');
			return array(
				'end_input'       => substr($coinbase, 0, $pos),
				'sequence_number' => substr($coinbase, $pos, 8),
				'tx_outputs'      => substr($coinbase, $pos + 8, 2),
				'tx_amount'       => substr($coinbase, $pos + 10, 16),
				'txout_length'    => substr($coinbase, $pos + 26, 2),
				'script_start'    => substr($coinbase, $pos + 28, 6),
				'pubkey_hash'     => substr($coinbase, $pos + 34, 40),
				'script_end'      => substr($coinbase, $pos + 74, 4),
				'lock_time'       => substr($coinbase, $pos + 78, 8)
			);
		} else if (strpos($coinbase, 'ffffffff') !== FALSE) {
			$pos = strpos($coinbase, 'ffffffff');
			return array(
				'end_input'       => substr($coinbase, 0, $pos),
				'sequence_number' => substr($coinbase, $pos, 8),
				'tx_outputs'      => substr($coinbase, $pos + 8, 2),
				'tx_amount'       => substr($coinbase, $pos + 10, 16),
				'txout_length'    => substr($coinbase, $pos + 26, 2),
				'script_start'    => substr($coinbase, $pos + 28, 6),
				'pubkey_hash'     => substr($coinbase, $pos + 34, 40),
				'script_end'      => substr($coinbase, $pos + 74, 4),
				'lock_time'       => substr($coinbase, $pos + 78, 8)
			);
		} else {
			return NULL;
		}
	}

	public static function getTXAmountFromCoinbase($coinbase) 
	{
		$coinbase = self::getCoinbaseData($coinbase);
		if ($coinbase === NULL) {
			return NULL;
		} else {
			$tx_amount = $coinbase['tx_amount'];
			$result = '0';
			while (strlen($tx_amount)) {
				$ord = hexdec(substr($tx_amount, strlen($tx_amount) - 2, 2));
				$result = bcadd(bcmul($result, 256), $ord);
				$tx_amount = substr($tx_amount, 0, strlen($tx_amount) - 2);
			}
			return $result / 100000000;
		}
	}

	public static function getAddressesFromCoinbase($address_prefix = '1E', $coinbase1, $coinbase2)
	{
		if ($coinbase2 === NULL) {
			return NULL;
		}
		if ($coinbase2 == '00000000') {
			$addresses = array();
			$coinbase = $coinbase1;
			$part1 = substr($coinbase, 0, 136);
			$addresses_part = substr($coinbase, 136);
			$addresses_length = strlen($addresses_part);
			for ($i = 62; $addresses_length > $i; $i += 62) {
				$encoded_address = substr(substr(substr($addresses_part, 0, 62), 8), 0, 40);
				$addresses_part = substr($addresses_part, 62 + strlen('000000'));
				$addresses[] = self::hash160ToAddress($encoded_address, $address_prefix);
			}
			return $addresses;
		} else {
			return self::hash160ToAddress(substr(substr($coinbase2, -52), 0, 40), $address_prefix);
		}
	}

	public static function hash160ToAddress($hash160, $address_prefix)
	{
		if (strlen($hash160) == 66) {
			$hash160 = strtoupper(hash('ripemd160', hash('sha256', pack('H*', $hash160), TRUE)));
		}

		$hash160 = $address_prefix . $hash160;
		$check = pack('H*' , $hash160);
		$check = hash('sha256', hash('sha256', $check, TRUE));
		$check = substr($check, 0, 8);
		$hash160 = strtoupper($hash160 . $check);
		return self::encodeBase58($hash160);
	}

	public static function addressToHash160($addr)
	{
		$addr = self::decodeBase58($addr);
		$addr = substr($addr, 2, strlen($addr) - 10);
		return $addr;
	}

	public static function checkAddress($addr, $type = 'LTC', $address_prefix)
	{
		$addr = self::decodeBase58($addr);
		if (strlen($addr) != 50) {
			return false;
		}
		$version = substr($addr,0,2);
		if (strtolower($type) == 'ltc' && hexdec($version) != hexdec($address_prefix)) {
			return false;
		} else if (strtolower($type) == 'btc' && hexdec($version) > hexdec($address_prefix)) {
			return false;
		}
		$check = substr($addr, 0, strlen($addr) - 8);
		$check = pack('H*', $check);
		$check = strtoupper(hash('sha256', hash('sha256', $check, TRUE)));
		$check = substr($check, 0, 8);
		return $check == substr($addr, strlen($addr) - 8);
	}

	public static function hash160($data)
	{
		return strtoupper(hash('ripemd160', hash('sha256', pack('H*', $data), TRUE)));
	}

	public static function pubKeyToAddress($pub_key)
	{
		return self::hash160ToAddress(self::hash160($pub_key));
	}

	public static function remove0x($string)
	{
		if (substr($string,0, 2) === '0x' || substr($string, 0, 2) === '0X') {
			$string = substr($string, 2);
		}
		return $string;
	}
}
?>