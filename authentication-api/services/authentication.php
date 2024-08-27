<?php

use FurqanSiddiqui\BIP39\BIP39 as BIP39BIP39;
use FurqanSiddiqui\BIP39\WordList;

class AuthenticationService
{

    public function register($deviceId)
    {
        $binaryEntropy = hash('sha256', $deviceId, true);
        $hexEntropy = bin2hex($binaryEntropy);
        $wordList = new WordList('spanish');
        $bip39 = new BIP39BIP39(12, $wordList);
        $mnemonic = $bip39->entropy2Mnemonic($hexEntropy);

        return ['success' => true, 'codigo' => 200, 'msg' => $mnemonic];
    }
}
