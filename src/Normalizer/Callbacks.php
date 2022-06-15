<?php

namespace App\Normalizer;

use DateTimeInterface;

class Callbacks
{
    public const DATETIME_TO_DATE = [self::class, 'datetimeToDate'];
    public const DATETIME_ATOMIC = [self::class, 'datetimeAtomic'];

    /**
     * @param mixed $innerObject
     * @return string|null
     */
    public static function datetimeToDate($innerObject): ?string
    {
        return $innerObject instanceof DateTimeInterface ? $innerObject->format('Y-m-d') : null;
    }

    /**
     * @param mixed $innerObject
     * @return string|null
     */
    public static function datetimeAtomic($innerObject): ?string
    {
        return $innerObject instanceof DateTimeInterface ? $innerObject->format(DateTimeInterface::ATOM) : null;
    }
}
