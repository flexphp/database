<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Factories\User;

final class SQLiteUserFactory extends AbstractUserFactory
{
    public function asCreate(): string
    {
        return '';
    }

    public function asDrop(): string
    {
        return '';
    }

    public function asPrivileges(): string
    {
        return '';
    }
}
