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
require_once 'coin_functions.php';

class StratumNotify
{
	private $data;

	public function __construct($data)
	{
		$this->data = $data;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getParams()
	{
		return isset($this->data) && array_key_exists('params', $this->data) ? $this->data['params'] : NULL;
	}

	public function getJobID()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(0, $this->data['params']) ? $this->data['params'][0] : NULL;
	}

	public function getPreviousBlockHash()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(1, $this->data['params']) ? $this->data['params'][1] : NULL;
	}
	
	public function getPreviousBlockHashDecoded()
	{
		if ($this->getPreviousBlockHash() === NULL) {
			return NULL;
		}

		$block_hash_decoded = '';
		for ($i = 56; $i >= 0; $i -= 8) {
	    	$block_hash_decoded .= substr($this->getPreviousBlockHash(), $i, 8);
	    }
		return $block_hash_decoded;
	}

	public function getCoinbasePart1()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(2, $this->data['params']) ? $this->data['params'][2] : NULL;
	}

	public function getCoinbasePart2()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(3, $this->data['params']) ? $this->data['params'][3] : NULL;
	}

	public function getMerkleBranches()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(4, $this->data['params']) ? $this->data['params'][4] : NULL;
	}

	public function getBlockVersion()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(5, $this->data['params']) ? $this->data['params'][5] : NULL;
	}

	public function getNetworkDifficulty()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(6, $this->data['params']) ? $this->data['params'][6] : NULL;
	}

	public function getTime()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(7, $this->data['params']) ? $this->data['params'][7] : NULL;
	}

	public function isCleanJob()
	{
		return isset($this->data) && array_key_exists('params', $this->data) && array_key_exists(8, $this->data['params']) ? $this->data['params'][8] : NULL;
	}

	public function getID()
	{
		return isset($this->data) && array_key_exists('id', $this->data) ? $this->data['id'] : NULL;
	}

	public function getMethod()
	{
		return isset($this->data) && array_key_exists('method', $this->data) ? $this->data['method'] : NULL;
	}

	public function getWalletAddress($address_prefix)
	{
		return CoinFunctions::getAddressesFromCoinbase($address_prefix, $this->getCoinbasePart1(), $this->getCoinbasePart2());
	}
}
?>