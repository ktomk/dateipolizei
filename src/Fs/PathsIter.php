<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 09.08.17 17:51
 */

namespace Ktomk\DateiPolizei\Fs;

use AppendIterator;

/**
 * Class PathsIter
 *
 * INodeIter to iterate over multiple paths.
 *
 * @method INodeIter getInnerIterator()
 */
class PathsIter extends AppendIterator implements INodeIter
{
    /**
     * ManyIter constructor.
     * @param INodeIterFactory $factory
     * @param string[] ...$paths
     */
    public function __construct(INodeIterFactory $factory, string ...$paths)
    {
        parent::__construct();

        foreach ($paths as $path) {
            $iter = $factory->getIterator($path);
            $this->append($iter);
        }

        // rewind as otherwise per default it's on the last iterator appended
        $paths && $this->rewind();
    }

    /**
     * Path w/o basedir
     *
     * @return string
     */
    public function getSubPathname(): string
    {
        return $this->getInnerIterator()->getSubPathname();
    }

    public function current(): INode
    {
        return $this->getInnerIterator()->current();
    }
}
