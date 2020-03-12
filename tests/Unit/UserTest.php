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

use FlexPHP\Database\Exception\UserDatabaseException;
use FlexPHP\Database\User;

class DatabaseTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     *
     * @param string $name
     */
    public function testItUserCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        (new User($name, 'password'))->asCreate();
    }

    public function testItUserCreateWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(\str_replace("\r\n", "\n", <<<T
CREATE USER '$name'@'%' IDENTIFIED BY '$password';
T
), $user->asCreate());
    }

    public function testItUserCreateWithCustomHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $host = 'custom.host';

        $user = new User($name, $password, $host);
        $this->assertEquals(\str_replace("\r\n", "\n", <<<T
CREATE USER '$name'@'$host' IDENTIFIED BY '$password';
T
), $user->asCreate());
    }

    public function testItUserDrop(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $host = 'custom.host';

        $user = new User($name, $password, $host);
        $this->assertEquals(\str_replace("\r\n", "\n", <<<T
DROP USER '$name'@'$host';
T
), $user->asDrop());
    }

    public function getNameInvalid(): array
    {
        return [
            ['jon doe'],
        ];
    }
}
