@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif
