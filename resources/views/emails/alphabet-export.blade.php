<x-mail::message>
    # Alphabet export

    **County:** {{ $county }}

    **Letter:** {{ $letter }}

    @if (count($cities) === 0)
        No cities found.
    @else
        <x-mail::panel>
            @foreach ($cities as $city)
                - {{ $city }}
            @endforeach
        </x-mail::panel>
    @endif

    The CSV export is attached to this email.

    Thanks,
    {{ config('app.name') }}
</x-mail::message>