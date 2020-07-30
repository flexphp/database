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
use FlexPHP\Schema\SchemaInterface;

final class Builder
{
    public const PLATFORM_MYSQL = 'MySQL';

    public const PLATFORM_SQLSRV = 'SQLSrv';

    /**
     * @var string
     */
    private $platform;

    /**
     * @var AbstractPlatform
     */
    private $DBALPlatform;

    /**
     * @var DBALSchema
     */
    private $DBALSchema;

    /**
     * @var array<int, string>
     */
    private $databases = [];

    /**
     * @var array<int, string>
     */
    private $users = [];

    /**
     * @var array<int, string>
     */
    private $tables = [];

    /**
     * @var array<int, string>
     */
    private $constraints = [];

    /**
     * @var array<string, string>
     */
    private $platformSupport = [
        self::PLATFORM_MYSQL => 'MySQL57',
        self::PLATFORM_SQLSRV => 'SQLServer2012',
    ];

    public function __construct(string $platform)
    {
        if (empty($this->platformSupport[$platform])) {
            throw new \InvalidArgumentException(\sprintf(
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
        $this->databases[] = $this->DBALPlatform->getCreateDatabaseSQL($name)
            . ' ' . $this->getCollateDatabase()
            . ';';
    }

    public function createUser(string $name, string $password, string $host = ''): void
    {
        $user = new User($name, $password, $host);
        $user->setPlatform($this->platform);

        $this->users[] = $user->toSqlCreate();
    }

    public function createTable(SchemaInterface $schema): void
    {
        $table = new Table($schema);

        $DBALSchemaConfig = new DBALSchemaConfig();
        $DBALSchemaConfig->setDefaultTableOptions($table->getOptions());

        $this->DBALSchema = new DBALSchema([], [], $DBALSchemaConfig);

        $DBALTable = $this->DBALSchema->createTable($table->getName());

        foreach ($table->getColumns() as $column) {
            $DBALTable->addColumn($column->getName(), $column->getType(), $column->getOptions());

            if ($column->isPrimaryKey()) {
                $DBALTable->setPrimaryKey([$column->getName()]);
            }

            if ($column->isForeingKey()) {
                $fkRel = $schema->fkRelations()[$column->getName()];

                $DBALTable->addForeignKeyConstraint($fkRel['pkTable'], [$fkRel['pkId']], [$fkRel['fkId']]);
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
        $sql = [];
        $glue = \str_repeat("\n", 2);

        if (\count($this->databases)) {
            $sql[] = \implode($glue, $this->databases);
        }

        if (\count($this->users)) {
            $sql[] = \implode($glue, $this->users);
        }

        if (\count($this->tables)) {
            $sql[] = \implode($glue, $this->tables);
        }

        if (\count($this->constraints)) {
            $sql[] = \implode($glue, $this->constraints);
        }

        return \implode($glue, $sql);
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

    private function isSQLSrvPlatform(): bool
    {
        return $this->platform === self::PLATFORM_SQLSRV;
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
}
