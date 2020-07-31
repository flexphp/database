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

use FlexPHP\Database\Builder;
use FlexPHP\Database\Exception\DatabaseValidationException;
use FlexPHP\Database\Tests\TestCase;
use FlexPHP\Schema\Schema;
use FlexPHP\Schema\SchemaAttribute;
use FlexPHP\Schema\SchemaInterface;

class BuilderTest extends TestCase
{
    public function testItPlatformErrorThrowException(): void
    {
        $this->expectException(DatabaseValidationException::class);
        $this->expectExceptionMessage('try: MySQL, SQLSrv');

        new Builder('Unknow');
    }

    public function testItDatabaseNameErrorThrowException(): void
    {
        $this->expectException(DatabaseValidationException::class);
        $this->expectExceptionMessage('Database name [_db] invalid');

        $builder = new Builder('MySQL');
        $builder->createDatabase('_db');
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

    public function testItCreateMySQLDatabaseWithUse(): void
    {
        $name = 'db';

        $builder = new Builder('MySQL');
        $builder->createDatabaseWithUse($name);
        $this->assertEquals(<<<T
CREATE DATABASE $name CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE $name;
T
, $builder->toSql());
    }

    public function testItCreateSQLSrvDatabaseWithUse(): void
    {
        $name = 'db';

        $builder = new Builder('SQLSrv');
        $builder->createDatabaseWithUse($name);
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

    public function testItCreateMySQLUserWithGrants(): void
    {
        $name = 'mysql';
        $password = 'p4sw00rd';

        $builder = new Builder('MySQL');
        $builder->createUser($name, $password, 'host', ['ALL PRIVILEGES']);
        $this->assertEquals(<<<T
CREATE USER '$name'@'host' IDENTIFIED BY '$password';

GRANT ALL PRIVILEGES ON *.* TO '$name'@'host';
T
, $builder->toSql());
    }

    public function testItCreateSQLSrvUserWithGrants(): void
    {
        $name = 'sqlsrv';
        $password = 'p4sw00rd';

        $builder = new Builder('SQLSrv');
        $builder->createUser($name, $password, 'host', ['ALL PRIVILEGES']);
        $this->assertEquals(<<<T
CREATE LOGIN $name WITH PASSWORD = '$password';
GO
CREATE USER $name FOR LOGIN $name;
GO

GRANT ALL TO sqlsrv;
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
    foo INT DEFAULT NULL COMMENT 'foo'
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
    foo INT
);

EXEC sp_addextendedproperty N'MS_Description', N'foo', N'SCHEMA', 'dbo', N'TABLE', 'bar', N'COLUMN', foo;
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
            new SchemaAttribute('Pk', 'string', 'pk|required'),
            new SchemaAttribute('foo', 'string', 'minlength:10|maxlength:100'),
            new SchemaAttribute('bar', 'integer', 'min:10|max'),
        ]);

        $schemaFk = new Schema('fuz', 'title', [
            new SchemaAttribute('Pk', 'integer', 'pk|ai|required'),
            new SchemaAttribute('barId', 'integer', 'fk:bar'),
        ]);

        $builder = new Builder('MySQL');
        $builder->createDatabaseWithUse($dbname);
        $builder->createUser($username, $password, $host, ['ALL PRIVILEGES'], $dbname);
        $builder->createTable($schema);
        $builder->createTable($schemaFk);

        $this->assertEquals(<<<T
CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE $dbname;

CREATE USER '$username'@'$host' IDENTIFIED BY '$password';

GRANT ALL PRIVILEGES ON $dbname.* TO '$username'@'$host';

CREATE TABLE bar (
    Pk VARCHAR(255) NOT NULL COMMENT 'Pk',
    foo VARCHAR(100) DEFAULT NULL COMMENT 'foo',
    bar INT DEFAULT NULL COMMENT 'bar',
    PRIMARY KEY(Pk)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE fuz (
    Pk INT AUTO_INCREMENT NOT NULL COMMENT 'Pk',
    barId INT DEFAULT NULL COMMENT 'barId',
    INDEX IDX_51837B119A5BAE65 (barId),
    PRIMARY KEY(Pk)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

ALTER TABLE fuz ADD CONSTRAINT FK_51837B119A5BAE65 FOREIGN KEY (barId) REFERENCES bar (id);
T
, $builder->toSql());
    }

    public function testItConstraints(): void
    {
        $dbname = 'complete';
        $username = 'username';
        $password = 'password';
        $host = 'host';
        $schema = new Schema('bar', 'title', [
            new SchemaAttribute('Pk', 'string', 'pk|required'),
            new SchemaAttribute('foo', 'string', 'minlength:10|maxlength:100'),
            new SchemaAttribute('bar', 'integer', 'min:10|max'),
        ]);

        $schemaFk = new Schema('fuz', 'title', [
            new SchemaAttribute('Pk', 'integer', 'pk|ai|required'),
            new SchemaAttribute('barId', 'integer', 'fk:bar'),
        ]);

        $schemaFk2 = new Schema('baz', 'title', [
            new SchemaAttribute('Pk', 'integer', 'pk|ai|required'),
            new SchemaAttribute('fuzId', 'integer', 'fk:fuz'),
            new SchemaAttribute('barId', 'integer', 'fk:bar'),
        ]);

        $builder = new Builder('MySQL');
        $builder->createDatabase($dbname);
        $builder->createUser($username, $password, $host);
        $builder->createTable($schema);
        $builder->createTable($schemaFk);
        $builder->createTable($schemaFk2);

        $this->assertEquals(<<<T
CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

CREATE USER '$username'@'$host' IDENTIFIED BY '$password';

CREATE TABLE bar (
    Pk VARCHAR(255) NOT NULL COMMENT 'Pk',
    foo VARCHAR(100) DEFAULT NULL COMMENT 'foo',
    bar INT DEFAULT NULL COMMENT 'bar',
    PRIMARY KEY(Pk)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE fuz (
    Pk INT AUTO_INCREMENT NOT NULL COMMENT 'Pk',
    barId INT DEFAULT NULL COMMENT 'barId',
    INDEX IDX_51837B119A5BAE65 (barId),
    PRIMARY KEY(Pk)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

CREATE TABLE baz (
    Pk INT AUTO_INCREMENT NOT NULL COMMENT 'Pk',
    fuzId INT DEFAULT NULL COMMENT 'fuzId',
    barId INT DEFAULT NULL COMMENT 'barId',
    INDEX IDX_78240498BEB399D5 (fuzId),
    INDEX IDX_782404989A5BAE65 (barId),
    PRIMARY KEY(Pk)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

ALTER TABLE fuz ADD CONSTRAINT FK_51837B119A5BAE65 FOREIGN KEY (barId) REFERENCES bar (id);

ALTER TABLE baz ADD CONSTRAINT FK_78240498BEB399D5 FOREIGN KEY (fuzId) REFERENCES fuz (id);

ALTER TABLE baz ADD CONSTRAINT FK_782404989A5BAE65 FOREIGN KEY (barId) REFERENCES bar (id);
T
, $builder->toSql());
    }

    public function testItConstraintsFkErrorThrowException(): void
    {
        $this->expectException(DatabaseValidationException::class);
        $this->expectExceptionMessage('Tables in foreign');

        $schema = new Schema('bar', 'title', [
            new SchemaAttribute('Pk', 'string', 'pk|required'),
            new SchemaAttribute('foo', 'string', 'minlength:10|maxlength:100'),
            new SchemaAttribute('bar', 'integer', 'min:10|max'),
        ]);

        $schemaFk = new Schema('fuz', 'title', [
            new SchemaAttribute('Pk', 'integer', 'pk|ai|required'),
            new SchemaAttribute('barId', 'integer', 'fk:notexist'),
        ]);

        $builder = new Builder('MySQL');
        $builder->createTable($schema);
        $builder->createTable($schemaFk);

        $builder->toSql();
    }

    public function getSchema(): SchemaInterface
    {
        return new Schema('bar', 'title', [
            new SchemaAttribute('foo', 'integer', [
                'min' => 10,
                'max' => 100,
            ]),
        ]);
    }
}
