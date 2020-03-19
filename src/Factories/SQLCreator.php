<?php declare(strict_types=1);
/*
 * This file is part of FlexPHP.
 *
 * (c) Freddie Gar <freddie.gar@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexPHP\Database\Factories;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class SQLCreator
{
    /**
     * @var AbstractPlatform
     */
    private $platform;

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

        $this->platform = new $fqdnPlatform();
    }

    public function getPlatform(): AbstractPlatform
    {
        return $this->platform;
    }
}
