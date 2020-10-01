<?php


namespace Ag84ark\LaravelMinimalTranslation\Console\Commands;

use Ag84ark\LaravelMinimalTranslation\JsonFileManager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class LMTCommand extends Command
{

    protected $signature = 'lmt:scan-i18n
                            {--find= : Find a translate key and display files where was found (case-sensitive)}
                            {--remove-old : Remove texts that were not found during the search}
                            {--display : Display only the found i18n texts, no write to base file}';

    protected $description = 'This will scan resources\view files and app folders for translations and add them to base JSON file';

    /** @var JsonFileManager */
    private $jsonFileManager;

    private $removeOld = false;

    private $display = false;

    private $findKey;

    private $scanPaths = [];

    private $translationMethods = [];

    private $baseTranslations = [];

    private $foundTranslations = [];

    public function handle(): void
    {
        $this->init();

        if($this->findKey) {
            $this->searchForKey();
            return;
        }

        $this->getTranslations();

        if($this->display){
            dump($this->foundTranslations['single']['single'] ?? []);
            return;
        }

        $this->addToBaseTranslations();

        dump($this->foundTranslations);
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


        $this->display = $this->option('display') ?? $this->display;
        $this->removeOld = $this->option('remove-old') ?? $this->removeOld;
        $this->findKey = $this->option('find');

        $this->baseTranslations = $this->jsonFileManager->readJsonFile(config('laravel-minimal-translation.base_file'));

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

                $this->info("Starting database sync: " . now()->toTimeString());


                //dump($allTranslations['single']);

                //$oldTranslations = $this->dbSync->mark_not_found_translations();

                $this->info("Finished database sync: " . now()->toTimeString());
                //$this->info("Old translations found $oldTranslations");
                //$this->info("Items added  " . count($this->dbSync->getIdsAdded()));
                //$this->info("Total items found " . count($this->dbSync->getIds()));

                //$this->dbSync->resetIds();
            } catch (\Exception $exception) {
                $this->warn($exception->getMessage());
            }


        }

        foreach ($this->foundTranslations['single'] as $group => $items) {
            dump($this->foundTranslations['single'][$group]);
            foreach ($items as $key => $_) {
                //dump([$key]);
                //$this->dbSync->handle($group, $key);
            }
        }
    }

    private function searchForKey(): void
    {
        $this->info("Searching for translation key '$this->findKey'");
        foreach (array_keys($this->scanPaths) as $value) {
            try {
                $this->info("Scanning for $value items in files: " . now()->toTimeString());
                $scanner = new Scanner(
                    new Filesystem,
                    $this->scanPaths[$value],
                    $this->translationMethods[$value]
                );

                $scanner->textExistsInFiles($this->findKey, true);

            } catch (\Exception $exception) {
                $this->warn($exception->getMessage());
            }


        }
    }

    private function addToBaseTranslations() : void
    {
        $singleTranslations = $this->foundTranslations['single']['single'] ?? [];
        if($this->removeOld){
            $this->baseTranslations = array_intersect_key($this->baseTranslations, $singleTranslations);
        }
        $this->baseTranslations = array_merge($this->baseTranslations, $singleTranslations);
        ksort($this->baseTranslations, SORT_NATURAL | SORT_FLAG_CASE);
        $this->info("Base translations");
        dump($this->baseTranslations);
    }
}
