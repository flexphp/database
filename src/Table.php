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

use FlexPHP\Schema\SchemaInterface;

class Table implements TableInterface
{
    /**
     * @var SchemaInterface
     */
    private $schema;

    public function __construct(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    public function getName(): string
    {
        return $this->schema->name();
    }

    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->schema->attributes() as $attribute) {
            $columns[] = new Column($attribute);
        }

        return $columns;
    }

    public function getOptions(): array
    {
        return [
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
        ];
    }
}
