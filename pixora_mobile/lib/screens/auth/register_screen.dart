// lib/screens/auth/register_screen.dart
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

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});
  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() { _nameCtrl.dispose(); _emailCtrl.dispose(); _phoneCtrl.dispose(); _passCtrl.dispose(); _confirmCtrl.dispose(); super.dispose(); }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.register(name: _nameCtrl.text.trim(), email: _emailCtrl.text.trim(),
      password: _passCtrl.text, passwordConfirmation: _confirmCtrl.text, phone: _phoneCtrl.text.trim().isEmpty ? null : _phoneCtrl.text.trim());
    if (!mounted) return;
    if (ok) {
      Navigator.pushNamedAndRemoveUntil(context, AppRoutes.main, (_) => false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(auth.error ?? 'Registrasi gagal'), backgroundColor: PixoraTheme.error, behavior: SnackBarBehavior.floating));
    }
  }

  void _registerWithGoogle() {
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
      body: SafeArea(child: SingleChildScrollView(padding: const EdgeInsets.all(24), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        const SizedBox(height: 20),
        IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.arrow_back_ios),
          style: IconButton.styleFrom(backgroundColor: PixoraTheme.lightGray, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)))),
        const SizedBox(height: 32),
        Text('Buat Akun Baru ✨', style: GoogleFonts.poppins(fontSize: 28, fontWeight: FontWeight.bold, color: PixoraTheme.dark))
            .animate().fadeIn(duration: 400.ms),
        const SizedBox(height: 8),
        Text('Daftar untuk mulai booking fotografi', style: GoogleFonts.inter(fontSize: 16, color: PixoraTheme.gray)),
        const SizedBox(height: 32),
        Form(key: _formKey, child: Column(children: [
          TextFormField(controller: _nameCtrl, validator: Validators.name, textCapitalization: TextCapitalization.words,
            decoration: const InputDecoration(hintText: 'Nama Lengkap', prefixIcon: Icon(Icons.person_outlined, color: PixoraTheme.gray))),
          const SizedBox(height: 16),
          TextFormField(controller: _emailCtrl, keyboardType: TextInputType.emailAddress, validator: Validators.email,
            decoration: const InputDecoration(hintText: 'Email', prefixIcon: Icon(Icons.email_outlined, color: PixoraTheme.gray))),
          const SizedBox(height: 16),
          TextFormField(controller: _phoneCtrl, keyboardType: TextInputType.phone, validator: Validators.phone,
            decoration: const InputDecoration(hintText: 'No. Telepon (opsional)', prefixIcon: Icon(Icons.phone_outlined, color: PixoraTheme.gray))),
          const SizedBox(height: 16),
          TextFormField(controller: _passCtrl, obscureText: _obscure, validator: Validators.password,
            decoration: InputDecoration(hintText: 'Password (min 6 karakter)', prefixIcon: const Icon(Icons.lock_outlined, color: PixoraTheme.gray),
              suffixIcon: IconButton(icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility, color: PixoraTheme.gray), onPressed: () => setState(() => _obscure = !_obscure)))),
          const SizedBox(height: 16),
          TextFormField(controller: _confirmCtrl, obscureText: true, validator: (v) => Validators.confirmPassword(v, _passCtrl.text),
            decoration: const InputDecoration(hintText: 'Konfirmasi Password', prefixIcon: Icon(Icons.lock_outlined, color: PixoraTheme.gray))),
          const SizedBox(height: 32),
          Consumer<AuthProvider>(builder: (ctx, auth, _) => GradientButton(text: 'Daftar', onPressed: _register, isLoading: auth.isLoading, icon: Icons.person_add)),
          const SizedBox(height: 20),
          Row(children: [const Expanded(child: Divider()), Padding(padding: const EdgeInsets.symmetric(horizontal: 16), child: Text('atau', style: TextStyle(color: PixoraTheme.gray))), const Expanded(child: Divider())]),
          const SizedBox(height: 20),
          OutlinedButton.icon(onPressed: _registerWithGoogle,
            icon: const Icon(Icons.g_mobiledata, size: 28), label: const Text('Daftar dengan Google'),
            style: OutlinedButton.styleFrom(foregroundColor: PixoraTheme.dark, padding: const EdgeInsets.symmetric(vertical: 14), minimumSize: const Size(double.infinity, 50), side: const BorderSide(color: Color(0xFFE5E7EB)), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)))),
          const SizedBox(height: 24),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            Text('Sudah punya akun? ', style: TextStyle(color: PixoraTheme.gray)),
            GestureDetector(onTap: () => Navigator.pop(context),
              child: const Text('Masuk', style: TextStyle(color: PixoraTheme.primaryRose, fontWeight: FontWeight.w600))),
          ]),
        ])),
      ]))),
    );
  }
}

/// WebView-based Google Sign-In for Register
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
          final uri = Uri.parse(url);
          final baseUri = Uri.parse(ApiConfig.baseUrl);
          if (uri.host == baseUri.host && uri.path == '/' && !url.contains('/auth/google')) {
            _handled = true;
            widget.onSuccess();
          }
        },
        onPageFinished: (url) {
          if (_handled) return;
          setState(() => _isLoading = false);
          final uri = Uri.parse(url);
          final baseUri = Uri.parse(ApiConfig.baseUrl);
          if (uri.host == baseUri.host && uri.path == '/login') {
            _handled = true;
            widget.onError('Pendaftaran dengan Google gagal. Pastikan kredensial Google telah dikonfigurasi.');
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
