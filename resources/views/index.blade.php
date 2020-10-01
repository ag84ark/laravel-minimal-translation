@extends(config('laravel-minimal-translation.extend_layout'))


@section(config('laravel-minimal-translation.content_section'))

    @if(config('laravel-minimal-translation.import_tailwind'))
        <script>
            var fileRef = document.createElement("link");
            fileRef.setAttribute("rel", "stylesheet");
            fileRef.setAttribute("type", "text/css");
            fileRef.setAttribute("href", "https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css");
            document.getElementsByTagName("head")[0].appendChild(fileRef);
        </script>

    @endif

    <h1 class="text-2xl text-capitalize text-center">Translations for {{ strtoupper($lang)}}</h1>
    <div class="text-center mb-4">
        @foreach(config('laravel-minimal-translation.supported_languages') as $item)
            <a href="{{ route('minimal_translation.index', $item) }}">{{$item}}</a>
        @endforeach

    </div>



    <form action="{{route('minimal_translation.save', [$lang])}}" method="POST">
        @csrf
        <div class="p-5 bg-gray-100 text-left">

            @foreach($data as $key => $val)
                <div class="w-full rounded overflow-hidden shadow-sm mb-4 bg-white">
                    <div class="px-6 py-1">
                        <div class="font-bold text-md mb-2">
                            <label for="{{$key}}">{{$key}}</label>
                        </div>
                        <input
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            name="{{$key}}" id="{{$key}}" value="{{ old($key) ?? $val }}" type="text" placeholder="">
                    </div>
                    <div class="px-6 pt-1 pb-2">
                        <span class="text-gray-500 mr-2 mb-2">
                            {{$baseData[$key]}}
                        </span>
                    </div>
                </div>
            @endforeach

            <div class="w-full rounded overflow-hidden shadow-sm mb-4 bg-white">
                <div class="p-6">
                    <button type="submit"
                            class=" w-full bg-green-700 hover:bg-green-500 text-white font-bold py-2 px-12 rounded focus:outline-none focus:shadow-outline">
                        Save
                    </button>
                </div>
            </div>

        </div>
    </form>

@stop
