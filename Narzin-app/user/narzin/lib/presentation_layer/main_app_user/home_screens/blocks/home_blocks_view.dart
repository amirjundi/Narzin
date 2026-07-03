import 'package:flutter/material.dart';

import '../../../../model_layer/home_blocks_model.dart';
import 'announcement_bar_block.dart';
import 'category_grid_block.dart';
import 'info_strip_block.dart';
import 'promo_tiles_block.dart';

class HomeBlocksView extends StatelessWidget {
  final List<HomeBlock> blocks;
  const HomeBlocksView({super.key, required this.blocks});

  Widget? _buildBlock(HomeBlock block) {
    switch (block.type) {
      case 'announcement_bar':
        return AnnouncementBarBlock(content: block.content);
      case 'info_strip':
        return InfoStripBlock(content: block.content);
      case 'promo_tiles':
        return PromoTilesBlock(content: block.content);
      case 'category_grid':
        return CategoryGridBlock(content: block.content);
      // popup renders as an overlay (home_popup.dart), never inline.
      // hero_slider / product_rail / countdown_banner arrive in Task 4.
      default:
        return null;
    }
  }

  @override
  Widget build(BuildContext context) {
    final children = <Widget>[];
    for (final block in blocks) {
      final widget = _buildBlock(block);
      if (widget != null) {
        children.add(Padding(
          padding: const EdgeInsets.only(bottom: 4),
          child: widget,
        ));
      }
    }
    return Column(mainAxisSize: MainAxisSize.min, children: children);
  }
}
