<?php

declare(strict_types=1);

namespace Service\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

class Logger implements LoggerInterface, Stringable
{
    private array $logs = [];
    private LogSanitizer $sanitizer;

    public function __construct(LogSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message,$context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message,$context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message,$context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message,$context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message,$context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message,$context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message,$context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message,$context);
    }

    public function log($level, $message, array $context = []): void
    {
        $message = $this->sanitizer->sanitize($message);
        $this->logs[] = "[$level] $message " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function __toString(): string
    {
        return implode("\n", $this->logs);
    }
}
