<?php

namespace Vinicius\Ecc;

class Ecies {
    
    private $privateKey;
    private $publicKey;
    private $rBuf;
    private $kEkM;
    private $kE;
    private $kM;
    private $opts;
    
    public function __construct($privateKey, $publicKey, $opts = array("noKey" => true, "shortTag" => true)) {
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->opts = $opts;
    }

    public function getRbuf() {
        if (is_null($this->rBuf)) {
            $this->rBuf = Helper::hex2bin($this->privateKey->getPublic(true, "hex"));
        }
        return $this->rBuf;
    }

    private function getSharedKey()
    {
        $shared = $this->privateKey->derive($this->publicKey->getPublic());
        $bin = Helper::hex2bin( $shared->toString("hex") );
        return hash("sha512", $bin, true);
    }
    
    public function getkEkM() {
        if (is_null($this->kEkM)) {
            $this->kEkM = $this->getSharedKey();
        }
        return $this->kEkM;
    }
    
    public function getkE() {
        if (is_null($this->kE)) {
            $this->kE = Helper::substring($this->getkEkM(), 0, 32);
        }
        return $this->kE;
    }
    
    public function getkM() {
        if (is_null($this->kM)) {
            $this->kM = Helper::substring($this->getkEkM(), 32, 64);
        }
        return $this->kM;
    }

    private function getPrivateEncKey()
    {
        $hex = $this->privateKey->getPrivate("hex");
        return Helper::hex2bin( $hex );
    }
    
    public function encrypt($message, $ivbuf = null) {
        if (is_null($ivbuf)) {
            $ivbuf = Helper::substring(Crypto::hmacSha256($this->getPrivateEncKey(), $message), 0, 16);
        }
        $c = $ivbuf . Crypto::aes256CbcPkcs7Encrypt($message, $this->getkE(), $ivbuf);
        $d = Crypto::hmacSha256($this->getkM(), $c);
        if (Helper::arrayValue($this->opts, "shortTag")) {
            $d = Helper::substring($d, 0, 4);
        }
        if (Helper::arrayValue($this->opts, "noKey")) {
            $encbuf = $c . $d;
        }
        else {
            $encbuf = $this->getRbuf() . $c . $d;
        }
        return $encbuf;
    }
    
    public function decrypt($encbuf) {
        $offset = 0;
        $tagLength = 32;
        if (Helper::arrayValue($this->opts, "shortTag")) {
            $tagLength = 4;
        }
        if (!Helper::arrayValue($this->opts, "noKey")) {
            $offset = 33;
             $this->publicKey = Helper::substring($encbuf, 0, 33);
        }
        
        $c = Helper::substring($encbuf, $offset, strlen($encbuf) - $tagLength);
        $d = Helper::substring($encbuf, strlen($encbuf) - $tagLength, strlen($encbuf));
        
        $d2 = Crypto::hmacSha256($this->getkM(), $c);
        if (Helper::arrayValue($this->opts, "shortTag")) {
            $d2 = Helper::substring($d2, 0, 4);
        }
        
        $equal = true;
        for ($i = 0; $i < strlen($d); $i++) {
            $equal &= ($d[$i] === $d2[$i]);
        }
        if (!$equal) {
            throw new \Exception("Invalid checksum");
        }
        
        return Crypto::aes256CbcPkcs7Decrypt(Helper::substring($c, 16, strlen($c)), $this->getkE(), Helper::substring($c, 0, 16));
    }
}