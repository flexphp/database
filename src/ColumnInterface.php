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

interface ColumnInterface
{
    public function __construct(string $name, string $dataType, array $options);

    public function setPlatform(string $platform): void;

    public function asAdd(): string;

    // public function asDrop(): string;

    // public function asAddIntable(string $table): string;

    // public function asDropIntable(string $table): string;
}
