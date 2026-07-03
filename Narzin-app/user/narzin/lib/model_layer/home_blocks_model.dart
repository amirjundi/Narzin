import 'package:flutter/material.dart';

class HomeBlocksModel {
  bool? status;
  List<HomeBlock> blocks = [];

  HomeBlocksModel({this.status, List<HomeBlock>? blocks})
      : blocks = blocks ?? [];

  HomeBlocksModel.fromJson(Map<String, dynamic> json) {
    status = json['status'] == true;
    final data = json['data'];
    if (data is List) {
      for (final entry in data) {
        final block = HomeBlock.tryParse(entry);
        if (block != null) blocks.add(block);
      }
    }
  }
}

class HomeBlock {
  final int id;
  final String type;
  final Map<String, dynamic> content;

  HomeBlock({required this.id, required this.type, required this.content});

  /// Returns null for anything malformed — a bad block must never crash the feed.
  static HomeBlock? tryParse(dynamic entry) {
    if (entry is! Map) return null;
    final type = entry['type'];
    final id = entry['id'];
    if (type is! String || type.isEmpty || id is! int) return null;
    final rawContent = entry['content'];
    return HomeBlock(
      id: id,
      type: type,
      content: rawContent is Map
          ? Map<String, dynamic>.from(rawContent)
          : <String, dynamic>{},
    );
  }
}

class BlockLink {
  final String type;
  final dynamic value;

  BlockLink({required this.type, required this.value});

  static BlockLink? fromJson(dynamic json) {
    if (json is! Map) return null;
    final type = json['type'];
    if (type is! String || !['category', 'product', 'url'].contains(type)) {
      return null;
    }
    return BlockLink(type: type, value: json['value']);
  }
}

Color parseHexColor(String? hex, Color fallback) {
  if (hex == null) return fallback;
  final match = RegExp(r'^#([0-9a-fA-F]{6})$').firstMatch(hex);
  if (match == null) return fallback;
  return Color(int.parse('FF${match.group(1)}', radix: 16));
}
