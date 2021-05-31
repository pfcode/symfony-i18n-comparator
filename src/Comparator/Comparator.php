<?php


namespace Pfcode\I18nComparatorBundle\Comparator;


use ErrorException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class Comparator
{
    /** @var array[] */
    private $translationFiles = [];

    /** @var array|LoaderInterface[] */
    private $loaders;

    /**
     * Comparator constructor.
     * @param array|LoaderInterface[] $loaders
     */
    public function __construct(array $loaders)
    {
        $this->loaders = $loaders;
    }

    /**
     * @param string $filepath
     * @param string|null $domain
     * @param string|null $lang
     * @throws ErrorException
     */
    public function addTranslationFile(string $filepath, ?string $domain = null, ?string $lang = null): void
    {
        $parts = explode('.', basename($filepath));
        if (count($parts) !== 3) {
            throw new ErrorException("Method " . __METHOD__ . ' supports only files with names
                of pattern: domain.lang.ext');
        }

        if ($domain === null) {
            $domain = $parts[0];
        }

        if ($lang === null) {
            $lang = $parts[1];
        }

        $ext = $parts[2];
        if (!isset($this->loaders[$ext])) {
            throw new ErrorException("Extension '$ext' is not supported by any registered loader.");
        }

        $this->translationFiles[] = [
            'filepath' => $filepath,
            'domain' => $domain,
            'lang' => $lang,
            'extension' => $ext,
            'loader' => $this->loaders[$ext],
        ];
    }

    /**
     * @return string[]|array
     */
    public function getKnownDomains(): array
    {
        $domains = [];
        foreach ($this->translationFiles as $file) {
            if (!in_array($file['domain'], $domains, true)) {
                $domains[] = $file['domain'];
            }
        }

        return $domains;
    }

    /**
     * @return string[]|array
     */
    public function getKnownLanguages(): array
    {
        $languages = [];
        foreach ($this->translationFiles as $file) {
            if (!in_array($file['lang'], $languages, true)) {
                $languages[] = $file['lang'];
            }
        }

        return $languages;
    }

    /**
     * @return array[] Each element is a pair of domain (0) and lang (1) strings (non-associative array)
     */
    public function getKnownLanguageDomainPairs(): array
    {
        $pairs = [];

        foreach ($this->translationFiles as $file) {
            $pair = [$file['domain'], $file['lang']];
            foreach ($pairs as $_pair) {
                if ($_pair[0] === $pair[0] && $_pair[1] === $pair[1]) {
                    continue 2;
                }
            }

            $pairs[] = $pair;
        }

        return $pairs;
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessageArray(array $messages): array
    {
        $flat = [];
        foreach ($messages as $k => $v) {
            if (is_array($v)) {
                foreach ($this->flattenMessageArray($v) as $subK => $subV) {
                    $flat["$k.$subK"] = $subV;
                }
            } else {
                $flat[$k] = $v;
            }
        }

        return $flat;
    }

    /**
     * @param MessageCatalogue $catalogue
     * @return array
     */
    private function flattenMessageCatalogue(MessageCatalogue $catalogue): array
    {
        return $this->flattenMessageArray($catalogue->all());
    }

    /**
     * @param string $domain
     * @param string $lang
     * @return array
     */
    public function getConflicts(string $domain, string $lang): array
    {
        // Filter files to compare
        $comparedFiles = [];
        foreach ($this->translationFiles as $file) {
            if ($file['domain'] === $domain && $file['lang'] === $lang) {
                $file['translations'] = [];
                $comparedFiles[] = $file;
            }
        }

        // Extract translations from each file
        foreach ($comparedFiles as $k => $file) {
            /** @var LoaderInterface $loader */
            $loader = $file['loader'];
            $catalogue = $loader->load($file['filepath'], $file['lang'], $file['domain']);
            $comparedFiles[$k]['translations'] = $this->flattenMessageCatalogue($catalogue);
        }

        // Get all translations keys
        $keys = [];
        foreach ($comparedFiles as $file) {
            foreach (array_keys($file['translations']) as $k) {
                if (!in_array($k, $keys, true)) {
                    $keys[] = $k;
                }
            }
        }

        // Get conflicting entries
        $conflicts = [];
        foreach ($keys as $key) {
            $translations = [];
            foreach ($comparedFiles as $file) {
                $translations[$file['filepath']] = $file['translations'][$key] ?? null;
            }

            $prevTranslation = null;
            $isFirst = true;
            $isConflict = false;
            foreach ($translations as $translation) {
                if ($isFirst) {
                    $isFirst = false;
                    $prevTranslation = $translation;
                    continue;
                }

                if ($translation !== $prevTranslation) {
                    $isConflict = true;
                    break;
                }
            }

            if ($isConflict) {
                $conflicts[$key] = $translations;
            }
        }

        return $conflicts;
    }
}