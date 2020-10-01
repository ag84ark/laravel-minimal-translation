<?php


namespace Ag84ark\LaravelMinimalTranslation\Http\Controllers;


use Ag84ark\LaravelMinimalTranslation\JsonFileManager;

class MinimalTranslationController
{

    private $jsonFileManager;

    public function __construct(JsonFileManager $jsonFileManager)
    {
        $this->jsonFileManager = $jsonFileManager;
    }

    public function index(string $lang = null)
    {
        try {
            $lang = $lang ?? config('laravel-minimal-translation.main_language');

            $baseData = $this->jsonFileManager->readJsonFile(config('laravel-minimal-translation.base_file'));
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

    public function save(string $lang)
    {
        $baseData = $this->jsonFileManager->readJsonFile(config('laravel-minimal-translation.base_file'));


        $lang = $lang ?? config('laravel-minimal-translation.main_language');
        $data = [];

        foreach (array_keys($baseData) as $key){
            $data[$key] = request()->get($this->spaceToLodash($key)) ?? '';
        }

        //$data = collect(request()->all())
        //    ->mapWithKeys(function ($item, $key) {
        //        return [/*$this->lodashToSpace($key)*/ $key => str_replace("\"", "“", $item)];
        //    })
        //    ->toArray();

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

    private function spaceToLodash(string $value)
    {
        return str_replace(" ", "_", $value);
    }


}
