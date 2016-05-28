<?php

namespace Afpa\PokerGameBundle\Models;

class Encrypt {

    const PREFIX_SALT = 'babar';
    const SUFFIX_SALT = 'celeste';

    private $sPwd;

    public function __construct($pwd) {
        $this->sPwd = md5(self::PREFIX_SALT . $pwd . self::SUFFIX_SALT);
    }

    public function getEncryption() {
        return $this->sPwd;
    }

}
