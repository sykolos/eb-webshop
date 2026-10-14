@foreach($nodes as $node)
    @php($nextTrail = array_merge($trail, [$node->name]))
    <li>
        <details open>
            <summary><strong>{{ $node->name }}</strong> <small>{{ implode(' / ', $nextTrail) }}</small></summary>
            @if($node->children->isNotEmpty())
                <ul class="v6-tree__children">
                    @include('admin.m06.categories._tree', ['nodes' => $node->children, 'trail' => $nextTrail])
                </ul>
            @endif
        </details>
    </li>
@endforeach
