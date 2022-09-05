<?php

namespace Spy\TimelineBundle\Driver\ORM;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Spy\Timeline\ResultBuilder\Pager\PagerInterface;
use Traversable;

class Pager implements PagerInterface, \IteratorAggregate, \Countable, \ArrayAccess
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var integer
     */
    protected $lastPage;

    /**
     * @var integer
     */
    protected $page;

    /**
     * @var integer
     */
    protected $nbResults;

    /**
     * {@inheritdoc}
     */
    public function paginate($target, $page = 1, $limit = 10)
    {
        if (!$target instanceof DoctrineQueryBuilder) {
            throw new \Exception('Not supported yet');
        }

        if ($limit) {
            $offset = ($page - 1) * (int)$limit;

            $target
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $paginator = new Paginator($target, true);
        $this->page = $page;
        $this->items = (array)$paginator->getIterator();
        $this->nbResults = count($paginator);
        $this->lastPage = intval(ceil($this->nbResults / $limit));

        return $this;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function haveToPaginate(): bool
    {
        return $this->getLastPage() > 1;
    }

    public function getNbResults(): int
    {
        return $this->nbResults;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetSet(mixed $offset, mixed $value):void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }
}
