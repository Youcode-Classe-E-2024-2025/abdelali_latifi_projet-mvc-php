<?php

namespace App\Core;

class Logger {
    private const LOG_LEVELS = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    private string $logFile;
    
    public function __construct(string $logFile = '') {
        $this->logFile = $logFile ?? __DIR__ . '/../../logs/app.log';
    }
    
    public function log(string $level, string $message, array $context = []): void {
        if (!isset(self::LOG_LEVELS[$level])) {
            throw new \InvalidArgumentException('Invalid log level');
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context) : '';
        $logMessage = "[$timestamp] [$level] $message $contextJson" . PHP_EOL;
        
        // error_log($logMessage, 3, $this->logFile);
    }
    
    public function debug(string $message, array $context = []): void {
        $this->log('DEBUG', $message, $context);
    }
    
    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }
    
    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }
    
    public function critical(string $message, array $context = []): void {
        $this->log('CRITICAL', $message, $context);
    }
}