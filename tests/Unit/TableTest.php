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
        $attributes = [
            Keyword::NAME => 'foo',
            Keyword::DATATYPE => 'string',
            Keyword::CONSTRAINTS => [
                'min' => 10,
                'max' => 100,
            ],
        ];

        $schemaAttribute = new SchemaAttribute($attributes);

        $column = new Column($schemaAttribute);

        $schema = new Schema();
        $schema->setName('bar');
        $schema->setAttributes(['foo' => $attributes]);
        

        $table = new Table($schema);

        $this->assertEquals($schema->name(), $table->getName());
        $this->assertEquals([$column], $table->getColumns());
        $this->assertEquals([
            'collation' => 'utf8mb4',
        ], $table->getOptions());
    }
}
