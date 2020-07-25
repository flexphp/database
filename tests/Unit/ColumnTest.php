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
use FlexPHP\Schema\SchemaAttribute;

class ColumnTest extends TestCase
{
    public function testItDefinition(): void
    {
        $schemaAttribute = new SchemaAttribute('foo', 'integer', [
            'min' => 10,
            'max' => 100,
        ]);

        $column = new Column($schemaAttribute);

        $this->assertEquals($schemaAttribute->name(), $column->getName());
        $this->assertEquals($schemaAttribute->dataType(), $column->getType());
        $this->assertFalse($column->isPrimaryKey());
        $this->assertFalse($column->isForeingKey());
        $this->assertEquals([
            'length' => $schemaAttribute->maxLength(),
            'notnull' => $schemaAttribute->isRequired(),
            'autoincrement' => $schemaAttribute->isAi(),
            'comment' => $schemaAttribute->name(),
        ], $column->getOptions());
    }

    public function testItDefinitionAi(): void
    {
        $schemaAttribute = new SchemaAttribute('foo', 'integer', [
            'pk' => true,
            'ai' => true,
            'required' => true,
        ]);

        $column = new Column($schemaAttribute);

        $this->assertEquals($schemaAttribute->name(), $column->getName());
        $this->assertEquals($schemaAttribute->dataType(), $column->getType());
        $this->assertTrue($column->isPrimaryKey());
        $this->assertFalse($column->isForeingKey());
        $this->assertEquals([
            'length' => $schemaAttribute->maxLength(),
            'notnull' => $schemaAttribute->isRequired(),
            'autoincrement' => $schemaAttribute->isAi(),
            'comment' => $schemaAttribute->name(),
        ], $column->getOptions());
    }

    public function testItDefinitionPk(): void
    {
        $schemaAttribute = new SchemaAttribute('bar', 'integer', [
            'fk' => 'baz',
        ]);

        $column = new Column($schemaAttribute);

        $this->assertEquals($schemaAttribute->name(), $column->getName());
        $this->assertEquals($schemaAttribute->dataType(), $column->getType());
        $this->assertFalse($column->isPrimaryKey());
        $this->assertTrue($column->isForeingKey());
        $this->assertEquals([
            'length' => $schemaAttribute->maxLength(),
            'notnull' => $schemaAttribute->isRequired(),
            'autoincrement' => $schemaAttribute->isAi(),
            'comment' => $schemaAttribute->name(),
        ], $column->getOptions());
    }
}
