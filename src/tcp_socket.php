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
require_once 'tcp_exception.php';

class TCPSocket
{
	private $socket = NULL;
	private $hostname;
	private $ip_address;
	private $port;

	public function __construct($hostname, $port)
	{
		$this->hostname = $hostname;
		if (filter_var($hostname, FILTER_VALIDATE_IP)) {
			$this->ip_address = $hostname;
		} else {
			$this->ip_address = gethostbyname($hostname);
			if ($hostname == $this->ip_address) {
				throw new TCPException($this->hostname, $this->ip_address, $port, NULL, 'Failed to resolve hostname: "' . $hostname . '" [RESULT="' . $this->ip_address . '"]');
			}
		}
		$this->port = $port;
	}

	private function connect()
	{
		if (!isset($this->socket) || $this->socket === NULL) {
			$this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($this->socket === FALSE) {
			    throw new TCPException($this->hostname, $this->ip_address, $this->port, $this->socket, socket_strerror(socket_last_error())); 
			}
			socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 2, 'usec' => 0));
			socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 2, 'usec' => 0));
			$result = @socket_connect($this->socket, $this->ip_address, $this->port);
			if ($result === FALSE) {
			    throw new TCPException($this->hostname, $this->ip_address, $this->port, $this->socket, socket_strerror(socket_last_error($this->socket)), $result); 
			}
		}
	}

	public function getIPAddress()
	{
		return $this->ip_address;
	}

	public function getHostname()
	{
		return $this->hostname;
	}

	public function getPort()
	{
		return $this->port;
	}

	public function getResponse($in, $in2 = NULL)
	{
		$this->connect();
		$out = '';
		socket_write($this->socket, $in, strlen($in));
		if ($in2 !== NULL) {
			socket_write($this->socket, $in2, strlen($in2));
		}
		while ($buffer = @socket_read($this->socket, 2048)) {
			$out .= $buffer;
		}
		socket_close($this->socket);
		$this->socket = NULL;
		return $out;
	}
}
?>