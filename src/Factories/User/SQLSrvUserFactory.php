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

class SQLSrvUserFactory extends AbstractUserFactory
{
    public const MAPPING_PERMISSION = [
        'ALL PRIVILEGES' => 'ALL',
        'CREATE' => 'CREATE',
        'DROP' => 'CONTROL',
        'DELETE' => 'DELETE',
        'INSERT' => 'INSERT',
        'SELECT' => 'SELECT',
        'UPDATE' => 'UPDATE',
        'GRANT OPTION' => 'GRANT',
    ];

    public function asCreate(): string
    {
        return \sprintf(<<<T
CREATE LOGIN %1\$s WITH PASSWORD = '%2\$s';
GO
CREATE USER %1\$s FOR LOGIN %1\$s;
GO
T, $this->name, $this->password);
    }

    public function asDrop(): string
    {
        return \sprintf(<<<T
DROP USER %s;
GO
T, $this->name);
    }

    public function asPrivileges(): string
    {
        $privileges = [];

        foreach ($this->permissions as $permission) {
            $perm = $this->getPermission($permission['permission']);
            $database = $this->getDatabase($permission['database']);
            $table = $this->getTable($permission['table']);
            $scope = $this->getScope($database, $table);

            $privileges[] = empty($scope)
                ? $this->getTemplate($perm)
                : $this->getTemplateScope($perm, $scope);
        }

        return \implode("\n", $privileges);
    }

    private function getPermission(string $permission): string
    {
        return $this::MAPPING_PERMISSION[$permission] ?? '';
    }

    private function getDatabase(string $database): string
    {
        if (empty($database) || $database === '*') {
            return '';
        }

        return $database;
    }

    private function getTable(string $table): string
    {
        if (empty($table) || $table === '*') {
            return '';
        }

        return $table;
    }

    private function getScope(string $database, string $table): string
    {
        $scope = '';

        if (!empty($database)) {
            $scope .= $database;
        }

        if (!empty($table)) {
            $scope .= '.' . $table;
        }

        if (!empty($scope)) {
            $scope = 'ON ' . $scope;
        }

        return $scope;
    }

    private function getTemplate(string $permission): string
    {
        return \sprintf(<<<T
GRANT %s TO %s;
GO
T,
            $permission,
            $this->name
        );
    }

    private function getTemplateScope(string $permission, string $scope)
    {
        return \sprintf(<<<T
GRANT %s %s TO %s;
GO
T,
            $permission,
            $scope,
            $this->name
        );
    }

}
