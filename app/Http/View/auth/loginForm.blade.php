@extends('./layout.blade.php')

@section('content')
<form class="mt-6 card bg-white p-8" method="post">
    <div class="flex justify-left">
        <label for="name" class="w-40">Login</label>
        <input id="login" name="login" type="text" placeholder="Login" class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none">
    </div>

    <div class="mt-4 flex justify-left">
        <label for="email" class="w-40">Password</label>
        <input class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none" id="password" name="password" type="password" placeholder="Password">
    </div>

    <div class="mt-4 flex justify-start items-center">
        <button type="submit" class="block px-4 py-2 rounded border border-green-400 text-green-400 hover:bg-green-400 hover:text-white">Login</button>

        <div class="ml-4">or
            <a href="/auth/register" class="ml-1 cursor-pointer text-blue-400">register</a>
        </div>
    </div>
</form>
@endsection
