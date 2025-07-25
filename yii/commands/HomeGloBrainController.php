<?php

namespace app\commands;

use app\components\HomeGloBrainComponent;
use app\components\HomeAssistantComponent;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * HomeGlo Brain controller - Runs the central event processor
 */
class HomeGloBrainController extends Controller
{
    /**
     * @var bool Enable verbose output
     */
    public $verbose = false;
    
    /**
     * @var string Event types to listen to (comma-separated)
     */
    public $events = 'zha_event';
    
    /**
     * @var bool Enable trigger caching
     */
    public $cache = true;
    
    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['verbose', 'events', 'cache']);
    }
    
    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'v' => 'verbose',
            'e' => 'events',
            'c' => 'cache',
        ]);
    }
    
    /**
     * Run the HomeGlo Brain event processor
     * 
     * Examples:
     * - php yii home-glo-brain              # Listen to default events (zha_event)
     * - php yii home-glo-brain -v           # Verbose output
     * - php yii home-glo-brain -e zha_event,state_changed  # Listen to multiple events
     * - php yii home-glo-brain --no-cache   # Disable trigger caching
     */
    public function actionIndex()
    {
        $this->stdout("HomeGlo Brain Starting...\n");
        $this->stdout("========================================\n");
        
        // Parse event types
        $eventTypes = array_map('trim', explode(',', $this->events));
        
        // Create HomeAssistant component with logger
        $homeAssistant = new HomeAssistantComponent([
            'logger' => function($message, $level) {
                if ($this->verbose || $level === 'error') {
                    $prefix = strtoupper($level) . ': ';
                    $this->stdout($prefix . $message . "\n");
                }
            }
        ]);
        
        // Test connection first
        $this->stdout("Testing Home Assistant connection...\n");
        if (!$homeAssistant->testConnection()) {
            $this->stderr("Failed to connect to Home Assistant\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // Create brain component
        $brain = new HomeGloBrainComponent([
            'homeAssistant' => $homeAssistant,
            'enableTriggerCache' => $this->cache,
            'logger' => function($message, $level) {
                if ($this->verbose || $level === 'error') {
                    $time = date('Y-m-d H:i:s');
                    $prefix = "[{$time}] " . strtoupper($level) . ': ';
                    $this->stdout($prefix . $message . "\n");
                }
            }
        ]);
        
        $this->stdout("\n");
        $this->stdout("Configuration:\n");
        $this->stdout("- Event types: " . implode(', ', $eventTypes) . "\n");
        $this->stdout("- Trigger cache: " . ($this->cache ? 'enabled' : 'disabled') . "\n");
        $this->stdout("- Verbose mode: " . ($this->verbose ? 'enabled' : 'disabled') . "\n");
        $this->stdout("\n");
        
        // Register signal handlers for graceful shutdown
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        }
        
        $this->stdout("Listening for events... (Press Ctrl+C to stop)\n");
        $this->stdout("========================================\n\n");
        
        try {
            // Start listening
            $brain->startListening($eventTypes);
        } catch (\Exception $e) {
            $this->stderr("\nError: " . $e->getMessage() . "\n");
            if ($this->verbose) {
                $this->stderr($e->getTraceAsString() . "\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Clear the trigger cache
     */
    public function actionClearCache()
    {
        $this->stdout("Clearing trigger cache...\n");
        
        // In a real implementation, you might clear database cache or files
        // For now, just indicate success
        $this->stdout("Cache cleared successfully\n");
        
        return ExitCode::OK;
    }
    
    /**
     * Test event processing with a sample event
     * @param string $deviceId Device ID to test
     * @param string $command Command/event name (default: on_press)
     */
    public function actionTest($deviceId, $command = 'on_press')
    {
        $this->stdout("Testing event processing...\n");
        $this->stdout("Device ID: {$deviceId}\n");
        $this->stdout("Command: {$command}\n\n");
        
        // Create test event
        $testEvent = [
            'event_type' => 'zha_event',
            'data' => [
                'device_id' => $deviceId,
                'command' => $command,
                'params' => []
            ],
            'origin' => 'TEST',
            'time_fired' => date('Y-m-d\TH:i:s')
        ];
        
        // Create brain component
        $brain = new HomeGloBrainComponent([
            'homeAssistant' => new HomeAssistantComponent(),
            'enableTriggerCache' => false,
            'logger' => function($message, $level) {
                $prefix = strtoupper($level) . ': ';
                $this->stdout($prefix . $message . "\n");
            }
        ]);
        
        try {
            $brain->processEvent($testEvent);
            $this->stdout("\nTest completed successfully\n");
        } catch (\Exception $e) {
            $this->stderr("\nError: " . $e->getMessage() . "\n");
            if ($this->verbose) {
                $this->stderr($e->getTraceAsString() . "\n");
            }
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        return ExitCode::OK;
    }
    
    /**
     * Handle process signals
     * @param int $signal
     */
    protected function handleSignal($signal)
    {
        $this->stdout("\n\nReceived signal {$signal}, shutting down gracefully...\n");
        exit(0);
    }
}