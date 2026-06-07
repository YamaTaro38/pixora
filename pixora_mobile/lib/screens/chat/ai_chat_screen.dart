// lib/screens/chat/ai_chat_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:flutter_animate/flutter_animate.dart';
import '../../config/theme.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class AiChatScreen extends StatefulWidget {
  const AiChatScreen({super.key});
  @override
  State<AiChatScreen> createState() => _AiChatScreenState();
}

class _AiChatScreenState extends State<AiChatScreen> {
  final _controller = TextEditingController();
  final _scrollCtrl = ScrollController();
  final _api = ApiService();
  final List<_ChatMessage> _messages = [];
  bool _isTyping = false;

  @override
  void initState() {
    super.initState();
    _messages.add(_ChatMessage(text: 'Halo! 👋 Saya adalah asisten AI Pixora Studio. Saya bisa membantu Anda tentang:\n\n• Informasi paket fotografi\n• Ketersediaan jadwal\n• Proses booking dan pembayaran\n• Pertanyaan lainnya\n\nSilakan tanya apa saja!', isUser: false));
  }

  @override
  void dispose() { _controller.dispose(); _scrollCtrl.dispose(); super.dispose(); }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollCtrl.hasClients) _scrollCtrl.animateTo(_scrollCtrl.position.maxScrollExtent, duration: const Duration(milliseconds: 300), curve: Curves.easeOut);
    });
  }

  Future<void> _send() async {
    final text = _controller.text.trim();
    if (text.isEmpty) return;
    setState(() { _messages.add(_ChatMessage(text: text, isUser: true)); _isTyping = true; });
    _controller.clear();
    _scrollToBottom();

    try {
      final response = await _api.post(ApiConfig.aiChat, body: {'message': text});
      if (response.success && response.data != null) {
        setState(() { _messages.add(_ChatMessage(text: response.data['reply'] ?? 'Maaf, saya tidak bisa menjawab saat ini.', isUser: false)); _isTyping = false; });
      } else {
        setState(() { _messages.add(_ChatMessage(text: 'Maaf, terjadi kesalahan. Silakan coba lagi.', isUser: false)); _isTyping = false; });
      }
    } catch (_) {
      setState(() { _messages.add(_ChatMessage(text: 'Koneksi gagal. Pastikan terhubung ke internet.', isUser: false)); _isTyping = false; });
    }
    _scrollToBottom();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(children: [
          Container(width: 36, height: 36, decoration: const BoxDecoration(gradient: PixoraTheme.primaryGradient, shape: BoxShape.circle),
            child: const Icon(Icons.smart_toy, color: Colors.white, size: 20)),
          const SizedBox(width: 10),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text('Pixora AI', style: GoogleFonts.poppins(fontWeight: FontWeight.bold, fontSize: 16)),
            Text(_isTyping ? 'Mengetik...' : 'Online', style: TextStyle(fontSize: 11, color: _isTyping ? PixoraTheme.warning : PixoraTheme.success)),
          ]),
        ]),
      ),
      body: Column(children: [
        Expanded(child: ListView.builder(controller: _scrollCtrl, padding: const EdgeInsets.all(16), itemCount: _messages.length + (_isTyping ? 1 : 0),
          itemBuilder: (ctx, i) {
            if (i == _messages.length && _isTyping) return _typingIndicator();
            return _buildBubble(_messages[i]);
          })),
        _buildInput(),
      ]),
    );
  }

  Widget _buildBubble(_ChatMessage msg) {
    return Align(alignment: msg.isUser ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(margin: const EdgeInsets.only(bottom: 12), padding: const EdgeInsets.all(14),
        constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.78),
        decoration: BoxDecoration(
          gradient: msg.isUser ? PixoraTheme.primaryGradient : null,
          color: msg.isUser ? null : Colors.white,
          borderRadius: BorderRadius.only(topLeft: const Radius.circular(16), topRight: const Radius.circular(16),
            bottomLeft: Radius.circular(msg.isUser ? 16 : 4), bottomRight: Radius.circular(msg.isUser ? 4 : 16)),
          boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8, offset: const Offset(0, 2))]),
        child: Text(msg.text, style: TextStyle(color: msg.isUser ? Colors.white : PixoraTheme.dark, fontSize: 14, height: 1.4)))).animate().fadeIn(duration: 300.ms).slideY(begin: 0.1, end: 0);
  }

  Widget _typingIndicator() {
    return Align(alignment: Alignment.centerLeft, child: Container(margin: const EdgeInsets.only(bottom: 12), padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 8)]),
      child: Row(mainAxisSize: MainAxisSize.min, children: List.generate(3, (i) => Container(width: 8, height: 8, margin: const EdgeInsets.symmetric(horizontal: 2),
        decoration: BoxDecoration(color: PixoraTheme.gray.withValues(alpha: 0.4), shape: BoxShape.circle)).animate(delay: Duration(milliseconds: i * 200)).fadeIn().then().shimmer(duration: 1000.ms)))));
  }

  Widget _buildInput() {
    return Container(padding: EdgeInsets.fromLTRB(16, 12, 16, MediaQuery.of(context).padding.bottom + 12),
      decoration: BoxDecoration(color: Colors.white, boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 10, offset: const Offset(0, -2))]),
      child: Row(children: [
        Expanded(child: TextField(controller: _controller, textCapitalization: TextCapitalization.sentences, onSubmitted: (_) => _send(),
          decoration: InputDecoration(hintText: 'Tanya sesuatu...', filled: true, fillColor: PixoraTheme.lightGray, border: OutlineInputBorder(borderRadius: BorderRadius.circular(24), borderSide: BorderSide.none),
            contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12)))),
        const SizedBox(width: 8),
        GestureDetector(onTap: _send, child: Container(width: 48, height: 48, decoration: const BoxDecoration(gradient: PixoraTheme.primaryGradient, shape: BoxShape.circle),
          child: const Icon(Icons.send_rounded, color: Colors.white, size: 22))),
      ]));
  }
}

class _ChatMessage {
  final String text;
  final bool isUser;
  _ChatMessage({required this.text, required this.isUser});
}
