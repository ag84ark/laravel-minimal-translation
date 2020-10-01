<?php


namespace Ag84ark\LaravelMinimalTranslation;


class JsonFileManager
{

    /**
     * @param array $data
     * @param string $lang
     * @throws \Exception
     */
    public function writeJsonFile(array $data, string $lang = 'fr'): void
    {
        if (!in_array($lang, config('laravel-minimal-translation.supported_languages'), true)) {
            throw new \RuntimeException("Language: $lang is not allowed");
        }
        $newJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        file_put_contents(base_path("resources/lang/$lang.json"), stripslashes($newJsonString));
    }


    public function readJsonFile(string $lang = 'fr')
    {
        if (!in_array($lang, array_merge(config('laravel-minimal-translation.supported_languages'), ['base']), true)) {
            throw new \RuntimeException("Language: $lang is not allowed");
        }
        try {
            $jsonString = file_get_contents(base_path("resources/lang/$lang.json"));

        } catch (\Exception $exception) {

            $newJsonString = json_encode(["base" => "base"], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents(base_path("resources/lang/$lang.json"), stripslashes($newJsonString));
            $jsonString = file_get_contents(base_path("resources/lang/$lang.json"));
        }

        return json_decode($jsonString, true);
    }
}
