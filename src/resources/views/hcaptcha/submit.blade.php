<button
@foreach($attributes as $index=>$attribute)
    {{$index}}="{{$attribute}}"
@endforeach
>
    {{$title}}
</button>
