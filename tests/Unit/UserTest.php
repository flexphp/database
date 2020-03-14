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
use FlexPHP\Database\User;

class UserTest extends TestCase
{
    public function testItUserMySqlCreate(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
CREATE USER '$name'@'%' IDENTIFIED BY '$password';
T
, $user->asCreate());
    }

    public function testItUserMySqlDrop(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';

        $user = new User($name, $password);
        $this->assertEquals(<<<T
DROP USER '$name'@'%';
T
, $user->asDrop());
    }

    public function testItUserMySqlGrants(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $table = 'table';

        $user = new User($name, $password);
        $user->setGrants(['CREATE'], $database, $table);
        $this->assertEquals(<<<T
GRANT CREATE ON $database.$table TO '$name'@'%';
T
, $user->asPrivileges());
    }

    public function testItUserSqlSrvCreate(): void
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

    public function testItUserSqlSrvGrants(): void
    {
        $name = 'jon';
        $password = 'p4sw00rd';
        $database = 'db';
        $table = 'table';
        $permission = 'CREATE';

        $user = new User($name, $password);
        $user->setFactory(new SQLSrvUserFactory());
        $user->setGrants(['CREATE'], $database, $table);
        $this->assertEquals(<<<T
GRANT $permission ON $database.$table TO $name;
GO
T
, $user->asPrivileges());
    }
}
