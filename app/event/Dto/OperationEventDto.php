<?php

namespace app\event\Dto;

class OperationEventDto
{
    protected array $requestInfo;

    public function __construct(array $requestInfo)
    {
        $this->requestInfo = $requestInfo;
    }

    public function getRequestInfo(): array
    {
        return $this->requestInfo;
    }
}
