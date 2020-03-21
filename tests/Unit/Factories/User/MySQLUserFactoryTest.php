<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Tests\Unit\Factories\User;

use FlexPHP\Database\Exception\UserDatabaseException;
use FlexPHP\Database\Factories\User\SQLSrvUserFactory;
use FlexPHP\Database\Tests\TestCase;
use FlexPHP\Database\User;

class MySQLUserFactoryTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     *
     * @param mixed $name
     */
    public function testItCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        (new User($name, 'password'))->toSqlCreate();
    }

    public function testItCreateWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
CREATE USER '$name'@'%' IDENTIFIED BY '$password';
T
, $user->toSqlCreate());
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
, $user->toSqlCreate());
    }

    /**
     * @dataProvider getHostDefault
     *
     * @param mixed $host
     */
    public function testItDropWithDefaultHost($host): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
DROP USER '$name'@'%';
T
, $user->toSqlDrop());
    }

    public function testItDropWithCustomHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $host = 'custom.host';

        $user = new User($name, $password, $host);
        $this->assertEquals(<<<T
DROP USER '$name'@'$host';
T
, $user->toSqlDrop());
    }

    /**
     * @dataProvider getPermissionValid
     *
     * @param mixed $permission
     */
    public function testItGrantOptionOnAll($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $user->setGrant($permission);
        $this->assertEquals(<<<T
GRANT $permission ON *.* TO '$name'@'%';
T
, $user->toSqlPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     *
     * @param mixed $permission
     */
    public function testItGrantOptionOnDatabase($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';

        $user = new User($name, $password);
        $user->setGrant($permission, $database);
        $this->assertEquals(<<<T
GRANT $permission ON $database.* TO '$name'@'%';
T
, $user->toSqlPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     *
     * @param mixed $permission
     */
    public function testItGrantOptionOnTable($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $table = 'table';

        $user = new User($name, $password);
        $user->setGrant($permission, $database, $table);
        $this->assertEquals(<<<T
GRANT $permission ON $database.$table TO '$name'@'%';
T
, $user->toSqlPrivileges());
    }

    public function testItGrantOptionsMultiple(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $table = 'table';
        $permissions = [
            'CREATE',
            'UPDATE',
        ];

        $user = new User($name, $password);
        $user->setGrants($permissions, $database, $table);
        $this->assertEquals(<<<T
GRANT CREATE ON $database.$table TO '$name'@'%';
GRANT UPDATE ON $database.$table TO '$name'@'%';
T
, $user->toSqlPrivileges());
    }

    public function getMappingPermission(string $permission): string
    {
        return SQLSrvUserFactory::MAPPING_PERMISSION[$permission];
    }

    public function getHostDefault(): array
    {
        return [
            [''],
            [' '],
            ['%'],
        ];
    }

    public function getNameInvalid(): array
    {
        return [
            ['jon doe'],
        ];
    }

    public function getPermissionValid(): array
    {
        return [
            ['ALL PRIVILEGES'],
            ['CREATE'],
            ['DROP'],
            ['DELETE'],
            ['INSERT'],
            ['SELECT'],
            ['UPDATE'],
            ['GRANT OPTION'],
        ];
    }
}
