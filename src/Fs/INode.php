<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 22.07.17 23:33
 */

namespace Ktomk\DateiPolizei\Fs;

use SplFileInfo;

/**
 * Class INode
 *
 * The SplFileInfo as used in dateipolizei
 *
 * TODO(tk): INode::isLink() could handle Posix symlinks on windows (idea), e.g. in a git repo checked out on a windows system that has hard time so actually accept that Posix what not invented on Windows
 */
class INode extends SplFileInfo
{
}
