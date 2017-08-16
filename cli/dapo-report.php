<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 22.07.17 14:50
 */

namespace Ktomk\DateiPolizei\Cmd;

use Ktomk\DateiPolizei\Fs\INode;
use Ktomk\DateiPolizei\Fs\INodeIter;
use Ktomk\DateiPolizei\PathIter;
use Ktomk\DateiPolizei\Paths;
use Ktomk\DateiPolizei\Report\INodeReport;
use Ktomk\DateiPolizei\ShowArray;
use Ktomk\DateiPolizei\String\CallbackMatcher;
use Ktomk\DateiPolizei\String\Matcher;
use Ktomk\DateiPolizei\String\PcreMatcher;
use Ktomk\DateiPolizei\String\PhpCsMatcher;

/* @var $args \Ktomk\DateiPolizei\DapoArgs */

$ignore = $args->getIgnore();

$show = ShowArray::create([
    'basename' => false,
    'dir' => true,
    'extension' => true,
    'file' => true,
    'link' => true,
    'summary' => true,
    'target' => true,
]);

$paths = new Paths();
$pcre = new PcreMatcher();
$phpCs = new PhpCsMatcher();

$tokens = $args->getTokens();
foreach ($tokens as $offset => [$type, $argument]) {
    /** @var string $argument */
    switch ($type) {
        case $tokens::T_OPTION:
            switch (true) {
                case preg_match('~^--show=(.*)$~', $argument, $matches):
                    $show->setAll(false)->setChars($matches[1]);
                    $tokens->consume();
                    break 2;

                case preg_match('~^--pcre=(.*)$~', $argument, $matches):
                    if (false === $pcre::isValid($matches[1])) {
                        return error_fatal(sprintf("invalid PCRE pattern: '%s'", $matches[1]));
                    }
                    $pcre->addPattern($matches[1]);
                    $tokens->consume();
                    break 2;

                case preg_match('~^--exclude-pcre=(.*)$~', $argument, $matches):
                    if (false === $pcre::isValid($matches[1])) {
                        return error_fatal(sprintf("invalid exclude PCRE pattern: '%s'", $matches[1]));
                    }
                    $pcre->addPattern($matches[1], true);
                    $tokens->consume();
                    break 2;

                case preg_match('~^--phpcs-exclude=(.*)$~', $argument, $matches):
                    if (false === $phpCs::isValid($matches[1])) {
                        return error_fatal(sprintf("invalid PHPCS exclude pattern: '%s'", $matches[1]));
                    }
                    $phpCs->addExcludeRule($matches[1]);
                    $tokens->consume();
                    break 2;

                case preg_match('~^--phpcs-include=(.*)$~', $argument, $matches):
                    if (false === $phpCs::isValid($matches[1])) {
                        return error_fatal(sprintf("invalid PHPCS include pattern: '%s'", $matches[1]));
                    }
                    $phpCs->addIncludeRule($matches[1]);
                    $tokens->consume();
                    break 2;

            }

            switch ($argument) {
                case '-h':
                case '--help':
                    report_help();
                    return 0;

                case '--no-ignore':
                    $ignore->clearPatterns();
                    $tokens->consume();
                    break;

                case '--no-show':
                    $show->setAll(false);
                    $tokens->consume();
                    break;

                default:
                    return error_usage(
                        sprintf("Unknown option: %s", $argument),
                        report_usage()
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

$report = new INodeReport();

$accept = new CallbackMatcher(function($subPath) use ($pcre, $phpCs) {
    return $pcre->match($subPath)
           and $phpCs->match($subPath);
});

$iter = PathIter::create(...$paths);
$iter->setIgnore($ignore);
$iter->visit(function (INode $node, INodeIter $iter) use ($report, $show, $accept) {
    $subPath = $iter->getSubPathname();
    if (!$accept->match($subPath)) {
        return null;
    }

    $report->add($node);

    if ($show['basename']) {
        return (
            !$show->isAny('dfl')
            || ($show['dir'] && $node->isDir())
            || ($show['file'] && $node->isFile())
            || ($show['link'] && $node->isLink())
        ) ? $node->getBasename() : null;
    }

    $dirFileLink = $show->areThese('dfl');
    if (!$dirFileLink && $show['extension']) {
        $extension = $node->getExtension();
        return strlen($extension) ? $extension : null;
    }

    if ($node->isLink()) {
        return $show['link'] ? (
            $subPath
            . ($show['target'] ? ' -> ' . $node->getLinkTarget() : '')
        ) : null;
    }
    if ($node->isDir()) {
        return $show['dir'] ? $subPath : null;
    }
    if ($node->isFile()) {
        return $show['file'] ? $subPath : null;
    }
    throw New \RuntimeException("internal state error");
});
$count = $iter->dump();

if ($show['summary']) {
    $count && fputs(STDOUT, "---\n");
    $report->dump();
}

return 0;

/**
 * @see main_usage()
 */
function report_usage(): string
{
    return <<<EOD
usage: dapo report [-h|--help] [--show=<deflst>] [--no-show] [--pcre=<pattern>]
                   [--exclude-pcre=<pattern>] [--phpcs-(exclude|include)=<pattern>]
                   [--] [<path>...]

EOD;
}

/**
 * @see main_help()
 */
function report_help()
{
    echo report_usage(), <<<EOD

    -h, --help            show help
    --show=<deflst>       list of what to show, by characters, empty to show nothing. default
                          is to show all 'deflst', can by any combination in any order of:
                            b - basenames; d - directories; e - extension; f - files; l - links;
                            s - summary; t - link targets
    --no-show             show nothing
    --pcre=<pattern>      filter all paths (relative/absolute to  <path>) based on PCRE
                          <pattern> which includes delimiters and modifiers
    --exclude-pcre=<pattern>
                          filter all paths not matching PCRE <pattern>, exclusion is additional
                          to inclusion with --pcre=<pattern>.
    --phpcs-include=<pattern>
    --phpcs-exclude=<pattern>
                          include or exclude paths by PHP_CodeSniffer <pattern>, inclusion
                          overrides exclusion.
    [<path>...]           path(s) to report on; if no <path> is given, the current directory
                          will be used


EOD;
}
