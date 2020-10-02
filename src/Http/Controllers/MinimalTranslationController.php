<?php


namespace Ag84ark\LaravelMinimalTranslation\Http\Controllers;

use Ag84ark\LaravelMinimalTranslation\JsonFileManager;

class MinimalTranslationController
{
    private $jsonFileManager;
    private $mainLanguage;
    private $baseFile;

    public function __construct(JsonFileManager $jsonFileManager)
    {
        $this->jsonFileManager = $jsonFileManager;
        $this->mainLanguage = (string) config('laravel-minimal-translation.main_language');
        $this->baseFile = (string) config('laravel-minimal-translation.base_file');
    }

    public function index(string $lang = null)
    {
        try {
            $lang = $lang ?? $this->mainLanguage;

            $baseData = $this->jsonFileManager->readJsonFile($this->baseFile);
            $langData = $this->jsonFileManager->readJsonFile($lang);
            $data = $this->getCombinedData($baseData, $langData);

            return view('laravel-minimal-translation::index', compact('data', 'lang', 'baseData'));
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }


    private function getCombinedData($baseData, $langData)
    {
        foreach ($baseData as $key => $value) {
            if (array_key_exists($key, $langData)) {
                $baseData[$key] = $langData[$key];
            }
        }
        return $baseData;
    }

    public function save(string $lang = null)
    {
        $baseData = $this->jsonFileManager->readJsonFile($this->baseFile);


        $lang = $lang ?? $this->mainLanguage;
        $data = [];

        foreach (array_keys($baseData) as $key) {
            $data[$key] = request()->get($this->spaceAndPointToLodash($key)) ?? '';
        }

        try {
            $this->jsonFileManager->writeJsonFile($data, $lang);
        } catch (\Exception $exception) {
            return redirect()->back()->withInput()->withErrors($exception->getMessage());
        }

        return redirect()->route("minimal_translation.index", [$lang])->with("status", "Saved");
    }

    private function lodashToSpace(string $value)
    {
        return str_replace("_", " ", $value);
    }

    private function spaceAndPointToLodash(string $value)
    {
        return str_replace([" ",'.'], "_", $value);
    }
}
