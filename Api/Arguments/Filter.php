<?php

namespace PhotoCake\Api\Arguments;

class Filter
{
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const INTEGER = 'integer';
    const FLOAT = 'float';
    const OBJECT = 'object';

    const EMAIL = 'email';
    const URL = 'url';
    const IP = 'ip';
    const PHONE = 'phone';

    const BASE64 = 'base64';
    const JSON = 'json';

    const PHONE_REGEXP = '/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{5,10}$/';

    /**
     * @param mixed $value
     * @param string $type
     * @return bool|mixed|null|string
     */
    public function check($value, $type) {
        if ($value !== NULL) {
            switch ($type) {
                case self::STRING:
                case self::BOOLEAN:
                case self::INTEGER:
                case self::FLOAT:
                case self::OBJECT: {
                    settype($value, $type);
                    return $value;
                }

                case self::BASE64: return base64_decode($value, true);
                case self::JSON:   return json_decode($value);

                case self::EMAIL: return filter_var($value, FILTER_VALIDATE_EMAIL);
                case self::URL:   return filter_var($value, FILTER_VALIDATE_URL);
                case self::IP:    return filter_var($value, FILTER_VALIDATE_IP);

                case self::PHONE:
                    return preg_match(self::PHONE_REGEXP, $value) > 0 ? $value : false;

                default: return NULL;
            }
        }

        return NULL;
    }
}
