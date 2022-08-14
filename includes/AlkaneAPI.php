<?php

namespace Alkane\AlkaneAPI;

/**
 * Class AlkaneAPI
 *
 * @category  Alkane API Handler
 * @package   AlkaneAPI
 * @author    Sadiq <sadiq.com.bd@gmail.com>
 * @copyright Copyright (c) 2022
 * @version   1.0.2
 * @package   Alkane\AlkaneAPI
 */

class AlkaneAPI {

    private static $apiKey, $apiSecret;

    public static function setApiKey(string $key) {
        self::$apikey = $key;
    }

    public static function setApiSecret(string $sec) {
        self::$apiSecret = $sec;
    }

    // in development mode

}

