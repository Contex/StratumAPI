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
class TCPException extends Exception
{
	private $hostname,
		 	$ip_address,
			$port,
			$socket,
			$result,
			$error;

	public function __construct($host, $ip_address, $port, $socket, $error, $result = NULL)
	{
		$this->host = $host;
		$this->ip_address = $ip_address;
		$this->port = $port;
		$this->socket = $socket;
		$this->result = $result;
		$this->error = $error;
	}

	public function getHostname()
	{
		return $this->host;
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

	public function getResult()
	{
		return $this->result;
	}

	public function getError()
	{
		return $this->error;
	}
}
?>