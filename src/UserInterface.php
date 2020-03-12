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

interface UserInterface
{
    public function __construct(string $name, string $password, string $host, string $driver);

    public function setName(string $name): void;

    public function setPassword(string $password): void;

    public function setHost(string $host): void;

    public function setGrants(array $permissions, string $database, string $table): void;

    public function setDriver(string $driver): void;

    public function asCreate(): string;

    public function asDrop(): string;
}
