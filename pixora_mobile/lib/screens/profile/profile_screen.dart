// lib/screens/profile/profile_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/auth_provider.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(builder: (ctx, auth, _) {
      final user = auth.user;
      final isLoggedIn = auth.isLoggedIn;

      return Scaffold(
        appBar: AppBar(title: Text('Profil', style: GoogleFonts.poppins(fontWeight: FontWeight.bold))),
        body: SingleChildScrollView(padding: const EdgeInsets.all(20), child: Column(children: [
          // Avatar section
          Container(width: double.infinity, padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(gradient: PixoraTheme.primaryGradient, borderRadius: BorderRadius.circular(20), boxShadow: PixoraTheme.roseShadow),
            child: Column(children: [
              CircleAvatar(radius: 40, backgroundColor: Colors.white.withValues(alpha: 0.2),
                backgroundImage: isLoggedIn && user!.avatar != null ? NetworkImage(user.avatar!) : null,
                child: (isLoggedIn && user!.avatar != null) ? null : 
                  (isLoggedIn ? Text(user!.initials, style: GoogleFonts.poppins(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white))
                  : const Icon(Icons.person, size: 40, color: Colors.white))),
              const SizedBox(height: 12),
              Text(isLoggedIn ? user!.name : 'Tamu', style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
              if (isLoggedIn) Text(user!.email, style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 14)),
              if (isLoggedIn && user!.phone != null) Text(user.phone!, style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 13)),
            ])),
          const SizedBox(height: 24),

          if (!isLoggedIn) ...[
            _menuItem(Icons.login, 'Masuk', 'Login ke akun Anda', () => Navigator.pushNamed(context, AppRoutes.login)),
            _menuItem(Icons.person_add, 'Daftar', 'Buat akun baru', () => Navigator.pushNamed(context, AppRoutes.register)),
          ],

          if (isLoggedIn) ...[
            _menuItem(Icons.edit, 'Edit Profil', 'Ubah nama dan telepon', () => Navigator.pushNamed(context, AppRoutes.editProfile)),
            _menuItem(Icons.lock_outline, 'Ubah Password', 'Ganti password akun', () => _showChangePasswordDialog(context)),
            _menuItem(Icons.history, 'Riwayat Booking', 'Lihat booking Anda', () {}),
            const Divider(height: 32),
            _menuItem(Icons.logout, 'Keluar', 'Logout dari akun', () => _logout(context), isDestructive: true),
          ],

          const Divider(height: 32),
          _menuItem(Icons.info_outline, 'Tentang Pixora', 'Versi 1.0.0', () {}),
          _menuItem(Icons.help_outline, 'Bantuan', 'FAQ dan kontak', () {}),
        ])),
      );
    });
  }

  Widget _menuItem(IconData icon, String title, String subtitle, VoidCallback onTap, {bool isDestructive = false}) {
    return ListTile(leading: Container(width: 44, height: 44, decoration: BoxDecoration(
        color: isDestructive ? PixoraTheme.error.withValues(alpha: 0.1) : PixoraTheme.lightRose, borderRadius: BorderRadius.circular(12)),
      child: Icon(icon, color: isDestructive ? PixoraTheme.error : PixoraTheme.primaryRose, size: 22)),
      title: Text(title, style: TextStyle(fontWeight: FontWeight.w600, color: isDestructive ? PixoraTheme.error : PixoraTheme.dark)),
      subtitle: Text(subtitle, style: const TextStyle(fontSize: 12, color: PixoraTheme.gray)),
      trailing: const Icon(Icons.chevron_right, color: PixoraTheme.gray), onTap: onTap,
      contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2));
  }

  void _logout(BuildContext context) {
    showDialog(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Logout'), content: const Text('Yakin ingin keluar dari akun?'),
      actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
        TextButton(onPressed: () async { Navigator.pop(ctx); await Provider.of<AuthProvider>(context, listen: false).logout(); }, child: const Text('Keluar', style: TextStyle(color: PixoraTheme.error)))]));
  }

  void _showChangePasswordDialog(BuildContext context) {
    final currCtrl = TextEditingController(); final newCtrl = TextEditingController(); final confCtrl = TextEditingController();
    showDialog(context: context, builder: (ctx) => AlertDialog(
      title: const Text('Ubah Password'),
      content: Column(mainAxisSize: MainAxisSize.min, children: [
        TextField(controller: currCtrl, obscureText: true, decoration: const InputDecoration(hintText: 'Password Saat Ini')),
        const SizedBox(height: 12),
        TextField(controller: newCtrl, obscureText: true, decoration: const InputDecoration(hintText: 'Password Baru')),
        const SizedBox(height: 12),
        TextField(controller: confCtrl, obscureText: true, decoration: const InputDecoration(hintText: 'Konfirmasi Password Baru')),
      ]),
      actions: [TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
        TextButton(onPressed: () async {
          final auth = Provider.of<AuthProvider>(context, listen: false);
          final ok = await auth.changePassword(currentPassword: currCtrl.text, newPassword: newCtrl.text, newPasswordConfirmation: confCtrl.text);
          if (ctx.mounted) Navigator.pop(ctx);
          if (ok && context.mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password berhasil diubah')));
        }, child: const Text('Simpan', style: TextStyle(color: PixoraTheme.primaryRose)))]));
  }
}
