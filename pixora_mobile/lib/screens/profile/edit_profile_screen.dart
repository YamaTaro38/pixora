// lib/screens/profile/edit_profile_screen.dart
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../utils/validators.dart';
import '../../widgets/gradient_button.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});
  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _nameCtrl;
  late TextEditingController _phoneCtrl;
  File? _imageFile;

  @override
  void initState() {
    super.initState();
    final user = Provider.of<AuthProvider>(context, listen: false).user;
    _nameCtrl = TextEditingController(text: user?.name ?? '');
    _phoneCtrl = TextEditingController(text: user?.phone ?? '');
  }

  @override
  void dispose() { _nameCtrl.dispose(); _phoneCtrl.dispose(); super.dispose(); }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);
    if (pickedFile != null) {
      setState(() {
        _imageFile = File(pickedFile.path);
      });
    }
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final ok = await auth.updateProfile(
      name: _nameCtrl.text.trim(), 
      phone: _phoneCtrl.text.trim().isEmpty ? null : _phoneCtrl.text.trim(),
      avatarPath: _imageFile?.path,
    );
    if (!mounted) return;
    if (ok) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profil berhasil diupdate'), backgroundColor: PixoraTheme.success));
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(auth.error ?? 'Gagal update profil'), backgroundColor: PixoraTheme.error));
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).user;
    return Scaffold(
      appBar: AppBar(title: Text('Edit Profil', style: GoogleFonts.poppins(fontWeight: FontWeight.bold))),
      body: SingleChildScrollView(padding: const EdgeInsets.all(20), child: Form(key: _formKey, child: Column(children: [
        // Avatar
        Stack(
          alignment: Alignment.bottomRight,
          children: [
            CircleAvatar(
              radius: 50, 
              backgroundColor: PixoraTheme.lightRose,
              backgroundImage: _imageFile != null 
                ? FileImage(_imageFile!) 
                : (user?.avatar != null ? NetworkImage(user!.avatar!) : null) as ImageProvider?,
              child: (_imageFile == null && user?.avatar == null)
                ? Text(user?.initials ?? '?', style: GoogleFonts.poppins(fontSize: 36, fontWeight: FontWeight.bold, color: PixoraTheme.primaryRose))
                : null,
            ),
            GestureDetector(
              onTap: _pickImage,
              child: Container(
                padding: const EdgeInsets.all(8),
                decoration: const BoxDecoration(color: PixoraTheme.primaryRose, shape: BoxShape.circle),
                child: const Icon(Icons.camera_alt, color: Colors.white, size: 20),
              ),
            ),
          ],
        ),
        const SizedBox(height: 32),
        TextFormField(controller: _nameCtrl, validator: Validators.name, textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(labelText: 'Nama Lengkap', prefixIcon: Icon(Icons.person_outlined, color: PixoraTheme.gray))),
        const SizedBox(height: 16),
        TextFormField(controller: _phoneCtrl, keyboardType: TextInputType.phone, validator: Validators.phone,
          decoration: const InputDecoration(labelText: 'No. Telepon', prefixIcon: Icon(Icons.phone_outlined, color: PixoraTheme.gray))),
        const SizedBox(height: 32),
        Consumer<AuthProvider>(builder: (ctx, auth, _) => GradientButton(text: 'Simpan Perubahan', icon: Icons.check, onPressed: _save, isLoading: auth.isLoading)),
      ]))),
    );
  }
}
