<?php declare(strict_types=1);

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\CliTest;
use PHPUnit\Framework\TestCase;

/**
 * Class PhpRunner
 *
 * Helper class to run a PHP script in tests
 */
class PhpRunner
{
    /**
     * @var null|array
     */
    private $last;

    /**
     * @var TestCase
     */
    private $test;

    /**
     * @var string
     */
    private $cmd;

    public function __construct(TestCase $test, string $cmd)
    {
        $this->test = $test;
        $this->cmd = $cmd;
    }

    public function assertOk(array $args = null, string $message = '')
    {
        $this->assertStatus(0, $args, $message);
    }

    public function assertStatus($expected, array $args = null, string $message = '')
    {
        $run = $this->runArgs($args);
        if (!strlen($message)) {
            $message .= sprintf(
                "Status %d expected, got %d\n\n" .
                "standard output:\n%s\n\n" .
                "standard error:\n%s",
                $expected,
                ...$run
            );
        }

        $this->test->assertEquals(
            $expected,
            $this->getStatus(),
            $message
        );
    }

    private function runArgs(?array $args): array
    {
        if (null === ($args ?? $this->last)) {
            $args = [];
        }

        return null === $args
            ? $this->last
            : $this->run($this->cmd, ...$args);
    }

    public function run($cmd, ...$args): array
    {
        $command = sprintf(
            PHP_BINARY . ' %s -f %s -- %s',
            XDebugHelper::args(),
            escapeshellarg($cmd),
            implode(" ", array_map('escapeshellarg', $args))
        );

        if ($cmd === '') {
            $command = '';
        }

        return $this->runCommand($command);
    }

    private function runCommand(string $command)
    {
        if ('' === $command) {
            return $this->last = [0, '', '', $command];
        }

        $cwd = __DIR__ . '/../..';

        $descriptors = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $handle = proc_open($command, $descriptors, $pipes, $cwd);
        if (!is_resource($handle)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Failed to open a new process");
            // @codeCoverageIgnoreEnd
        }

        $outputStandard = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $outputError = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $status = proc_close($handle);
        if (false === $status) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Failed to close process");
            // @codeCoverageIgnoreEnd
        }

        return $this->last = [
            $status,
            $outputStandard,
            $outputError,
            $command,
        ];
    }

    /**
     * Status of last run, fails if not run yet
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->last[0];
    }
}
