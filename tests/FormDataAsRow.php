<?php
/**
 * @author selcukmart
 * 10.02.2022
 * 14:32
 */

namespace Tests;

class FormDataAsRow
{
    private static $data = [
        'id' => '7',
        'type' => '1',
        'user_id' => '8015',
        'address_identification' => 'Work Adress',
        'name' => 'Joe',
        'surname' => 'DOE',
        'address' => 'Test strasse berlin',
        'postal_code' => '28100',
        'country' => 'us',
        'province' => '0',
        'county' => '0',
        'district' => '0',
        'neighbourhood' => '0',
        'phone' => '',
        'tel' => '+905542856789',
        'email' => 'admin@hostingdevi.com',
        'password' => '12345678',
        'invoice_type' => '1',
        'identification_number' => '3514950',
        'nationality_tc_or_not' => '1',
        'company_name' => '',
        'tax_department' => '',
        'tax_number' => '',
        'is_e_invoice_user' => '2',
        'color' => '#000',
        'date' => '10.02.2022',
        'datetime-local' => '10.02.2022 15:14',
        'month' => 'february',
        'height' => 180,
        'time' => '15:30',
        'website' => 'https://www.onlinekurum.com',
        'week' => '6',
    ];

    /**
     * @return array
     */
    public static function getData(): array
    {
        return self::$data;
    }
}