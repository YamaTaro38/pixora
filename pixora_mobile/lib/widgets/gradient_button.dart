// lib/widgets/gradient_button.dart
// Tombol gradient khas Pixora (rose-pink)

import 'package:flutter/material.dart';
import '../config/theme.dart';

class GradientButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final IconData? icon;
  final double? width;
  final EdgeInsets? padding;
  final double borderRadius;

  const GradientButton({
    super.key,
    required this.text,
    this.onPressed,
    this.isLoading = false,
    this.icon,
    this.width,
    this.padding,
    this.borderRadius = 12,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width ?? double.infinity,
      decoration: BoxDecoration(
        gradient: onPressed != null
            ? PixoraTheme.primaryGradient
            : const LinearGradient(
                colors: [Color(0xFFD1D5DB), Color(0xFF9CA3AF)]),
        borderRadius: BorderRadius.circular(borderRadius),
        boxShadow: onPressed != null ? PixoraTheme.roseShadow : null,
      ),
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          padding: padding ??
              const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(borderRadius),
          ),
        ),
        child: isLoading
            ? const SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 20, color: Colors.white),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    text,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
