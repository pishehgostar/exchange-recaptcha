<div class="form-group">
    <div class="text-end mt-3">
        <button
        @foreach($attributes as $index=>$attribute)
            {{$index}}="{{$attribute}}"
        @endforeach
        >
            {{$title}}
        </button>
    </div>
</div>
