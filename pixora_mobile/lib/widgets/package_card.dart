// lib/widgets/package_card.dart
// Card widget untuk menampilkan paket fotografi

import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../config/theme.dart';
import '../models/package.dart';

class PackageCard extends StatelessWidget {
  final Package package;
  final VoidCallback? onTap;

  const PackageCard({
    super.key,
    required this.package,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(PixoraTheme.radiusLg),
          boxShadow: PixoraTheme.softShadow,
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                CachedNetworkImage(
                  imageUrl: package.defaultImageUrl,
                  height: 180,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    height: 180,
                    color: PixoraTheme.lightGray,
                    child: const Center(
                      child: CircularProgressIndicator(
                        color: PixoraTheme.primaryRose,
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    height: 180,
                    color: PixoraTheme.lightGray,
                    child: const Icon(Icons.image, size: 40, color: PixoraTheme.gray),
                  ),
                ),
                Positioned(
                  top: 12,
                  right: 12,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: PixoraTheme.primaryRose,
                      borderRadius:
                          BorderRadius.circular(PixoraTheme.radiusFull),
                    ),
                    child: const Text(
                      'Best Deal',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),

            // Content
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    package.name,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: PixoraTheme.dark,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    package.description,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 13,
                      color: PixoraTheme.gray,
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Specs
                  Row(
                    children: [
                      _specItem(Icons.access_time, '${package.durationHours} jam'),
                      const SizedBox(width: 16),
                      _specItem(Icons.photo_library, '${package.editedPhotos} foto'),
                      const SizedBox(width: 16),
                      _specItem(Icons.location_on, package.locationLabel),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Price
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            package.formattedPrice,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: PixoraTheme.primaryRose,
                            ),
                          ),
                          if (package.formattedDownPayment != null)
                            Text(
                              'DP: ${package.formattedDownPayment}',
                              style: const TextStyle(
                                fontSize: 12,
                                color: PixoraTheme.gray,
                              ),
                            ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 8),
                        decoration: BoxDecoration(
                          gradient: PixoraTheme.primaryGradient,
                          borderRadius:
                              BorderRadius.circular(PixoraTheme.radiusMd),
                        ),
                        child: const Text(
                          'Pilih',
                          style: TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _specItem(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: PixoraTheme.primaryRose),
        const SizedBox(width: 4),
        Flexible(
          child: Text(
            text,
            style: const TextStyle(fontSize: 11, color: PixoraTheme.gray),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
