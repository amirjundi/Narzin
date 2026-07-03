import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/model_layer/home_blocks_model.dart';

void main() {
  test('parses a feed and keeps unknown types as raw blocks', () {
    final model = HomeBlocksModel.fromJson({
      'status': true,
      'data': [
        {
          'id': 1,
          'type': 'announcement_bar',
          'content': {'text': 'مرحبا', 'bg_color': '#141923'}
        },
        {'id': 2, 'type': 'from_the_future', 'content': {}},
      ],
    });

    expect(model.status, true);
    expect(model.blocks.length, 2);
    expect(model.blocks[0].type, 'announcement_bar');
    expect(model.blocks[0].content['text'], 'مرحبا');
    expect(model.blocks[1].type, 'from_the_future');
  });

  test('drops malformed entries instead of throwing', () {
    final model = HomeBlocksModel.fromJson({
      'status': true,
      'data': [
        'not-a-map',
        {'id': 'x'}, // no type
        {'id': 3, 'type': 'popup', 'content': null},
      ],
    });
    expect(model.blocks.length, 1);
    expect(model.blocks[0].type, 'popup');
    expect(model.blocks[0].content, isEmpty);
  });

  test('BlockLink parses valid links and rejects junk', () {
    expect(BlockLink.fromJson({'type': 'product', 'value': 7})?.type, 'product');
    expect(BlockLink.fromJson({'type': 'product', 'value': 7})?.value, 7);
    expect(BlockLink.fromJson(null), isNull);
    expect(BlockLink.fromJson('nope'), isNull);
    expect(BlockLink.fromJson({'value': 1}), isNull);
  });

  test('parseHexColor parses #RRGGBB and falls back on junk', () {
    expect(parseHexColor('#141923', Colors.red).toARGB32(), 0xFF141923);
    expect(parseHexColor('nope', Colors.red), Colors.red);
    expect(parseHexColor(null, Colors.red), Colors.red);
  });
}
