{{-- resources/views/admin/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pixora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }
        
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -2;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .input-glass {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .input-glass:focus {
            background: white;
            border-color: #e11d48;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
            outline: none;
        }
        
        .error-message {
            animation: shake 0.3s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>
    <div class="login-bg"></div>
    
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="glass-card rounded-3xl w-full max-w-md p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-rose-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-camera text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-display font-bold bg-gradient-to-r from-rose-600 to-pink-600 bg-clip-text text-transparent">Pixora Admin</h1>
                <p class="text-gray-500 mt-2">Silakan login untuk mengakses panel</p>
            </div>
            
            <!-- Tampilkan semua error -->
            @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-red-700 font-medium mb-1">Terjadi kesalahan:</p>
                        <ul class="text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
            
            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
            @endif
            
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
            @endif
            
            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" value="{{ old('email') }}" 
                               class="input-glass w-full rounded-xl px-10 py-3 focus:outline-none @error('email') border-red-500 @enderror" 
                               placeholder="admin@pixora.com" required>
                    </div>
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" 
                               class="input-glass w-full rounded-xl px-10 py-3 focus:outline-none @error('password') border-red-500 @enderror" 
                               placeholder="••••••••" required>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-rose-500 to-pink-600 text-white py-3 rounded-xl font-semibold hover:shadow-lg transition-all transform hover:scale-[1.02]">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
            
            <p class="text-center text-gray-500 text-sm mt-6">
                <a href="{{ route('home') }}" class="text-rose-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Website
                </a>
            </p>
        </div>
    </div>
</body>
</html>