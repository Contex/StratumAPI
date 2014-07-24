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
require_once 'stratum_notify.php';
require_once 'stratum_difficulty.php';
require_once 'stratum_result.php';
require_once 'stratum_authorize.php';


class StratumSubscribe
{
	private $reponse;
	private $data;
	private $notify;
	private $difficulty;
	private $authorize;
	private $work;

	public function __construct($response)
	{
		$this->response = $response;
		$this->data = explode("\n", $response);
		foreach ($this->data as $data_json) {
			if (empty($data_json)) {
				continue;
			}
			$data_array = json_decode($data_json, TRUE);
			if ($data_array !== NULL) {
				if (array_key_exists('method', $data_array)) {
					if (strtolower($data_array['method']) == 'mining.notify') {
						$this->notify = new StratumNotify($data_array);
						continue;
					} else if (strtolower($data_array['method']) == 'mining.set_difficulty') {
						$this->difficulty = new StratumDifficulty($data_array);
						continue;
					}
				} else if (array_key_exists('id', $data_array) 
						&& $data_array['id'] === 2 
						&& array_key_exists('error', $data_array) 
						&& array_key_exists('result', $data_array)) {
					$this->authorize = new StratumAuthorize($data_array);
					continue;
				} else if (array_key_exists('id', $data_array) && $data_array['id'] === 1 
						&& array_key_exists('error', $data_array) 
						&& array_key_exists('result', $data_array) 
						&& is_array($data_array['result'])) {
					$this->result = new StratumResult($data_array);
					continue;
				}
			}
		}
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getNotify()
	{
		return $this->notify;
	}

	public function getDifficulty()
	{
		return $this->difficulty;
	}

	public function getAuthorize()
	{
		return $this->authorize;
	}
}
?>