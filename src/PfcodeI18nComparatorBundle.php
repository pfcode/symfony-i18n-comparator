<?php


namespace Pfcode\I18nComparatorBundle;

use Pfcode\I18nComparatorBundle\DependencyInjection\PfcodeI18nComparatorExtension;
use Pfcode\I18nComparatorBundle\DependencyInjection\TranslationLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PfcodeI18nComparatorBundle extends Bundle
{
    /**
     * @return PfcodeI18nComparatorExtension|ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new PfcodeI18nComparatorExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TranslationLoaderPass());
    }
}