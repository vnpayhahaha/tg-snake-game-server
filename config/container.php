<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

$builder = new \DI\ContainerBuilder();
$builder->useAttributes(true);

// 启用 PHP 8 属性
$builder->enableCompilation(ini_get('open_basedir') ? null : __DIR__ . '/../var/cache');
$builder->addDefinitions(config('dependence', []));
$builder->useAutowiring(true);
return $builder->build();
