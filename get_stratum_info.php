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

/*
EXAMPLE RESPONSE:

STRATUM: stratum.doge.hashfaster.com:3339 (192.99.20.36)
PREVIOUS BLOCK HASH: b10d4c09e4ce2a17a697ec80fef9d80def017e0b6d0ec7420f2cb8ea047e5739
DIFFICULTY BASE: 32
BLOCK REWARD: 62,500
COINBASE PART 2: 0d2f6e6f64655374726174756d2f 00000000 01 01a40731af050000 19 76a914 1d5f0968a3b4bc21179afa87676e698d15669a18 88ac 00000000
POSSIBLE WALLET ADDRESS IF THE STRATUM IS MINING DOGECOIN: D7pPz6Dxzcyg7FniV5AqzGzxasmQJfqNZp

STRATUM RESPONSE: 
{
	"id": 1,
	"result": [
		[
			[
				"mining.set_difficulty",
				"deadbeefcafebabed0bf030000000000"
			],
			[
				"mining.notify",
				"deadbeefcafebabed0bf030000000000"
			]
		],
		"5003570f",
		4
	],
	"error": null
}{
	"id": null,
	"method": "mining.set_difficulty",
	"params": [
		32
	]
}{
	"id": null,
	"method": "mining.notify",
	"params": [
		"948",
		"047e57390f2cb8ea6d0ec742ef017e0bfef9d80da697ec80e4ce2a17b10d4c09",
		"01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff270379ba04062f503253482f048498cf5308",
		"0d2f6e6f64655374726174756d2f000000000101a40731af0500001976a9141d5f0968a3b4bc21179afa87676e698d15669a1888ac00000000",
		[
			"0be98f63bec755c97b4463ac0a2cc0112958d7897ace5bf4dd44af6895af37d7",
			"f0c3d0b47b1fa1016566afcfdd5a4e56fd2e006fb315db248253c99ad7670021"
		],
		"00000002",
		"1b44ff58",
		"53cf9884",
		true
	]
}{
	"id": 2,
	"result": false,
	"error": null
}
*/
header('Content-type: text/plain');
require_once 'src/stratum.php';
require_once 'src/misc_functions.php';

if (empty($_GET['host'])) {
	die('The "host" parameter is not set. Example: get_stratum_address.php?host=stratum.rapidhash.net:3333');
} else if (empty($_GET['port']) && strpos($_GET['host'], ':') === FALSE) {
	die('The "port" parameter is not set. Example: get_stratum_address.php?host=stratum.rapidhash.net:3333');
}

if (strpos($_GET['host'], ':') !== FALSE) {
	if (strpos($_GET['host'], 'stratum tcp://') !== FALSE) {
		$_GET['host'] = str_replace('stratum tcp://', '', $_GET['host']);
	} else if (strpos($_GET['host'], 'http://') !== FALSE) {
		$_GET['host'] = str_replace('http://', '', $_GET['host']);
	}
	$array = explode(':', $_GET['host']);
	$hostname = $array[0];
	$port = $array[1];
} else {
	$hostname = $_GET['host'];
	$port = $_GET['port'];
}

if (!empty($_GET['worker']) && !empty($_GET['password'])) {
	$worker = $_GET['worker'];
	$password = $_GET['password'];
} else {
	$worker = NULL;
	$password = NULL;
}

try {
	$stratum = new Stratum($hostname, $port);
	if ($worker !== NULL && $password !== NULL) {
		$subscribe = $stratum->getSubscribe($worker, $password);
	} else {
		$subscribe = $stratum->getSubscribe();
	}
	echo 'STRATUM: ' . $hostname . ':' . $port . ' (' . $stratum->getIPAddress() . ')' . PHP_EOL;
	if ($worker !== NULL && $password !== NULL) {
		echo PHP_EOL . 'WORKER: ' . $worker . PHP_EOL;
		echo 'WORKER PASSWORD: ' . $password . PHP_EOL;
		echo 'SUCCESSFUL AUTHENTICATION: ' . ($subscribe->getAuthorize() !== NULL && $subscribe->getAuthorize()->wasSuccessful() ? 'YES' : 'NO') . PHP_EOL;
	}
	if ($subscribe->getNotify() !== NULL) {
		echo 'PREVIOUS BLOCK HASH: ' . $subscribe->getNotify()->getPreviousBlockHashDecoded() . PHP_EOL;
		if ($subscribe->getDifficulty() !== NULL) {
			echo 'DIFFICULTY BASE: ' . $subscribe->getDifficulty()->getDifficulty() . PHP_EOL;
		}
		echo 'BLOCK REWARD: ' . number_format(CoinFunctions::getTXAmountFromCoinbase($subscribe->getNotify()->getCoinbasePart2())) . PHP_EOL;
		$wallet_addresses = $subscribe->getNotify()->getWalletAddress('1E');
		if (!is_array($wallet_addresses)) {
			$coinbase_data = CoinFunctions::getCoinbaseData($subscribe->getNotify()->getCoinbasePart2());
			echo 'COINBASE PART 2: ' . $coinbase_data['end_input'] . ' ' . $coinbase_data['sequence_number'] . ' ' . $coinbase_data['tx_outputs'] . ' ' . $coinbase_data['tx_amount']. ' ' . $coinbase_data['txout_length'] . ' ' . $coinbase_data['script_start'] . ' ' . $coinbase_data['pubkey_hash'] . ' ' . $coinbase_data['script_end'] . ' ' . $coinbase_data['lock_time'] . PHP_EOL;
			echo 'POSSIBLE WALLET ADDRESS IF THE STRATUM IS MINING DOGECOIN: ' . $wallet_addresses . PHP_EOL;
		} else {
			echo 'POSSIBLE WALLET ADDRESSES IF THE STRATUM IS MINING DOGECOIN: ' . PHP_EOL;
			foreach ($wallet_addresses as $wallet_address) {
				echo '- ' . $wallet_address . PHP_EOL;
			}
		}
	} else if ($subscribe->getAuthorize() !== NULL && $subscribe->getAuthorize()->wasSuccessful()) {
		echo 'ERROR: Could not find the "notify" response from Stratum and the authentication was successful.' . PHP_EOL;
	} else {
		echo 'ERROR: Could not find the "notify" response from Stratum and the authentication was not successful.' . PHP_EOL;
		echo 'ERROR: This stratum might require a valid worker to query the server.' . PHP_EOL;
	}
	echo PHP_EOL . 'STRATUM RESPONSE: ' . PHP_EOL . MiscFunctions::JSONPrettyPrint($subscribe->getResponse());
} catch (UnexpectedValueException $e) {
	die('ERROR: ' . $e->getMessage());
} catch (TCPException $e) {
	die('Something went wrong with the TCP connection. [HOST="' . $e->getHostname() . '" | IP="' . $e->getIPAddress() . '" | PORT="' . $e->getPort() . '" | SOCKET="' . $e->getSocket() . '" | ERROR="' . $e->getError() . '" | RESULT="' . $e->getResult() . '"]'); 
}
?>