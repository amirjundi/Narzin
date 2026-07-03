import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../../core/home_popup_gate.dart';
import '../../../../model_layer/home_blocks_model.dart';
import 'block_link.dart';

Future<void> maybeShowHomePopup(
    BuildContext context, List<HomeBlock> blocks) async {
  HomeBlock? popupBlock;
  for (final block in blocks) {
    if (block.type == 'popup') {
      popupBlock = block;
      break;
    }
  }
  if (popupBlock == null) return;
  final title = popupBlock.content['title'];
  if (title is! String || title.isEmpty) return;

  final prefs = await SharedPreferences.getInstance();
  if (!HomePopupGate.shouldShow(popupBlock, prefs)) return;

  final delaySeconds = popupBlock.content['delay_seconds'];
  await Future.delayed(
      Duration(seconds: delaySeconds is num ? delaySeconds.toInt() : 3));
  if (!context.mounted) return;

  await HomePopupGate.markShown(popupBlock, prefs);
  final content = popupBlock.content;

  if (!context.mounted) return;
  await showModalBottomSheet(
    context: context,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
    ),
    builder: (sheetContext) => SafeArea(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (content['image'] is String &&
              (content['image'] as String).isNotEmpty)
            ClipRRect(
              borderRadius:
                  const BorderRadius.vertical(top: Radius.circular(18)),
              child: CachedNetworkImage(
                imageUrl: content['image'],
                height: 170,
                width: double.infinity,
                fit: BoxFit.cover,
                errorWidget: (_, __, ___) => const SizedBox.shrink(),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(title,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF141923))),
                if (content['text'] is String &&
                    (content['text'] as String).isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 6),
                    child: Text(content['text'],
                        textAlign: TextAlign.center,
                        style:
                            TextStyle(fontSize: 13, color: Colors.grey[700])),
                  ),
                if (content['button_label'] is String &&
                    (content['button_label'] as String).isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 14),
                    child: SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF141923),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(24)),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                        onPressed: () {
                          Navigator.pop(sheetContext);
                          handleBlockLink(context, content['link']);
                        },
                        child: Text(content['button_label']),
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}
