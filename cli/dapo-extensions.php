<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 02.08.17 22:05
 */

namespace Ktomk\DateiPolizei\Cmd;

use Ktomk\DateiPolizei\Fs\INode;
use Ktomk\DateiPolizei\Fs\INodeIter;
use Ktomk\DateiPolizei\PathIter;
use Ktomk\DateiPolizei\Paths;
use Ktomk\DateiPolizei\Report\ExtensionReport;
use Ktomk\DateiPolizei\String\Matcher\InPathMatcher;

/* @var $args \Ktomk\DateiPolizei\DapoArgs */

$ignore = $args->getIgnore();

$paths = new Paths();

$exclude = new InPathMatcher();
// FIXME(tk): these are superfluous but the matcher plus the functionality is nice
// TODO(tk): patterns are used for ignore then (now), consider patterns for that matcher, too ?!
// TODO(tk): (cont.) consider a read of .gitignore patterns -> factor out a matcher for these
$development = [
];
$exclude->addSegments(...array_values($development));

$includeDirectories = false;
$showPaths = false;
$noExtension = false;
$showSummary = true;

$tokens = $args->getTokens();
foreach ($tokens as $offset => [$type, $argument]) {
    /** @var string $argument */
    switch ($type) {
        case $tokens::T_OPTION:

            switch ($argument) {
                case '-h':
                case '--help':
                    extensions_help();
                    return 0;

                case '--no-ignore':
                    $args->getIgnore()->clearPatterns();
                    $tokens->consume();
                    break;

                case '--show-paths':
                    $showPaths = true;
                    $tokens->consume();
                    break;

                case '--no-show-paths':
                    $showPaths = false;
                    $tokens->consume();
                    break;

                case '--no-ext';
                    $noExtension = true;
                    $tokens->consume();
                    break;

                case '--no-show-summary':
                    $showSummary = false;
                    $tokens->consume();
                    break;

                case '--show-summary':
                    $showSummary = true;
                    $tokens->consume();
                    break;

                case '--include-dir':
                case '--include-dirs':
                    $tokens->consume();
                    $includeDirectories = true;
                    break;

                case '--exclude-path':
                    $tokens->consumeNext();
                    if (null === $array = $tokens->consumeArguments()) {
                        echo implode("\n", $exclude->getSegments()), "\n";
                        return 0;
                    }
                    $exclude->addSegments(... $array);
                    break;

                default:
                    return error_usage(
                        sprintf("Unknown option: %s", $argument),
                        extensions_usage()
                    );
            }
            break;

        default:
            $paths->set(... (array)$tokens->consumeRemainingArguments());
    }
}

if ($buffer = $paths->hasUnreadable()) {
    return error_fatal($buffer);
}

$report = new ExtensionReport();

$accept = function ($subPath) use ($exclude) {

    if ($exclude->match($subPath)) {
        return false;
    }

    return true;
};


$iter = PathIter::create(...$paths);
$iter->setIgnore($ignore);
$iter->visit(
    function (INode $node, INodeIter $iter)
    use ($report, $accept, $includeDirectories, $showPaths, $noExtension) {
    if (!$includeDirectories && $node->isDir()) {
        return null;
    }

    $subPath = $iter->getSubPathname();
    if (!$accept($subPath)) {
        return null;
    }

    $report->add($node);

    if ($noExtension and $node->getExtension() === "") {
        return $subPath;
    }

    return $showPaths ? $subPath : null;
});

$count = $iter->dump();

if ($showSummary) {
    $count && fputs(STDOUT, "---\n");
    $report->dump();
}

return 0;

/**
 * @see main_usage()
 */
function extensions_usage(): string
{
    return <<<EOD
usage: dapo extensions [-h|--help] [--exclude-path <path>...] [--[no-]show-paths] [--[no-]show-summary]
                       [--no-ext] [--] [<path>...]

EOD;
}

/**
 * @see main_help()
 */
function extensions_help()
{
    echo extensions_usage(), <<<EOD

    -h, --help            show help
    --exclude-path <path> exclude <path> segments, one or more paths can be given. if no path 
                          is given, the current exclude path segments (if any) are listed and 
                          the command exits
    --[no-]show-paths     do (not) show paths, paths are not shown by default
    --[no-]show-summary   do (not) show summary, by default summary is shown
    --no-ext              show paths of files with no extension
    [<path>]              path(s) to report on; if no <path> is given, the current directory
                          will be used


EOD;
}
