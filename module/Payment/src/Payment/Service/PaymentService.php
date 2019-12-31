<?php

namespace Payment\Service;


class PaymentService
{
    public function payServiceSet($payCode) {
        $className  = 'Payment\\Service\\'.ucfirst($payCode).'Service';
        $classN     = new $className;
        return $classN;
    }
}