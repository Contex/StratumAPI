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
class StratumAuthorize
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

	public function wasSuccessful()
	{
		return isset($this->data) && array_key_exists('result', $this->data) ? $this->data['result'] : NULL;
	}

	public function getID()
	{
		return isset($this->data) && array_key_exists('id', $this->data) ? $this->data['id'] : NULL;
	}
}
?>