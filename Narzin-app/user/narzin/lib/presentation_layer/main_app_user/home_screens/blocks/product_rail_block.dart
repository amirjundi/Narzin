import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import 'block_link.dart';

class ProductRailBlock extends StatelessWidget {
  final Map<String, dynamic> content;
  const ProductRailBlock({super.key, required this.content});

  @override
  Widget build(BuildContext context) {
    final products =
        (content['products'] is List ? content['products'] as List : [])
            .whereType<Map>()
            .where((p) => p['id'] is int)
            .toList();
    if (products.isEmpty) return const SizedBox.shrink();

    final isArabic = Localizations.localeOf(context).languageCode == 'ar';
    final title = content['title'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (title is String && title.isNotEmpty)
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 8, 12, 6),
            child: Text(title,
                style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF141923))),
          ),
        SizedBox(
          height: 236,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12),
            itemCount: products.length,
            separatorBuilder: (_, __) => const SizedBox(width: 10),
            itemBuilder: (context, index) {
              final product = products[index];
              final name = isArabic
                  ? (product['name_arabic'] ?? product['name_german'])
                  : (product['name_german'] ?? product['name_arabic']);
              final image = product['image'];
              final iqd = product['min_price_iqd'];

              return GestureDetector(
                onTap: () => handleBlockLink(
                    context, {'type': 'product', 'value': product['id']}),
                child: SizedBox(
                  width: 132,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: AspectRatio(
                          aspectRatio: 3 / 4,
                          child: image is String && image.isNotEmpty
                              ? CachedNetworkImage(
                                  imageUrl: image,
                                  fit: BoxFit.cover,
                                  errorWidget: (_, __, ___) =>
                                      Container(color: const Color(0xFFF7F9FB)),
                                )
                              : Container(color: const Color(0xFFF7F9FB)),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text('${name ?? ''}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                              fontSize: 12, color: Color(0xFF141923))),
                      if ((product['min_price'] as num?)?.toStringAsFixed(2) case final formatted?)
                        Text('€$formatted',
                            style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF141923))),
                      if (iqd is num)
                        Text('${NumberFormat('#,##0', 'en_US').format(iqd)} IQD',
                            style: TextStyle(
                                fontSize: 10, color: Colors.grey[600])),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }
}
