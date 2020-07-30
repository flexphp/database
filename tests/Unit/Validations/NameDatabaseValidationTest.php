<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Tests\Unit\Validations;

use FlexPHP\Database\Exception\DatabaseValidationException;
use FlexPHP\Database\Tests\TestCase;
use FlexPHP\Database\Validations\NameDatabaseValidation;

class NameDatabaseValidationTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     *
     * @param string $name
     */
    public function testItNameInvalidThrowException($name): void
    {
        $this->expectException(DatabaseValidationException::class);

        (new NameDatabaseValidation($name))->validate();
    }

    /**
     * @dataProvider getNameValid
     *
     * @param string $name
     */
    public function testItNameValid($name): void
    {
        (new NameDatabaseValidation($name))->validate();

        $this->assertTrue(true);
    }

    public function getNameInvalid(): array
    {
        return [
            // [null],
            // [[]],
            [''],
            [' '],
            ['db.dot'],
            ['has space'],
            ['1db'],
            ['d?b'],
            ['d!b'],
            ['ñdb'],
            ['_db'],
            ['d' . \str_repeat('b', 63) . 'b'],
        ];
    }

    public function getNameValid(): array
    {
        return [
            ['db'],
            ['db_one'],
            ['db-one'],
            ['db1'],
            ['db_'],
            ['d' . \str_repeat('b', 61) . 'b'],
        ];
    }
}
