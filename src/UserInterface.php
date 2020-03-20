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
    public function __construct(string $name, string $password, string $host);

    public function setPlatform(string $platform): void;

    public function setGrant(string $permission, string $database = '*', string $table = '*'): void;

    public function setGrants(array $permissions, string $database = '*', string $table = '*'): void;

    public function toSqlCreate(): string;

    public function toSqlDrop(): string;

    public function toSqlPrivileges(): string;
}
