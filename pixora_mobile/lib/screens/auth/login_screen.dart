// lib/screens/auth/login_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../config/api_config.dart';
import '../../providers/auth_provider.dart';
import '../../utils/validators.dart';
import '../../widgets/gradient_button.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() { _emailCtrl.dispose(); _passCtrl.dispose(); super.dispose(); }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.login(_emailCtrl.text.trim(), _passCtrl.text);
    if (!mounted) return;
    if (ok) {
      Navigator.pushNamedAndRemoveUntil(context, AppRoutes.main, (_) => false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(auth.error ?? 'Login gagal'), backgroundColor: PixoraTheme.error,
        behavior: SnackBarBehavior.floating, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ));
    }
  }

  void _loginWithGoogle() {
    final googleUrl = '${ApiConfig.baseUrl}/auth/google';
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => _GoogleSignInWebView(url: googleUrl, onSuccess: () {
        if (!mounted) return;
        Navigator.pop(context); // Close WebView
        Navigator.pushNamedAndRemoveUntil(context, AppRoutes.main, (_) => false);
      }, onError: (error) {
        if (!mounted) return;
        Navigator.pop(context); // Close WebView
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text(error), backgroundColor: PixoraTheme.error,
          behavior: SnackBarBehavior.floating,
        ));
      }),
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const SizedBox(height: 20),
            IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.arrow_back_ios),
              style: IconButton.styleFrom(backgroundColor: PixoraTheme.lightGray, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)))),
            const SizedBox(height: 32),
            Text('Selamat Datang\nKembali! 👋', style: GoogleFonts.poppins(fontSize: 28, fontWeight: FontWeight.bold, color: PixoraTheme.dark, height: 1.2))
                .animate().fadeIn(duration: 400.ms).slideX(begin: -0.1, end: 0),
            const SizedBox(height: 8),
            Text('Masuk ke akun Pixora Anda', style: GoogleFonts.inter(fontSize: 16, color: PixoraTheme.gray))
                .animate(delay: 200.ms).fadeIn(duration: 400.ms),
            const SizedBox(height: 40),
            Form(key: _formKey, child: Column(children: [
              TextFormField(controller: _emailCtrl, keyboardType: TextInputType.emailAddress, validator: Validators.email,
                decoration: const InputDecoration(hintText: 'Email', prefixIcon: Icon(Icons.email_outlined, color: PixoraTheme.gray))),
              const SizedBox(height: 16),
              TextFormField(controller: _passCtrl, obscureText: _obscure, validator: Validators.password,
                decoration: InputDecoration(hintText: 'Password', prefixIcon: const Icon(Icons.lock_outlined, color: PixoraTheme.gray),
                  suffixIcon: IconButton(icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility, color: PixoraTheme.gray),
                    onPressed: () => setState(() => _obscure = !_obscure)))),
              const SizedBox(height: 32),
              Consumer<AuthProvider>(builder: (ctx, auth, _) => GradientButton(text: 'Masuk', onPressed: _login, isLoading: auth.isLoading, icon: Icons.login)),
              const SizedBox(height: 24),
              Row(children: [const Expanded(child: Divider()), Padding(padding: const EdgeInsets.symmetric(horizontal: 16), child: Text('atau', style: TextStyle(color: PixoraTheme.gray))), const Expanded(child: Divider())]),
              const SizedBox(height: 24),
              OutlinedButton.icon(onPressed: _loginWithGoogle,
                icon: const Icon(Icons.g_mobiledata, size: 28), label: const Text('Masuk dengan Google'),
                style: OutlinedButton.styleFrom(foregroundColor: PixoraTheme.dark, padding: const EdgeInsets.symmetric(vertical: 14), minimumSize: const Size(double.infinity, 50), side: const BorderSide(color: Color(0xFFE5E7EB)), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)))),
              const SizedBox(height: 32),
              Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                Text('Belum punya akun? ', style: TextStyle(color: PixoraTheme.gray)),
                GestureDetector(onTap: () => Navigator.pushNamed(context, AppRoutes.register),
                  child: const Text('Daftar Sekarang', style: TextStyle(color: PixoraTheme.primaryRose, fontWeight: FontWeight.w600))),
              ]),
            ])),
          ]),
        ),
      ),
    );
  }
}

/// WebView-based Google Sign-In
/// Opens the web OAuth flow and intercepts the redirect after successful login
class _GoogleSignInWebView extends StatefulWidget {
  final String url;
  final VoidCallback onSuccess;
  final Function(String error) onError;

  const _GoogleSignInWebView({required this.url, required this.onSuccess, required this.onError});

  @override
  State<_GoogleSignInWebView> createState() => _GoogleSignInWebViewState();
}

class _GoogleSignInWebViewState extends State<_GoogleSignInWebView> {
  late final WebViewController _controller;
  bool _isLoading = true;
  bool _handled = false;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (url) {
          if (_handled) return;
          setState(() => _isLoading = true);
          // Intercept the callback URL after Google auth
          final uri = Uri.parse(url);
          final baseUri = Uri.parse(ApiConfig.baseUrl);
          // Check if we're being redirected back to home (success) or login (error)
          if (uri.host == baseUri.host && uri.path == '/' && !url.contains('/auth/google')) {
            // Success — user was redirected to home
            _handled = true;
            widget.onSuccess();
          }
        },
        onPageFinished: (url) {
          if (_handled) return;
          setState(() => _isLoading = false);
          // Also check on page finished for login page with error
          final uri = Uri.parse(url);
          final baseUri = Uri.parse(ApiConfig.baseUrl);
          if (uri.host == baseUri.host && uri.path == '/login') {
            _handled = true;
            widget.onError('Login dengan Google gagal. Pastikan kredensial Google telah dikonfigurasi.');
          }
        },
        onWebResourceError: (error) {
          if (_handled) return;
          _handled = true;
          widget.onError('Gagal memuat halaman: ${error.description}');
        },
      ))
      ..loadRequest(Uri.parse(widget.url));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Google Sign-In', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        leading: IconButton(icon: const Icon(Icons.close), onPressed: () => Navigator.pop(context)),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading)
            const Center(child: CircularProgressIndicator(color: PixoraTheme.primaryRose)),
        ],
      ),
    );
  }
}
