<?php


namespace Pfcode\I18nComparatorBundle\Comparator;

use Symfony\Component\Translation\Loader\LoaderInterface;

class ComparatorFactory
{
    /** @var array|LoaderInterface[] */
    private $loaders = [];

    /**
     * @param string $format
     * @param LoaderInterface $loader
     */
    public function addLoader(string $format, LoaderInterface $loader): void
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * @return Comparator
     */
    public function newComparator(): Comparator
    {
        return new Comparator($this->loaders);
    }
}