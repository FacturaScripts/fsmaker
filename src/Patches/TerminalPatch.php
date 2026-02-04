<?php
/**
 * Terminal patch for Laravel Prompts to handle stty errors gracefully
 * This fixes the "stty: invalid argument" error in non-standard terminal environments
 */

namespace Laravel\Prompts;

use ReflectionClass;
use RuntimeException;
use Symfony\Component\Console\Terminal as SymfonyTerminal;

/**
 * Overrides the Laravel Prompts Terminal class to handle stty errors gracefully
 */
class Terminal
{
    /**
     * The initial TTY mode.
     */
    protected ?string $initialTtyMode = null;

    /**
     * The Symfony Terminal instance.
     */
    protected SymfonyTerminal $terminal;

    /**
     * Whether stty is available on this system.
     */
    protected static ?bool $sttyAvailable = null;

    /**
     * Create a new Terminal instance.
     */
    public function __construct()
    {
        $this->terminal = new SymfonyTerminal;
    }

    /**
     * Check if stty is available on this system.
     */
    protected function hasStty(): bool
    {
        if (self::$sttyAvailable !== null) {
            return self::$sttyAvailable;
        }

        // On Windows, stty is usually not available unless using Git Bash/WSL
        if ('\\' === \DIRECTORY_SEPARATOR) {
            // Try to detect if stty exists
            $result = @shell_exec('stty 2>&1');
            self::$sttyAvailable = $result !== null && !str_contains(strtolower($result), 'not recognized');
            return self::$sttyAvailable;
        }

        // On Unix-like systems, assume stty is available
        self::$sttyAvailable = true;
        return true;
    }

    /**
     * Read a line from the terminal.
     */
    public function read(): string
    {
        $input = fread(STDIN, 1024);

        return $input !== false ? $input : '';
    }

    /**
     * Set the TTY mode.
     */
    public function setTty(string $mode): void
    {
        // Skip if stty is not available
        if (!$this->hasStty()) {
            return;
        }

        try {
            $this->initialTtyMode ??= $this->exec('stty -g');
            $this->exec("stty $mode");
        } catch (RuntimeException $e) {
            // Silently ignore stty errors in non-standard terminal environments
            // This allows prompts to work even when stty is not available
            // Mark stty as unavailable to avoid further attempts
            self::$sttyAvailable = false;
        }
    }

    /**
     * Restore the initial TTY mode.
     */
    public function restoreTty(): void
    {
        if (isset($this->initialTtyMode) && $this->hasStty()) {
            try {
                $this->exec("stty {$this->initialTtyMode}");
            } catch (RuntimeException $e) {
                // Silently ignore stty restore errors
            }

            $this->initialTtyMode = null;
        }
    }

    /**
     * Get the number of columns in the terminal.
     */
    public function cols(): int
    {
        return $this->terminal->getWidth();
    }

    /**
     * Get the number of lines in the terminal.
     */
    public function lines(): int
    {
        return $this->terminal->getHeight();
    }

    /**
     * (Re)initialize the terminal dimensions.
     */
    public function initDimensions(): void
    {
        (new ReflectionClass($this->terminal))
            ->getMethod('initDimensions')
            ->invoke($this->terminal);
    }

    /**
     * Exit the interactive session.
     */
    public function exit(): void
    {
        exit(1);
    }

    /**
     * Execute the given command and return the output.
     */
    protected function exec(string $command): string
    {
        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! $process) {
            throw new RuntimeException('Failed to create process.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0 || $stdout === false) {
            throw new RuntimeException(trim($stderr ?: "Unknown error (code: $code)"), $code);
        }

        return $stdout;
    }
}
