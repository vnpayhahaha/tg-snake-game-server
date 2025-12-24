<?php

namespace app\model\enums;

use app\constants\RecycleBin;

enum RecycleBinEnabled: int
{

    case Restored = RecycleBin::ENABLED_YES;
    case Not_Restored = RecycleBin::ENABLED_NO;

    public function isRestored(): bool
    {
        return $this === self::Restored;
    }

    public function isNotRestored(): bool
    {
        return $this === self::Not_Restored;
    }
}
