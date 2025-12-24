<?php
declare(strict_types=1);
namespace app\lib\annotation;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
class Message
{
    private string $message;
    private ?string $default = null;

    public function __construct(string $message, ?string $default = null)
    {
        $this->message = $message;
        $this->default = $default;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }
}
