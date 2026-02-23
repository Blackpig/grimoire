<x-filament-panels::page>
    @if ($this->editing)
        <div wire:key="grimoire-editor">
            {{ ($this->form) }}
        </div>
    @else
        <div @class(['grimoire-prose', $proseTheme => $proseTheme !== ''])>
            {!! $renderedHtml !!}
        </div>
    @endif
</x-filament-panels::page>
