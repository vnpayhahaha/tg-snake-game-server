<?php

return [
    'commands' => [
        // ... 其他命令 ...
        'cache:listeners' => app\command\CacheListenerCommand::class,
        'cache:clear-listeners' => app\command\CacheClearListenersCommand::class,
        'event:diagnose' => app\command\EventDiagnoseCommand::class,
        'cache:test-clear' => app\command\CacheCleanerCommand::class,
    ]
];
