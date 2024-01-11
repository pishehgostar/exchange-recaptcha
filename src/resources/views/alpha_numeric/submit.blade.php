<div class="form-group">
    <div class="input-group">
        <input type="text" class="form-control" id="captcha-text-{{$uuid}}" required>
        <span class="input-group-text fa fa-refresh text-muted" id="captcha-refresh-{{$uuid}}"
              onclick="refreshCaptcha('{{$uuid}}')"></span>
        <span class="input-group-text bg-white border-0">
            <img id="captcha-img-{{$uuid}}" class="rounded-1" alt="captch" src="data:image/jpeg;base64,{{$src}}"/>
        </span>
    </div>
    <input id="captcha-response-{{$uuid}}" type="hidden" name="{{$inputName}}">
</div>
<div class="form-group">
    <div class="text-end mt-3">
        <button
        @foreach($attributes as $index=>$attribute)
            {{$index}}="{{$attribute}}"
        @endforeach
        >{{$title}}</button>
    </div>
</div>
