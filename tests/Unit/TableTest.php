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

use FlexPHP\Database\Column;
use FlexPHP\Database\Table;
use FlexPHP\Schema\Constants\Keyword;
use FlexPHP\Schema\Schema;
use FlexPHP\Schema\SchemaAttribute;

class TableTest extends TestCase
{
    public function testItDefinition(): void
    {
        $attribute1 = [
            Keyword::NAME => 'foo',
            Keyword::DATATYPE => 'string',
            Keyword::CONSTRAINTS => [
                'minlength' => 10,
                'maxlength' => 100,
            ],
        ];

        $attribute2 = [
            Keyword::NAME => 'baz',
            Keyword::DATATYPE => 'integer',
            Keyword::CONSTRAINTS => [
                'min' => 10,
                'max' => 100,
            ],
        ];

        $schemaAttribute1 = new SchemaAttribute('foo', 'string', $attribute1[Keyword::CONSTRAINTS]);
        $schemaAttribute2 = new SchemaAttribute('baz', 'integer', $attribute2[Keyword::CONSTRAINTS]);

        $column1 = new Column($schemaAttribute1);
        $column2 = new Column($schemaAttribute2);

        $schema = new Schema('bar', 'title', [$attribute1, $attribute2]);

        $table = new Table($schema);

        $this->assertEquals($schema->name(), $table->getName());
        $this->assertEquals([$column1, $column2], $table->getColumns());
        $this->assertEquals([
            'collation' => 'utf8mb4',
        ], $table->getOptions());
    }
}
