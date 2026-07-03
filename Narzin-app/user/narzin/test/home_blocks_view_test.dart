import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:network_image_mock/network_image_mock.dart';
import 'package:narzin/model_layer/home_blocks_model.dart';
import 'package:narzin/presentation_layer/main_app_user/home_screens/blocks/home_blocks_view.dart';

Widget wrap(Widget child) => MaterialApp(
  home: Scaffold(
    body: SingleChildScrollView(child: child),
  ),
);

void main() {
  testWidgets('renders known blocks and skips unknown + popup types',
      (tester) async {
    final blocks = [
      HomeBlock(id: 1, type: 'announcement_bar', content: {'text': 'Free shipping'}),
      HomeBlock(id: 2, type: 'weird_future_type', content: {}),
      HomeBlock(id: 3, type: 'popup', content: {'title': 'Nope'}),
      HomeBlock(id: 4, type: 'info_strip', content: {
        'items': [
          {'icon': 'truck', 'text': 'Fast delivery', 'link': null},
        ]
      }),
    ];

    await mockNetworkImagesFor(() => tester.pumpWidget(wrap(HomeBlocksView(blocks: blocks))));

    expect(find.text('Free shipping'), findsOneWidget);
    expect(find.text('Fast delivery'), findsOneWidget);
    expect(find.text('Nope'), findsNothing);
  });

  testWidgets('promo tiles and category grid render labels/names',
      (tester) async {
    final blocks = [
      HomeBlock(id: 5, type: 'promo_tiles', content: {
        'tiles': [
          {'image': 'https://x.test/t.jpg', 'label': 'Summer', 'link': null}
        ]
      }),
      HomeBlock(id: 6, type: 'category_grid', content: {
        'categories': [
          {'id': 1, 'name': 'Kleider', 'image': null}
        ]
      }),
    ];

    await mockNetworkImagesFor(() async {
      await tester.pumpWidget(wrap(HomeBlocksView(blocks: blocks)));
      await tester.pump();
    });

    expect(find.text('Summer'), findsOneWidget);
    expect(find.text('Kleider'), findsOneWidget);
  });

  testWidgets('product rail renders names and dual prices', (tester) async {
    final blocks = [
      HomeBlock(id: 7, type: 'product_rail', content: {
        'title': 'Super Deals',
        'products': [
          {
            'id': 21,
            'name_arabic': 'فستان',
            'name_german': 'Kleid',
            'image': null,
            'min_price': 49.99,
            'min_price_iqd': 72500,
          }
        ]
      }),
    ];

    await tester.pumpWidget(wrap(HomeBlocksView(blocks: blocks)));

    expect(find.text('Super Deals'), findsOneWidget);
    expect(find.text('Kleid'), findsOneWidget);
    expect(find.text('€49.99'), findsOneWidget);
    expect(find.textContaining('IQD'), findsOneWidget);
  });

  testWidgets('countdown banner shows for future dates and hides for past',
      (tester) async {
    final future = DateTime.now().add(const Duration(hours: 2)).toIso8601String();
    final past = DateTime.now().subtract(const Duration(hours: 2)).toIso8601String();
    await tester.pumpWidget(wrap(HomeBlocksView(blocks: [
      HomeBlock(id: 8, type: 'countdown_banner', content: {'text': 'Sale ends', 'ends_at': future}),
      HomeBlock(id: 9, type: 'countdown_banner', content: {'text': 'Old sale', 'ends_at': past}),
    ])));
    await tester.pump();

    expect(find.text('Sale ends'), findsOneWidget);
    expect(find.text('Old sale'), findsNothing);
    // let pending timers fire before teardown
    await tester.pump(const Duration(seconds: 2));
  });
}
