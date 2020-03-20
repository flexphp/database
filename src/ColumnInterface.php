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

interface ColumnInterface
{
    public function __construct(SchemaAttributeInterface $schemaAttribute);

    public function getName(): string;

    public function getType(): string;

    public function getOptions(): array;
}
