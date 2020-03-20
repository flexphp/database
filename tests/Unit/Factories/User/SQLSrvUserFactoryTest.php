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

class SQLSrvUserFactoryTest extends TestCase
{
    /**
     * @dataProvider getNameInvalid
     */
    public function testItSrvCreateWithNameInvalidThrownException($name): void
    {
        $this->expectException(UserDatabaseException::class);
        $user = new User($name, 'password');
        $user->setFactory(new SQLSrvUserFactory());
        $user->toSqlCreate();
    }

    public function testItSrvCreate(): void
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
, $user->toSqlCreate());
    }

    public function testItSrvDrop(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $this->assertEquals(<<<T
DROP USER $name;
GO
T
, $user->toSqlDrop());
    }

    /**
     * @dataProvider getPermissionValid
     */
    public function testItSrvGrantOptionOnAll($permission): void
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
, $user->toSqlPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     */
    public function testItSrvGrantOptionOnDatabase($permission): void
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
, $user->toSqlPrivileges());
    }

    /**
     * @dataProvider getPermissionValid
     */
    public function testItSrvGrantOptionOnTable($permission): void
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
, $user->toSqlPrivileges());
    }

    public function testItSrvGrantOptionsMultiple(): void
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
, $user->toSqlPrivileges());
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
