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

use Doctrine\DBAL\Types\Type as DBALType;
use FlexPHP\Schema\SchemaAttribute;

class Column implements ColumnInterface
{
    /**
     * @var SchemaAttribute
     */
    private $schemaAttribute;

    public function __construct(SchemaAttribute $schemaAttribute)
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

    public function getOptions(): array
    {
        return [
            'length' => $this->schemaAttribute->maxLength(),
            'notnull' => $this->schemaAttribute->isRequired(),
            // 'autoincrement' => $this->schemaAttribute->name(),
            'comment' => $this->schemaAttribute->name(),
            // 'precision' => $this->schemaAttribute->name(),
            // 'scale' => $this->schemaAttribute->name(),
            // 'unsigned' => $this->schemaAttribute->name(),
            // 'fixed' => $this->schemaAttribute->name(),
            // 'default' => $this->schemaAttribute->name(),
        ];
    }
}
