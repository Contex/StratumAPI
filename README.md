StratumAPI
==========

Stratum PHP wrapper to grab information from a stratum host

Copyright (c) 2014, Contex <https://github.com/Contex>

StratumAPI is licensed under GNU LESSER GENERAL PUBLIC LICENSE Version 3.

Documentation
==========

I'll try and explain it as simple as I can as the back-end is a bit more complex.

In the stratum response, there are 2 data items that are interesting, the last block hash and the coinbase (coinbase part 2 to be more specific).
See https://www.btcguild.com/new_protocol.php for the documentation of the stratum protocol.
```
params[1] = Hash of previous block. Used to build the header.
params[3] = Coinbase (part 2). The miner appends this after the first part of the coinbase and the two ExtraNonce values.
```

I can use the previous block hash and look it up on popular block explorers to determine which coin the stratum is mining, once I know that I can decode the encoded address in the coinbase.

I can also use the data from the coinbase to determine what the size of the block reward is.

I'll use my pool that mines DogeCoin as an example, here's the stratum response for my stratum:
```
{
    "id": null,
    "method": "mining.notify",
    "params": [
        "3313",
        "d18ecb2bf1914e4dd427e52fa540b2e3e71cef57a03cd3e16dca7d0ec948519f",
        "01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff270376ea02062f503253482f043f95555308",
        "0d2f6e6f64655374726174756d2f000000000100b7d7edbc1600001976a914e0401fae9ec7f860ceefd71b17205d219f55f28388ac00000000",
        [
            "8db8c9798694387729af33f0aa5b1f23254e0230584b58d9d99c95db64ee5db8",
            "094422fbeba5a10782e66bef52d7b2678b78a57843fa54ff43a8039effe567bd",
            "078d2637b58df8f0c1ad49f1e1057c5a52ca2de0f369074c004ed8178aeb73c4",
            "aeb497338ed4d1bbb5fd2eca7c978f98a75663715b19e6677406cbf834c1878d"
        ],
        "00000002",
        "1b3e8a11",
        "5355953e",
        false
    ]
}
```
Last block hash (*BEFORE* reversing each 8 byte string): 
```
d18ecb2bf1914e4dd427e52fa540b2e3e71cef57a03cd3e16dca7d0ec948519f
```

Last block hash (*AFTER* reversing each 8 byte string): 
```
c948519f6dca7d0ea03cd3e1e71cef57a540b2e3d427e52ff1914e4dd18ecb2b
```
http://dogechain.info/block/c948519f6dca7d0ea03cd3e1e71cef57a540b2e3d427e52ff1914e4dd18ecb2b

Coinbase part 2 in this case example is:
```
0d2f6e6f64655374726174756d2f000000000100b7d7edbc1600001976a914e0401fae9ec7f860ceefd71b17205d219f55f28388ac00000000
```
The tool splits coinbase part 2 into pieces.
```
end_input: 0d2f6e6f64655374726174756d2f
sequence_number: 00000000
tx_outputs: 01
tx_amount: 00b7d7edbc160000
txout_length: 19
script_start: 76a914
pubkey_hash (i.e. encoded wallet address): e0401fae9ec7f860ceefd71b17205d219f55f283
script_end: 88ac
lock_time: 00000000
```
The tx_amount (**00b7d7edbc160000**) can be converted into a little endian integer (64 bit) and then divided by 10^8, that gives me **250,007** (which is DogeCoin's current block reward [250K] + fees).

Next step would be to decode the *pubkey_hash*.

I need to know the address prefix (*PUBKEY_ADDRESS*) beforehand, in Dogecoin's case it's **D**, see https://github.com/dogecoin/dogecoin/blob/master/src/chainparams.cpp#L151
```
std::vector<unsigned char> pka = list_of(30); // <-- This is what we're looking for, 30
base58Prefixes[PUBKEY_ADDRESS] = pka;
```

That's **30** in decimals, when converted it's **1e** in heximal, The tools prefixes the *pubkey_hash* with **1e** like so: 
```
1ee0401fae9ec7f860ceefd71b17205d219f55f283
```
The next steps are:

1. Convert *1ee0401fae9ec7f860ceefd71b17205d219f55f283* to binary
2. Hash it twice with sha256: *7beafdd54f7a4c1fedc97356f8e00047b0d28a7465ca8db9e12d69b1ebd29a82*
3. Grab the first 8 characters of the double sha256 hashed string: *7beafdd5*
4. Append/Suffix the 8 characters to the pubkey_hash and convert it to uppercase like so: *1EE0401FAE9EC7F860CEEFD71B17205D219F55F2837BEAFDD5*
5. Encode the new pubkey_hash with base58

When this process is done, the tool will output the wallet address of the stratum.
