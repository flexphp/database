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
use FlexPHP\Database\Table;
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
, $builder->toSql()
        );
    }

    public function testItCreateSQLSrvDatabase(): void
    {
        $name = 'db';

        $builder = new Builder('SQLSrv');
        $builder->createDatabase($name);
        $this->assertEquals(<<<T
CREATE DATABASE $name COLLATE latin1_general_100_ci_ai_sc;
T
, $builder->toSql()
        );
    }

    public function testItCreateMySQLTable(): void
    {
        $table = new Table($this->getSchema());

        $builder = new Builder('MySQL');
        $builder->createTable($table);
        $this->assertEquals(<<<T
CREATE TABLE bar (foo VARCHAR(255) DEFAULT NULL COMMENT 'foo') DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
T
, $builder->toSql()
        );
    }

    public function testItCreateSQLSrvTable(): void
    {
        $table = new Table($this->getSchema());

        $builder = new Builder('SQLSrv');
        $builder->createTable($table);
        $this->assertEquals(<<<T
CREATE TABLE bar (foo NVARCHAR(255));
T
, $builder->toSql()
        );
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
