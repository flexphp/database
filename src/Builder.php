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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema as DBALSchema;
use Doctrine\DBAL\Schema\SchemaConfig as DBALSchemaConfig;
use FlexPHP\Database\Exception\DatabaseValidationException;
use FlexPHP\Database\Validations\NameDatabaseValidation;
use FlexPHP\Schema\SchemaInterface;

final class Builder
{
    public const PLATFORM_MYSQL = 'MySQL';

    public const PLATFORM_SQLSRV = 'SQLSrv';

    public const PLATFORM_SQLITE = 'SQLite';

    private string $platform;

    /**
     * @var AbstractPlatform
     */
    private $DBALPlatform;

    private ?\Doctrine\DBAL\Schema\Schema $DBALSchema = null;

    /**
     * @var array<int, string>
     */
    private array $databases = [];

    /**
     * @var array<int, string>
     */
    private array $users = [];

    /**
     * @var array<int, string>
     */
    private array $tables = [];

    /**
     * @var array<int, string>
     */
    private array $constraints = [];

    /**
     * @var array<int, string>
     */
    private array $primaryTables = [];

    /**
     * @var array<int, string>
     */
    private array $foreignTables = [];

    /**
     * @var array<string, string>
     */
    private array $platformSupport = [
        self::PLATFORM_MYSQL => 'MySQL57',
        self::PLATFORM_SQLSRV => 'SQLServer2012',
        self::PLATFORM_SQLITE => 'Sqlite',
    ];

    public function __construct(string $platform)
    {
        if (empty($this->platformSupport[$platform])) {
            throw new DatabaseValidationException(\sprintf(
                'Platform %s not supported, try: %s',
                $platform,
                \implode(', ', \array_keys($this->platformSupport))
            ));
        }

        $fqdnPlatform = \sprintf('\Doctrine\DBAL\Platforms\%sPlatform', $this->platformSupport[$platform]);

        $this->platform = $platform;
        $this->DBALPlatform = new $fqdnPlatform();
    }

    public function createDatabase(string $name): void
    {
        (new NameDatabaseValidation($name))->validate();

        if ($this->isSQLitePlatform()) {
            return;
        }

        $this->databases[] = $this->DBALPlatform->getCreateDatabaseSQL($name)
            . ' ' . $this->getCollateDatabase()
            . ';';
    }

    public function createDatabaseWithUse(string $name): void
    {
        $this->createDatabase($name);

        if ($this->isMySQLPlatform()) {
            $this->databases[] = "USE {$name};";
        }
    }

    public function createUser(
        string $name,
        string $password,
        string $host = '',
        array $permissions = [],
        string $database = '*',
        string $table = '*'
    ): void {
        if ($this->isSQLitePlatform()) {
            return;
        }

        $user = new User($name, $password, $host);
        $user->setPlatform($this->platform);

        $this->users[] = $user->toSqlCreate();

        if (\count($permissions) > 0) {
            $user->setGrants($permissions, $database, $table);
            $this->users[] = $user->toSqlPrivileges();
        }
    }

    public function createTable(SchemaInterface $schema): void
    {
        $table = new Table($schema);

        $DBALSchemaConfig = new DBALSchemaConfig();
        $DBALSchemaConfig->setDefaultTableOptions($table->getOptions());

        $this->DBALSchema = new DBALSchema([], [], $DBALSchemaConfig);

        $DBALTable = $this->DBALSchema->createTable($table->getName());

        $this->primaryTables[] = $table->getName();

        foreach ($table->getColumns() as $column) {
            $options = $column->getOptions();

            if ($this->isSQLitePlatform()) {
                unset($options['comment']);
            }

            $DBALTable->addColumn($column->getName(), $column->getType(), $options);

            if ($column->isPrimaryKey()) {
                $DBALTable->setPrimaryKey([$column->getName()]);
            }

            if ($column->isForeingKey()) {
                $fkRel = $schema->fkRelations()[$column->getName()];

                $DBALTable->addForeignKeyConstraint($fkRel['pkTable'], [$fkRel['pkId']], [$fkRel['fkId']]);

                $this->foreignTables[] = $fkRel['pkTable'];
            }
        }

        $sentences = $this->DBALSchema->toSql($this->DBALPlatform);

        $this->tables[] = $this->getTable($sentences);

        if (\count($sentences) > 1) {
            $this->constraints[] = $this->getConstraints(\array_slice($sentences, 1));
        }
    }

    public function toSql(): string
    {
        $this->validateLogic();

        $sql = [];
        $glue = \str_repeat("\n", 2);

        if (\count($this->databases) > 0) {
            $sql[] = \implode($glue, $this->databases);
        }

        if (\count($this->users) > 0) {
            $sql[] = \implode($glue, $this->users);
        }

        if (\count($this->tables) > 0) {
            $sql[] = \implode($glue, $this->tables);
        }

        if (\count($this->constraints) > 0) {
            $sql[] = \implode($glue, $this->constraints);
        }

        $plain = \implode($glue, $sql);

        if ($plain !== '') {
            return $plain . "\n";
        }

        return '';
    }

    private function getTable(array $sentences): string
    {
        return $this->getPrettyTable($sentences[0]) . ';';
    }

    private function getConstraints(array $sentences): string
    {
        return \implode(";\n\n", $sentences) . ';';
    }

    private function getCollateDatabase(): string
    {
        $collate = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci';

        if ($this->isSQLSrvPlatform()) {
            $collate = 'COLLATE latin1_general_100_ci_ai_sc';
        }

        return $collate;
    }

    private function isMySQLPlatform(): bool
    {
        return $this->platform === self::PLATFORM_MYSQL;
    }

    private function isSQLSrvPlatform(): bool
    {
        return $this->platform === self::PLATFORM_SQLSRV;
    }

    private function isSQLitePlatform(): bool
    {
        return $this->platform === self::PLATFORM_SQLITE;
    }

    private function getPrettyTable(string $sql): string
    {
        $tag = '<columns>';
        $regExpColumns = "/\((?$tag.*)\)/";
        $prettySql = $sql;

        \preg_match($regExpColumns, $sql, $matches);

        if (!empty($matches['columns'])) {
            $columns = $matches['columns'];
            $table = \str_replace($columns, "\n    $tag\n", $sql);

            $prettySql = \str_replace($tag, \str_replace(', ', ",\n    ", $columns), $table);
        }

        return $prettySql;
    }

    private function validateLogic(): void
    {
        $undefinedTables = \array_diff($this->foreignTables, $this->primaryTables);

        if (count($undefinedTables) > 0) {
            throw new DatabaseValidationException(
                'Tables in foreign key not found: ' . \implode(', ', $undefinedTables)
            );
        }
    }
}
