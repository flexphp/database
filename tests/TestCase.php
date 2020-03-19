<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase
 */
class TestCase extends PHPUnitTestCase
{
    public function getDataType(): array
    {
        return [
            ['smallint'],
            ['integer'],
            ['bigint'],
            ['decimal'],
            ['float'],
            ['string'],
            ['text'],
            ['guid'],
            ['binary'],
            ['blob'],
            ['boolean'],
            ['date'],
            ['date_immutable'],
            ['datetime'],
            ['datetime_immutable'],
            ['datetimetz'],
            ['datetimetz_immutable'],
            ['time'],
            ['time_immutable'],
            ['dateinterval'],
            ['array'],
            ['simple_array'],
            ['json'],
            ['object'],
        ];
    }
}
