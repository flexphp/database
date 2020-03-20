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

class Builder
{
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
    private $users = [];

    /**
     * @var array<int, string>
     */
    private $tables = [];

    /**
     * @var array<string, string>
     */
    private $platformSupport = [
        'MySQL' => 'MySQL57',
        'SQLSrv' => 'SQLServer2012',
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

        $this->DBALPlatform = new $fqdnPlatform();
        $this->DBALSchema = new DBALSchema();
    }

    // public function createUser(UserInterface $user): void
    // {
    //     $this->users[] = $user->toSqlCreate();
    // }

    public function createTable(TableInterface $table): void
    {
        $DBALSchemaConfig = new DBALSchemaConfig();
        $DBALSchemaConfig->setDefaultTableOptions($table->getOptions());

        $DBALTable = $this->DBALSchema->createTable($table->getName());
        $DBALTable->setSchemaConfig($DBALSchemaConfig);

        foreach ($table->getColumns() as $column) {
            $DBALTable->addColumn($column->getName(), $column->getType(), $column->getOptions());
        }

        $this->tables[] = $this->DBALSchema->toSql($this->DBALPlatform)[0] . ';';
    }

    public function toSql(): string
    {
        $sql = '';
        $glue = "\n";

        // if (\count($this->users) > 0) {
        //     $sql .= \implode($glue, $this->users);
        // }

        if (\count($this->tables) > 0) {
            $sql .= \implode($glue, $this->tables);
        }

        return $sql;
    }
}
