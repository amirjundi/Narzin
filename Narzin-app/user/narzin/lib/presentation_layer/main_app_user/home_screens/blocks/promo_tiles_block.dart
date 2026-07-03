import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import 'block_link.dart';

class PromoTilesBlock extends StatelessWidget {
  final Map<String, dynamic> content;
  const PromoTilesBlock({super.key, required this.content});

  @override
  Widget build(BuildContext context) {
    final tiles = (content['tiles'] is List ? content['tiles'] as List : [])
        .whereType<Map>()
        .where((tile) => tile['image'] is String && (tile['image'] as String).isNotEmpty)
        .toList();
    if (tiles.isEmpty) return const SizedBox.shrink();

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Row(
        children: [
          for (final tile in tiles)
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 2),
                child: GestureDetector(
                  onTap: () => handleBlockLink(context, tile['link']),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: Stack(
                      children: [
                        AspectRatio(
                          aspectRatio: 4 / 3,
                          child: CachedNetworkImage(
                            imageUrl: tile['image'],
                            fit: BoxFit.cover,
                            errorWidget: (_, __, ___) =>
                                Container(color: const Color(0xFFF7F9FB)),
                          ),
                        ),
                        if (tile['label'] is String &&
                            (tile['label'] as String).isNotEmpty)
                          PositionedDirectional(
                            bottom: 6,
                            start: 6,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 6, vertical: 3),
                              decoration: BoxDecoration(
                                color: const Color(0xCC141923),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(tile['label'],
                                  style: const TextStyle(
                                      fontSize: 10,
                                      color: Color(0xFFC5A880))),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
