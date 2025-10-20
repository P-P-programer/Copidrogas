@if(isset($breadcrumbs) && count($breadcrumbs) > 1)
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="flex text-sm text-gray-300 gap-2">
            @foreach($breadcrumbs as $i => $crumb)
                @if($i < count($breadcrumbs) - 1)
                    <li>
                        <a href="{{ $crumb['url'] }}" class="text-blue-200 hover:text-white">{{ $crumb['label'] }}</a>
                        <span class="mx-1">/</span>
                    </li>
                @else
                    <li class="text-white">{{ $crumb['label'] }}</li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif