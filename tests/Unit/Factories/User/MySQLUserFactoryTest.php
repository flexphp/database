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
     */
    public function testItCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        (new User($name, 'password'))->asCreate();
    }

    public function testItCreateWithDefaultHost(): void
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

    public function testItDropWithDefaultHost(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
DROP USER '$name'@'%';
T
, $user->asDrop());
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
, $user->asDrop());
    }

    /**
     * @dataProvider getPermissionValid
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
, $user->asPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
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
, $user->asPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
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
, $user->asPrivileges());
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
, $user->asPrivileges());
    }

    /**
     * @dataProvider getNameInvalid
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

    /**
     * @dataProvider getPermissionValid
     */
    public function testItUserSqlSrvGrantOptionOnAll($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $mappingPermission = $this->getMappingPermission($permission);

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $user->setGrant($permission);
        $this->assertEquals(<<<T
GRANT $mappingPermission TO $name;
GO
T
, $user->asPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     */
    public function testItUserSqlSrvGrantOptionOnDatabase($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $mappingPermission = $this->getMappingPermission($permission);

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $user->setGrant($permission, $database);
        $this->assertEquals(<<<T
GRANT $mappingPermission ON $database TO $name;
GO
T
, $user->asPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     */
    public function testItUserSqlSrvGrantOptionOnTable($permission): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $table = 'table';
        $mappingPermission = $this->getMappingPermission($permission);

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $user->setGrant($permission, $database, $table);
        $this->assertEquals(<<<T
GRANT $mappingPermission ON $database.$table TO $name;
GO
T
, $user->asPrivileges());
    }

    public function testItUserSqlSrvGrantOptionsMultiple(): void
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
        $user->setFactory(new SQLSrvUserFactory());
        $user->setGrants($permissions, $database, $table);
        $this->assertEquals(<<<T
GRANT CREATE ON $database.$table TO $name;
GO
GRANT UPDATE ON $database.$table TO $name;
GO
T
, $user->asPrivileges());
    }

    public function getMappingPermission(string $permission): string
    {
        return SQLSrvUserFactory::MAPPING_PERMISSION[$permission];
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
