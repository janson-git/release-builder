@extends('./layout.blade.php')

@section('content')
<form class="card" method="post">
    <div class="flex justify-left">
        <label for="name" class="w-40">Login</label>
        <input
                id="login" name="login" type="text" placeholder="Login"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-left">
        <label for="name" class="w-40">Username</label>
        <input
                id="name" name="name" type="text" placeholder="Username"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-left">
        <label for="email" class="w-40">Password</label>
        <input
                id="password" name="password" type="password" placeholder="Password"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-left">
        <label for="email" class="w-40">Password</label>
        <input
                id="password2" name="password2" type="password" placeholder="Confirm"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-start items-center">
        <button type="submit" class="block px-4 py-2 rounded border border-green-400 text-green-400 hover:bg-green-400 hover:text-white">Register</button>

        <div class="ml-4">or
            <a href="/auth/login" class="ml-2 cursor-pointer text-blue-400">login</a>
        </div>
    </div>
</form>
@endsection
