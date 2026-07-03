import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

class CategoryGridBlock extends StatelessWidget {
  final Map<String, dynamic> content;
  const CategoryGridBlock({super.key, required this.content});

  @override
  Widget build(BuildContext context) {
    final categories =
        (content['categories'] is List ? content['categories'] as List : [])
            .whereType<Map>()
            .where((c) => c['name'] is String)
            .toList();
    if (categories.isEmpty) return const SizedBox.shrink();

    return SizedBox(
      height: 92,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        itemCount: categories.length,
        separatorBuilder: (_, __) => const SizedBox(width: 14),
        itemBuilder: (context, index) {
          final category = categories[index];
          final image = category['image'];
          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xFFF7F9FB),
                  border: Border.all(color: const Color(0x33C5A880)),
                ),
                clipBehavior: Clip.antiAlias,
                child: image is String && image.isNotEmpty
                    ? CachedNetworkImage(
                        imageUrl: image,
                        fit: BoxFit.cover,
                        errorWidget: (_, __, ___) => const SizedBox.shrink(),
                      )
                    : Center(
                        child: Text(
                          (category['name'] as String).isNotEmpty
                              ? (category['name'] as String)[0]
                              : '?',
                          style: const TextStyle(
                              color: Color(0xFF141923),
                              fontWeight: FontWeight.bold),
                        ),
                      ),
              ),
              const SizedBox(height: 4),
              SizedBox(
                width: 64,
                child: Text(
                  category['name'],
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 10, color: Color(0xFF141923)),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
