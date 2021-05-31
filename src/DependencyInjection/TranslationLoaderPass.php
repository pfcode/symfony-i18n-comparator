<?php


namespace Pfcode\I18nComparatorBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TranslationLoaderPass implements CompilerPassInterface
{
    private const COMPARATOR_FACTORY_ID = 'pfcode_i18n_comparator_bundle.comparator.comparator_factory';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::COMPARATOR_FACTORY_ID)) {
            return;
        }

        $definition = $container->findDefinition(self::COMPARATOR_FACTORY_ID);
        $taggedServices = $container->findTaggedServiceIds('translation.loader', true);
        foreach ($taggedServices as $id => $attributes) {
            $loaderReference = new Reference($id);
            $formats = [$attributes[0]['alias']];
            if (isset($attributes[0]['legacy-alias'])) {
                $formats[] = $attributes[0]['legacy-alias'];
            }

            foreach ($formats as $format) {
                $definition->addMethodCall('addLoader', [$format, $loaderReference]);
            }
        }
    }
}