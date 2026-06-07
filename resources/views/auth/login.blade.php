{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pixora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-r from-rose-500 to-pink-600 font-[Inter]">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-rose-600">Pixora</h1>
                <p class="text-gray-500 mt-2">Login ke akun Anda</p>
            </div>
            
            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded text-sm">
                {{ session('error') }}
            </div>
            @endif
            
            @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded text-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500" required>
                </div>
                <button type="submit" class="w-full bg-rose-500 text-white py-2 rounded-lg font-semibold hover:bg-rose-600 transition">
                    Login
                </button>
            </form>
            
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Atau</span>
                </div>
            </div>
            
            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 border border-gray-300 rounded-lg py-2 hover:bg-gray-50 transition">
                <img src="https://www.google.com/favicon.ico" class="w-5 h-5">
                <span>Login dengan Google</span>
            </a>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                Belum punya akun? <a href="{{ route('register') }}" class="text-rose-500 hover:underline">Daftar disini</a>
            </p>
        </div>
    </div>
</body>
</html>