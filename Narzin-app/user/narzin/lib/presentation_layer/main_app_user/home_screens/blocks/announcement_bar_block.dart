import 'package:flutter/material.dart';

import '../../../../model_layer/home_blocks_model.dart';

class AnnouncementBarBlock extends StatelessWidget {
  final Map<String, dynamic> content;
  const AnnouncementBarBlock({super.key, required this.content});

  @override
  Widget build(BuildContext context) {
    final text = content['text'];
    if (text is! String || text.isEmpty) return const SizedBox.shrink();
    return Container(
      width: double.infinity,
      color: parseHexColor(content['bg_color'], const Color(0xFF141923)),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Text(
        text,
        textAlign: TextAlign.center,
        style: TextStyle(
          fontSize: 12,
          color: parseHexColor(content['text_color'], const Color(0xFFC5A880)),
        ),
      ),
    );
  }
}
