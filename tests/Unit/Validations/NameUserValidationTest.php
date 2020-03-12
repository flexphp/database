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

use FlexPHP\Database\Exception\NameUserValidationException;
use FlexPHP\Database\Tests\TestCase;
use FlexPHP\Database\Validations\NameUserValidation;

class NameUserValidationTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     *
     * @param string $name
     * @return void
     */
    public function testItNameInvalidThrowException($name): void
    {
        $this->expectException(NameUserValidationException::class);

        (new NameUserValidation($name))->validate();
    }

    /**
     * @dataProvider getNameValid
     *
     * @param string $name
     * @return void
     */
    public function testItNameValid($name): void
    {
        (new NameUserValidation($name))->validate();

        $this->assertTrue(true);
    }

    public function getNameInvalid(): array
    {
        return [
            // [null],
            // [[]],
            [''],
            [' '],
            ['has space'],
            ['1jon'],
            ['j?on'],
            ['j!on'],
            ['ñon'],
            ['_jon'],
            ['j' . str_repeat('o', 31) . 'n'],
        ];
    }

    public function getNameValid(): array
    {
        return [
            ['jon'],
            ['jon_doe'],
            ['jon-doe'],
            ['jon1'],
            ['jon_'],
            ['j' . str_repeat('o', 30) . 'n'],
        ];
    }
}
