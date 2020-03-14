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

use FlexPHP\Database\Factories\User\SQLSrvUserFactory;
use FlexPHP\Database\Exception\UserDatabaseException;
use FlexPHP\Database\User;

class UserTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     *
     * @param mixed $name
     */
    public function testItUserMySqlCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        (new User($name, 'password'))->asCreate();
    }

    public function testItUserMySqlCreateWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
CREATE USER '$name'@'%' IDENTIFIED BY '$password';
T
, $user->asCreate());
    }

    public function testItUserCreateWithCustomHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $host = 'custom.host';

        $user = new User($name, $password, $host);
        $this->assertEquals(<<<T
CREATE USER '$name'@'$host' IDENTIFIED BY '$password';
T
, $user->asCreate());
    }

    public function testItUserMySqlDropWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
DROP USER '$name'@'%';
T
, $user->asDrop());
    }

    public function testItUserMySqlDropWithCustomHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $host = 'custom.host';

        $user = new User($name, $password, $host);
        $this->assertEquals(<<<T
DROP USER '$name'@'$host';
T
, $user->asDrop());
    }

    /**
     * @dataProvider getNameInvalid
     *
     * @param mixed $name
     */
    public function testItUserSqlSrvCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        $user = new User($name, 'password');
        $user->setFactory(new SQLSrvUserFactory());
        $user->asCreate();
    }

    public function testItUserSqlSrvCreateWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $this->assertEquals(<<<T
CREATE LOGIN $name WITH PASSWORD = '$password';
GO
CREATE USER $name FOR LOGIN $name;
GO
T
, $user->asCreate());
    }

    public function testItUserSqlSrvDrop(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $this->assertEquals(<<<T
DROP USER $name;
GO
T
, $user->asDrop());
    }

    public function getNameInvalid(): array
    {
        return [
            ['jon doe'],
        ];
    }
}
