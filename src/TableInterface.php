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

interface TableInterface
{
    public function __construct(SchemaInterface $schema);

    public function getName(): string;

    /**
     * @return array<int, ColumnInterface>
     */
    public function getColumns(): array;

    public function getOptions(): array;
}
