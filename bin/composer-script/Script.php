<?php

declare(strict_types=1);

namespace Bolt\ComposerScripts;

use Composer\Script\Event;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

class Script
{
    /** @var SymfonyStyle */
    protected static $console;

    protected static function init(string $message = ''): void
    {
        $consoleFactory = new SymfonyStyleFactory();
        self::$console = $consoleFactory->create();

        self::$console->note($message);
    }

    protected static function getProjectFolder(Event $event): string
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

        return realpath($vendorDir . '/../');
    }

    /**
     * Execute a command in the CLI, as a separate process.
     */
    public static function run(string $command): int
    {
        // Depending on the context, we're using either Symfony/Process 2.8.52 (bundled with composer 2.1.x)
        // or Symfony/Process 5.3.x (if we're using our own). The signature of the Constructor changed
        // from: `public function __construct(string $commandline, …)`
        // to:   `public function __construct(array $command, …)`
        // We'll have to attempt one, and otherwise fall back to the other.

        try {
            $process = new Process($command);
        } catch (\TypeError $e) {
            $process = new Process([$command]);
        }

        return $process->run();
    }

    /**
     * Create SymfonyStyle object. Taken from Symplify (which we might not
     * have at our disposal inside a 'project' installation)
     */
    public static function createSymfonyStyle(): SymfonyStyle
    {
        // to prevent missing argv indexes
        if (! isset($_SERVER['argv'])) {
            $_SERVER['argv'] = [];
        }

        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();

        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        return new SymfonyStyle($argvInput, $consoleOutput);
    }
}
