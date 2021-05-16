<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database;

use FlexPHP\Schema\SchemaAttributeInterface;

final class Column implements ColumnInterface
{
    private \FlexPHP\Schema\SchemaAttributeInterface $schemaAttribute;

    public function __construct(SchemaAttributeInterface $schemaAttribute)
    {
        $this->schemaAttribute = $schemaAttribute;
    }

    public function getName(): string
    {
        return $this->schemaAttribute->name();
    }

    public function getType(): string
    {
        return $this->schemaAttribute->dataType();
    }

    public function isPrimaryKey(): bool
    {
        return $this->schemaAttribute->isPk();
    }

    public function isForeingKey(): bool
    {
        return $this->schemaAttribute->isFk();
    }

    public function getOptions(): array
    {
        return [
            'length' => $this->schemaAttribute->maxLength(),
            'notnull' => $this->schemaAttribute->isRequired(),
            'autoincrement' => $this->schemaAttribute->isAi(),
            'comment' => $this->schemaAttribute->name(),
            'default' => $this->schemaAttribute->default(),
            // 'precision' => $this->schemaAttribute->name(),
            // 'scale' => $this->schemaAttribute->name(),
            // 'unsigned' => $this->schemaAttribute->name(),
            // 'fixed' => $this->schemaAttribute->name(),
        ];
    }
}
