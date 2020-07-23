<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Tests\Unit;

use Exception;
use FlexPHP\Database\Builder;
use FlexPHP\Database\Tests\TestCase;
use FlexPHP\Schema\Constants\Keyword;
use FlexPHP\Schema\Schema;
use FlexPHP\Schema\SchemaInterface;

class BuilderTest extends TestCase
{
    public function testItPlatformErrorThrowException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('try: MySQL, SQLSrv');

        new Builder('Unknow');
    }

    public function testItCreateMySQLDatabase(): void
    {
        $name = 'db';

        $builder = new Builder('MySQL');
        $builder->createDatabase($name);
        $this->assertEquals(<<<T
CREATE DATABASE $name CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
T
, $builder->toSql());
    }

    public function testItCreateSQLSrvDatabase(): void
    {
        $name = 'db';

        $builder = new Builder('SQLSrv');
        $builder->createDatabase($name);
        $this->assertEquals(<<<T
CREATE DATABASE $name COLLATE latin1_general_100_ci_ai_sc;
T
, $builder->toSql());
    }

    public function testItCreateMySQLUser(): void
    {
        $name = 'mysql';
        $password = 'p4sw00rd';

        $builder = new Builder('MySQL');
        $builder->createUser($name, $password);
        $this->assertEquals(<<<T
CREATE USER '$name'@'%' IDENTIFIED BY '$password';
T
, $builder->toSql());
    }

    public function testItCreateSQLSrvUser(): void
    {
        $name = 'sqlsrv';
        $password = 'p4sw00rd';

        $builder = new Builder('SQLSrv');
        $builder->createUser($name, $password);
        $this->assertEquals(<<<T
CREATE LOGIN $name WITH PASSWORD = '$password';
GO
CREATE USER $name FOR LOGIN $name;
GO
T
, $builder->toSql());
    }

    public function testItCreateMySQLTable(): void
    {
        $builder = new Builder('MySQL');
        $builder->createTable($this->getSchema());
        $this->assertEquals(<<<T
CREATE TABLE bar (
    foo VARCHAR(255) DEFAULT NULL COMMENT 'foo'
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
T
, $builder->toSql());
    }

    public function testItCreateSQLSrvTable(): void
    {
        $builder = new Builder('SQLSrv');
        $builder->createTable($this->getSchema());
        $this->assertEquals(<<<T
CREATE TABLE bar (
    foo NVARCHAR(255)
);
T
, $builder->toSql());
    }

    public function testItCreateMySQLComplete(): void
    {
        $dbname = 'complete';
        $username = 'username';
        $password = 'password';
        $host = 'host';
        $schema = new Schema('bar', 'title', [
            [
                Keyword::NAME => 'Pk',
                Keyword::DATATYPE => 'integer',
                Keyword::CONSTRAINTS => [
                    'ai' => true,
                    'minlength' => 10,
                    'maxlength' => 100,
                ],
            ],
            [
                Keyword::NAME => 'foo',
                Keyword::DATATYPE => 'string',
                Keyword::CONSTRAINTS => [
                    'minlength' => 10,
                    'maxlength' => 100,
                ],
            ],
            [
                Keyword::NAME => 'bar',
                Keyword::DATATYPE => 'integer',
                Keyword::CONSTRAINTS => [
                    'min' => 10,
                    'max' => 100,
                ],
            ],
        ]);

        $schemaFk = new Schema('fuz', 'title', [
            [
                Keyword::NAME => 'Pk',
                Keyword::DATATYPE => 'string',
                Keyword::CONSTRAINTS => [
                    'ai' => true,
                    'minlength' => 10,
                    'maxlength' => 100,
                ],
            ],
            [
                Keyword::NAME => 'barId',
                Keyword::DATATYPE => 'integer',
                Keyword::CONSTRAINTS => [
                    'fk' => 'bar',
                ],
            ],
        ]);

        $builder = new Builder('MySQL');
        $builder->createDatabase($dbname);
        $builder->createUser($username, $password, $host);
        $builder->createTable($schema);
        $builder->createTable($schemaFk);

        $this->assertEquals(<<<T
CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE USER '$username'@'$host' IDENTIFIED BY '$password';

CREATE TABLE bar (
    Pk INT AUTO_INCREMENT DEFAULT NULL COMMENT 'Pk',
    foo VARCHAR(100) DEFAULT NULL COMMENT 'foo',
    bar INT DEFAULT NULL COMMENT 'bar'
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE fuz (
    Pk VARCHAR(100) DEFAULT NULL COMMENT 'Pk',
    barId INT DEFAULT NULL COMMENT 'barId'
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
T
, $builder->toSql());
    }

    public function getSchema(): SchemaInterface
    {
        return new Schema('bar', 'title', [[
            Keyword::NAME => 'foo',
            Keyword::DATATYPE => 'string',
            Keyword::CONSTRAINTS => [
                'min' => 10,
                'max' => 100,
            ],
        ]]);
    }
}
