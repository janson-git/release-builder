@extends('layout')

@section('content')
<form class="mt-6 card bg-white p-8" method="post">
    @csrf
    <div class="flex justify-left">
        <label for="name" class="w-40">Email</label>
        <input
                id="email" name="email" type="text" placeholder="Email"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-left">
        <label for="name" class="w-40">Name</label>
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
        <label for="email" class="w-40">Confirm password</label>
        <input
                id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password"
                class="w-72 border-b border-b-gray-400 focus:border-b-black focus:outline-none"
        >
    </div>

    <div class="mt-4 flex justify-start items-center">
        <div class="w-40"></div>
        <button type="submit" class="block px-4 py-2 rounded border border-green-400 text-green-400 hover:bg-green-400 hover:text-white">Register</button>

        <div class="ml-4">or
            <a href="/login" class="ml-2 cursor-pointer text-blue-400">login</a>
        </div>
    </div>
</form>
@endsection
