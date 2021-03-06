<?php


namespace Ag84ark\LaravelMinimalTranslation\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Arr;

class Scanner
{
    private $disk;

    private $scanPaths;

    private $translationMethods;

    private $matchingPattern;
    /**
     * @var string
     */
    private $stringPattern;

    public function __construct(Filesystem $disk, $scanPaths, $translationMethods)
    {
        $this->disk = $disk;
        $this->scanPaths = $scanPaths;
        $this->translationMethods = $translationMethods;
        $this->setRegex();
    }

    /**
     * Scan all the files in the provided $scanPath for translations.
     *
     * @return array
     */
    public function findTranslations(): array
    {
        $results = ['single' => [], 'group' => []];

        foreach ($this->disk->allFiles($this->scanPaths) as $file) {
            if (preg_match_all("/$this->matchingPattern/iU", $file->getContents(), $matches)) {

                foreach ($matches[2] as $key) {
                    if (preg_match("/(^[a-zA-Z0-9:_-]+([.][^\1)\ ]+)+$)/iU", $key, $arrayMatches)) {
                        [$group, $k] = explode('.', $arrayMatches[0], 2);
                        $results['group'][$group][$k] = '';
                        continue;
                    }

                    $results['single']['single'][$key] = '';
                }
            }

            if (config('laravel-minimal-translation.enable_second_search')) {
                if (preg_match_all("/$this->stringPattern/siU", $file->getContents(), $matches)) {

                    foreach ($matches['string'] as $key) {
                        if (preg_match("/(^[a-zA-Z0-9_-]+([.][^\1)\ ]+)+$)/siU", $key, $groupMatches)) {
                            // group{.group}.key format, already in $groupKeys but also matched here
                            // do nothing, it has to be treated as a group
                            continue;
                        }
                        $results['single']['single'][$key] = '';
                        // dump("Possible key: $key");
                        //TODO: This can probably be done in the regex, but I couldn't do it.
                        //skip keys which contain namespacing characters, unless they also contain a
                        //space, which makes it JSON.
                        //if (! (Str::contains($key, '::') && Str::contains($key, '.'))
                        //    || Str::contains($key, ' ')) {
                        //    $stringKeys[] = $key;
                        //}
                    }
                }
            }
        }

        return $results;
    }

    public function textExistsInFiles($text = 'group.key', bool $showFiles = false) : bool
    {
        $finder = new Finder();
        $finder->in($this->scanPaths)
            ->exclude('storage')
            ->exclude('vendor')
            ->exclude('lang')
            ->name('*.php')
            ->name('*.twig')
            ->name('*.vue')
            ->name('*.js')
            ->files()
            ->contains($text);

        if ($showFiles) {
            foreach ($finder as $file) {
                if (preg_match_all("/$this->matchingPattern/iU", $file->getContents(), $matches)) {
                    foreach ($matches[2] as $key) {
                        if ($key === $text) {
                            dump("`$key` found in " . $file->getPathname());
                        }
                    }
                }
            }
        }

        return (bool) $finder->count();
    }

    public function getTranslationFiles($text = 'group.key') : array
    {
        $result = [];
        $finder = new Finder();
        $finder->in($this->scanPaths)
            ->exclude('storage')
            ->exclude('vendor')
            ->exclude('lang')
            ->name('*.php')
            ->name('*.twig')
            ->name('*.vue')
            ->name('*.js')
            ->files()
            ->contains($text);

        foreach ($finder as $file) {
            if (preg_match_all("/$this->matchingPattern/iU", $file->getContents(), $matches)) {
                foreach ($matches[2] as $key) {
                    if ($key === $text) {
                        $result[] =("`$key` found in " . $file->getPathname());
                    }
                }
            }
        }


        return $result;
    }

    private function setRegex(): void
    {
        // This has been derived from a combination of the following:
        // * Laravel Language Manager GUI from Mohamed Said (https://github.com/themsaid/laravel-langman-gui)
        // * Laravel 5 Translation Manager from Barry vd. Heuvel (https://github.com/barryvdh/laravel-translation-manager)
        $this->matchingPattern =
            '[^\w]' . // Must not start with any alphanum or _
            '(?<!->)' . // Must not start with ->
            '(' . implode('|', $this->translationMethods) . ')' . // Must start with one of the functions
            "\(" . // Match opening parentheses
            "\s*" .                               // Match spaces before  if exists
            "[\'\"]" . // Match " or '
            '(' . // Start a new group to match:
            '.+' . // Must start with group
            ')' . // Close group
            "[\'\"]" . // Closing quote
            "\s*" .                              // Match spaces after if exists
            "[\),]";  // Close parentheses or new parameter

        $this->stringPattern =
            "[^\w]" .                                     // Must not have an alphanum before real method
            '(' . implode('|', $this->translationMethods) . ')' .             // Must start with one of the functions
            "\(\s*" .                                       // Match opening parenthesis
            "(?P<quote>['\"])" .                            // Match " or ' and store in {quote}
            "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)" . // Match any string that can be {quote} escaped
            "\k{quote}" .                                   // Match " or ' previously matched
            "\s*[\),]";                                    // Close parentheses or new parameter
    }
}
