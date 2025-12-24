<?php

namespace app\repository;

use app\model\ModelTelegramCommandMessageRecord;
use DI\Attribute\Inject;

class TelegramCommandMessageRecordRepository extends IRepository
{
    #[Inject]
    protected ModelTelegramCommandMessageRecord $model;
}