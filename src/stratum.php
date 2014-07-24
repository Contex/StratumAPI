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
require_once 'tcp_socket.php';
require_once 'tcp_exception.php';
require_once 'stratum_subscribe.php';

class Stratum
{
	private $hostname;
	private $ip_address;
	private $port;
	protected $socket;

	public function __construct($hostname, $port)
	{
		$this->hostname = $hostname;
		$this->port = $port;
		$this->socket = new TCPSocket($hostname, $port);
		$this->ip_address = $this->getSocket()->getIPAddress();
	}

	public function getHostname()
	{
		return $this->hostname;
	}

	public function getIPAddress()
	{
		return $this->ip_address;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getSocket()
	{
		return $this->socket;
	}

	private function getResponse(array $array, array $array2 = NULL)
	{
		if ($array2 === NULL) {
			$response = $this->getSocket()->getResponse(
				$this->formatData($array)
			);
		} else {
			$response = $this->getSocket()->getResponse(
				$this->formatData($array),
				$this->formatData($array2)
			);
		}
		if (empty($response)) {
			throw new UnexpectedValueException('Could not connect to stratum, stratum returned an empty string.', -2);
		}
		return $response;
	}

	private function formatData($array)
	{
		return json_encode($array) . "\n";
	}

	public function getSubscribe($worker = NULL, $password = NULL)
	{
		if ($worker === NULL || $password === NULL) {
			return new StratumSubscribe($this->getResponse(
				array(
					'id'     => 1,
					'method' => 'mining.subscribe',
					'params' => array()
				)
			));

		} else {
			return new StratumSubscribe($this->getResponse(
				array(
					'id'     => 1,
					'method' => 'mining.subscribe',
					'params' => array()
				),
				array(
					'id'     => 2,
					'method' => 'mining.authorize',
					'params' => array(
						$worker,
						$password
					)
				)
			));
		}
	}
}
?>