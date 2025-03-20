<?php

declare(strict_types=1);

namespace App\Service;

class AppointmentReferenceGenerator
{
    public function generateReferenceNumber(): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $chars = \str_shuffle($chars);

        $limit = \strlen($chars) - 1;
        $referenceNumber = '';
        for ($i = 1; $i <= 6; $i++) {
            $referenceNumber .= $chars[\random_int(0, $limit)];
        }

        // TODO: check if the reference number already exists in the database

        return $referenceNumber;
    }
}