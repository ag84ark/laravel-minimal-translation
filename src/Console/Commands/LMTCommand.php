<?php


namespace Ag84ark\LaravelMinimalTranslation\Console\Commands;

use Ag84ark\LaravelMinimalTranslation\JsonFileManager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LMTCommand extends Command
{
    protected $signature = 'lmt:scan-i18n
                            {--find= : Find a translate key and display files where was found (case-sensitive)}
                            {--show-old : Prints the list of the keys not found at the end }
                            {--ignore : Write base file even if duplicates due to case-sensitive were found}
                            {--remove-old : Remove texts that were not found during the search}
                            {--display : Display only the found i18n texts, no write to base file}';

    protected $description = 'This will scan resources\view files and app folders for translations and add them to base JSON file';

    /** @var JsonFileManager */
    private $jsonFileManager;

    private $removeOld = false;

    private $showOld = false;

    private $display = false;

    private $ignore = false;

    private $findKey;

    private $scanPaths = [];

    private $translationMethods = [];

    private $baseTranslations = [];

    private $foundTranslations = [];

    private $duplicatesFromCaseSensitive = [];

    public function handle(): void
    {
        $this->init();

        if ($this->findKey) {
            dump($this->searchForKey());
            return;
        }

        $this->getTranslations();

        $singleTranslations = $this->foundTranslations['single']['single'] ?? [];
        if ($this->display) {
            dump($singleTranslations);
            return;
        }

        $this->addToBaseTranslations();

        if (!$this->ignore && count($this->duplicatesFromCaseSensitive)) {
            $this->warn("Duplicates where found");
            dump($this->duplicatesFromCaseSensitive);
            $this->error("Base file was not updated");
            return;
        }

        $this->writeBaseTranslation();

        $this->info("\nFound  " . count($singleTranslations)
            . " translation keys, and base has now " . count($this->baseTranslations) . " keys");

        if ($this->showOld && count($singleTranslations) !== count($this->baseTranslations)) {
            $this->info("\nTranslations keys not found in files: \n");
            dump(array_diff_key($this->baseTranslations, $singleTranslations));
        }

        $this->info("\nFinished: " . now()->toTimeString());
    }

    private function init(): void
    {
        $this->jsonFileManager = new JsonFileManager();

        if (config('laravel-minimal-translation.scan_server')) {
            $this->scanPaths[I18NSourceEnum::SERVER] = config('laravel-minimal-translation.server_paths');
            $this->translationMethods[I18NSourceEnum::SERVER] = config('laravel-minimal-translation.server_i18n_functions');
        }

        if (config('laravel-minimal-translation.scan_vue')) {
            $this->scanPaths[I18NSourceEnum::VUE] = config('laravel-minimal-translation.vue_paths');
            $this->translationMethods[I18NSourceEnum::VUE] = config('laravel-minimal-translation.vue_i18n_functions');
        }


        $this->ignore = $this->option('ignore') ?? $this->ignore;
        $this->display = $this->option('display') ?? $this->display;
        $this->removeOld = $this->option('remove-old') ?? $this->removeOld;
        $this->showOld = $this->option('show-old') ?? $this->showOld;
        $this->findKey = $this->option('find');

        $this->baseTranslations = $this->jsonFileManager->readJsonFile(config('laravel-minimal-translation.base_file'));
    }

    private function searchForKey(string $key = null): array
    {
        $key = $key ?? $this->findKey;
        $this->info("Searching for translation key '$key'");
        foreach (array_keys($this->scanPaths) as $value) {
            try {
                $this->info("Scanning for $value items in files: " . now()->toTimeString());
                $scanner = new Scanner(
                    new Filesystem,
                    $this->scanPaths[$value],
                    $this->translationMethods[$value]
                );

                return $scanner->getTranslationFiles($key);
            } catch (\Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    private function getTranslations(): void
    {
        foreach (array_keys($this->scanPaths) as $value) {
            try {
                $this->info("Scanning for $value items in files: " . now()->toTimeString());
                $scanner = new Scanner(
                    new Filesystem,
                    $this->scanPaths[$value],
                    $this->translationMethods[$value]
                );

                /** @noinspection SlowArrayOperationsInLoopInspection */
                $this->foundTranslations = array_merge_recursive($this->foundTranslations, $scanner->findTranslations());

                $this->info("Scanning finished: " . now()->toTimeString());

                // $this->info("Starting database sync: " . now()->toTimeString());


                //dump($allTranslations['single']);

                //$oldTranslations = $this->dbSync->mark_not_found_translations();

                //$this->info("Finished database sync: " . now()->toTimeString());
                //$this->info("Old translations found $oldTranslations");
                //$this->info("Items added  " . count($this->dbSync->getIdsAdded()));
                //$this->info("Total items found " . count($this->dbSync->getIds()));

                //$this->dbSync->resetIds();
            } catch (\Exception $exception) {
                $this->error($exception->getMessage());
            }
        }
    }

    private function addToBaseTranslations(): void
    {
        $singleTranslations = $this->foundTranslations['single']['single'] ?? [];
        if ($this->removeOld) {
            $this->baseTranslations = array_intersect_key($this->baseTranslations, $singleTranslations);
        }
        foreach (array_keys($singleTranslations) as $key) {
            if (!array_key_exists($key, $this->baseTranslations)) {
                $this->checkDuplicate($key);
                $this->baseTranslations[$key] = ucfirst($key);
            }
        }

        ksort($this->baseTranslations, SORT_NATURAL | SORT_FLAG_CASE);
    }

    private function checkDuplicate(string $key): void
    {
        if (array_key_exists(strtolower($key), array_change_key_case($this->baseTranslations, CASE_LOWER))) {
            foreach (array_keys($this->baseTranslations) as $array_key) {
                if (strtolower($array_key) === strtolower($key)) {
                    $files = $this->searchForKey($key);
                    $this->duplicatesFromCaseSensitive[$array_key] = ['duplicate' => $key, 'files' => $files];
                }
            }
        }
    }

    private function writeBaseTranslation(): void
    {
        try {
            $this->jsonFileManager->writeJsonBaseFile($this->baseTranslations);
        } catch (\Exception $e) {
            $this->error("Error while trying to write the base file. Error: " . $e->getMessage());
        }
    }
}
