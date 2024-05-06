@extends('./layout.blade.php')

@section('content')

<h1 class="mt-4 border-b">Headers</h1>

<div>
    <h1>Header h1</h1>
    <h2>Header h2</h2>
    <h3>Header h3</h3>
    <h4>Header h4</h4>
</div>

<h1 class="mt-4 border-b">Buttons</h1>

<div class="flex mb-4 pb-4 border-b-2">
    <div class="w-1/2">
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn">Simple button</a>
            <span class="ml-4 italic">btn</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-primary">Primary button</a>
            <span class="ml-4 italic">btn btn-primary</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-primary-outline">Primary outline button</a>
            <span class="ml-4 italic">btn btn-primary-outline</span>
        </div>
    </div>

    <div class="w-1/2">
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-muted">Muted button</a>
            <span class="ml-4 italic">btn btn-muted</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-danger">Danger button</a>
            <span class="ml-4 italic">btn btn-danger</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-danger-outline">Danger outline button</a>
            <span class="ml-4 italic">btn btn-danger-outline</span>
        </div>
    </div>
</div>

<h2 class="mt-4">Buttons size</h2>

<div class="flex mb-4 pb-4 border-b-2">
    <div class="w-1/2">
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-s">Small button</a>
            <span class="ml-4 italic">btn btn-s</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn">Button</a>
            <span class="ml-4 italic">btn</span>
        </div>
        <div class="mt-4 flex justify-normal items-baseline">
            <a href="#" class="btn btn-lg">Large button</a>
            <span class="ml-4 italic">btn btn-lg</span>
        </div>
    </div>

    <div class="w-1/2">
        <div>&nbsp;</div>
    </div>
</div>
@endsection
