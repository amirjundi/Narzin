import 'package:flutter/material.dart';

class InfoStripBlock extends StatelessWidget {
  final Map<String, dynamic> content;
  const InfoStripBlock({super.key, required this.content});

  static const _icons = {
    'truck': Icons.local_shipping_outlined,
    'shield': Icons.verified_user_outlined,
    'star': Icons.star_outline,
    'returns': Icons.replay_outlined,
    'support': Icons.headset_mic_outlined,
    'tag': Icons.sell_outlined,
  };

  @override
  Widget build(BuildContext context) {
    final items = content['items'];
    if (items is! List || items.isEmpty) return const SizedBox.shrink();
    return Container(
      color: const Color(0xFFC5A880).withValues(alpha: 0.12),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Wrap(
        spacing: 16,
        runSpacing: 6,
        alignment: WrapAlignment.center,
        children: [
          for (final item in items)
            if (item is Map && item['text'] is String)
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(_icons[item['icon']] ?? Icons.sell_outlined,
                      size: 15, color: const Color(0xFFC5A880)),
                  const SizedBox(width: 4),
                  Text(item['text'],
                      style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF141923))),
                ],
              ),
        ],
      ),
    );
  }
}
