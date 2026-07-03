# Homepage Content Blocks — Phase 3 (Flutter Renderer) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** The Flutter user app renders the admin-composed homepage from `GET /api/v1/home?platform=app`, replacing the legacy banners carousel — with graceful fallback to the current home screen when the feed is unavailable.

**Architecture:** Follow the app's existing conventions exactly (cubit per feature using raw `http` + hand-written `fromJson` models, `Constants.apiBaseUrl`, `S.of(context)` intl, `locale == 'ar'` name switching). A new `HomeBlocksCubit` fetches the feed; a `HomeBlocksView` widget maps block types to widgets (unknown types → `SizedBox.shrink()`); `home_screen.dart` swaps its scroll body to `HomeBlocksView` when blocks are loaded and non-empty, otherwise keeps its existing legacy body untouched (fallback). Popup uses `shared_preferences` frequency capping.

**Tech Stack:** Flutter (project pins: flutter_bloc ^8.1.6, http, shared_preferences ^2.3.3, cached_network_image ^3.4.1, intl via `S`). NO new dependencies — `url` links are rendered non-tappable in this phase (url_launcher is not in pubspec; defer).

**Spec:** `docs/superpowers/specs/2026-07-02-homepage-blocks-design.md` §7. Backend (Phase 1) is live.

## Global Constraints

- All work in `Narzin-app/user/narzin/`; run every command from `C:\xampp\htdocs\Narzin\Narzin-app\user\narzin`. Git repo root is `C:\xampp\htdocs\Narzin`.
- API: `GET ${Constants.apiBaseUrl}home?platform=app&locale={locale}` (apiBaseUrl already ends with `/api/v1/`) → `{status: true, data: [{id, type, content}]}`. Locale param: the app's locale string (`ar`, `de`, `en` — pass as-is; server normalizes unknowns to ar).
- Resolved content shapes (text pre-localized to plain strings; images are absolute URLs):
  - announcement_bar `{text, link, bg_color, text_color}` · popup `{image, title, text, button_label, link, frequency:{mode,days}, delay_seconds}` · hero_slider `{slides:[{image, title, subtitle, link}]}` · category_grid `{categories:[{id, name, image}]}` · product_rail `{title, rule, products:[{id, name_arabic, name_german, image, min_price, min_price_iqd, min_price_variant_id}]}` · countdown_banner `{text, ends_at ISO8601, link, image, bg_color, text_color}` · info_strip `{items:[{icon, text, link}]}` · promo_tiles `{tiles:[{image, label, link}]}`.
  - Links `{type: 'category'|'product'|'url', value}` or null. In this phase: product → push `ProductDetailsScreen(productId: value)`; category and url → NOT tappable (category navigation is bound to cubit-driven screens; wire in a later polish pass).
- Unknown block types must be skipped silently (forward compatibility) — at the widget-mapping level, never a parse crash.
- Colors: parse `#RRGGBB` strings defensively (fall back to navy `0xFF141923` / sand `0xFFC5A880` / gold `0xFFD4AF37`).
- Popup: max one per app session run; `once_per_session` via an in-memory static flag; `once_per_days` via shared_preferences key `home_popup_seen_{id}` storing epoch ms; respects `delay_seconds`.
- Product names: `locale == 'ar' ? name_arabic : (name_german ?? name_arabic)` (matches existing home_screen convention). Dual price: `€{min_price}` primary, `{min_price_iqd} IQD` secondary.
- Gates for every task: `flutter analyze` introduces NO NEW issues in files you created/touched (pre-existing issues elsewhere are not yours) AND `flutter test` passes.
- Commits: `feat(app-home): ...`, each ending with the trailer line `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.
- Do not restructure `home_screen.dart` beyond the integration point described in Task 5; do not modify BannersCubit/ProductsCubit.

## File Structure

**Created:**
- `lib/model_layer/home_blocks_model.dart` — feed + per-type content parsing
- `lib/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart`, `home_blocks_state.dart`
- `lib/core/home_popup_gate.dart` — frequency capping logic (pure + shared_preferences)
- `lib/presentation_layer/main_app_user/home_screens/blocks/block_link.dart` — tap handler
- `.../blocks/home_blocks_view.dart` — renderer (type → widget)
- `.../blocks/announcement_bar_block.dart`, `hero_slider_block.dart`, `category_grid_block.dart`, `product_rail_block.dart`, `countdown_banner_block.dart`, `info_strip_block.dart`, `promo_tiles_block.dart`, `home_popup.dart`
- `test/home_blocks_model_test.dart`, `test/home_popup_gate_test.dart`, `test/home_blocks_view_test.dart`

**Modified:**
- `lib/presentation_layer/main_app_user/home_screens/home_screen.dart` (integration only), `lib/main.dart` (BlocProvider registration — follow how BannersCubit is provided)

---

### Task 1: `home_blocks_model.dart` + parsing tests

**Files:**
- Create: `Narzin-app/user/narzin/lib/model_layer/home_blocks_model.dart`
- Test: `Narzin-app/user/narzin/test/home_blocks_model_test.dart`

**Interfaces:**
- Produces: `HomeBlocksModel {bool? status; List<HomeBlock> blocks}` with `HomeBlocksModel.fromJson(Map)`; `HomeBlock {int id; String type; Map<String, dynamic> content}`; `BlockLink? BlockLink.fromJson(dynamic)` (`type`, `value`) returning null for null/invalid; helper `Color parseHexColor(String? hex, Color fallback)`. Parsing must NEVER throw on malformed entries — bad entries are dropped.

- [ ] **Step 1: Write the failing tests**

Create `Narzin-app/user/narzin/test/home_blocks_model_test.dart`:

```dart
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
    expect(parseHexColor('#141923', Colors.red).value, 0xFF141923);
    expect(parseHexColor('nope', Colors.red), Colors.red);
    expect(parseHexColor(null, Colors.red), Colors.red);
  });
}
```

- [ ] **Step 2: Run to verify failure**

Run: `flutter test test/home_blocks_model_test.dart`
Expected: FAIL — file not found / classes undefined.

- [ ] **Step 3: Implement**

Create `Narzin-app/user/narzin/lib/model_layer/home_blocks_model.dart`:

```dart
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
```

- [ ] **Step 4: Run tests — pass**

Run: `flutter test test/home_blocks_model_test.dart` → 4 tests pass.
Run: `flutter analyze lib/model_layer/home_blocks_model.dart test/home_blocks_model_test.dart` → no issues in these files.

- [ ] **Step 5: Commit**

```bash
git add "Narzin-app/user/narzin/lib/model_layer/home_blocks_model.dart" "Narzin-app/user/narzin/test/home_blocks_model_test.dart"
git commit -m "feat(app-home): home blocks model with crash-safe parsing"
```

---

### Task 2: HomeBlocksCubit + popup gate

**Files:**
- Create: `Narzin-app/user/narzin/lib/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart`, `home_blocks_state.dart`, `Narzin-app/user/narzin/lib/core/home_popup_gate.dart`
- Test: `Narzin-app/user/narzin/test/home_popup_gate_test.dart`

**Interfaces:**
- Produces:
  - `HomeBlocksCubit extends Cubit<HomeBlocksState>` with `Future<void> getHomeBlocks({required String locale})` → emits `HomeBlocksLoading` → `HomeBlocksLoaded(blocks)` on success (status true) or `HomeBlocksError()` on any failure/exception. Field `List<HomeBlock> blocks` retained on the cubit (matches app convention of cubit-held data). Uses `http.get('${Constants.apiBaseUrl}home?platform=app&locale=$locale')`, no auth header (public endpoint), NO toast on failure (the screen silently falls back).
  - `HomePopupGate.shouldShow(HomeBlock popupBlock, SharedPreferences prefs, {DateTime? now})` / `markShown(...)` implementing the Global Constraints rules; static in-memory `shownThisRun` set for once_per_session.

- [ ] **Step 1: Write the failing tests**

Create `Narzin-app/user/narzin/test/home_popup_gate_test.dart`:

```dart
import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/core/home_popup_gate.dart';
import 'package:narzin/model_layer/home_blocks_model.dart';
import 'package:shared_preferences/shared_preferences.dart';

HomeBlock popup(int id, String mode, {int days = 0}) => HomeBlock(
      id: id,
      type: 'popup',
      content: {
        'frequency': {'mode': mode, 'days': days}
      },
    );

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    HomePopupGate.resetForTests();
  });

  test('session popup shows once per app run', () async {
    final prefs = await SharedPreferences.getInstance();
    final block = popup(5, 'once_per_session');
    expect(HomePopupGate.shouldShow(block, prefs), true);
    await HomePopupGate.markShown(block, prefs);
    expect(HomePopupGate.shouldShow(block, prefs), false);
  });

  test('days popup respects the N-day window', () async {
    final prefs = await SharedPreferences.getInstance();
    final block = popup(6, 'once_per_days', days: 7);
    final now = DateTime.utc(2026, 7, 3);
    expect(HomePopupGate.shouldShow(block, prefs, now: now), true);
    await HomePopupGate.markShown(block, prefs, now: now);
    HomePopupGate.resetForTests(); // new app run, but prefs persist
    expect(
        HomePopupGate.shouldShow(block, prefs,
            now: now.add(const Duration(days: 6))),
        false);
    expect(
        HomePopupGate.shouldShow(block, prefs,
            now: now.add(const Duration(days: 8))),
        true);
  });

  test('blocks are independent per id', () async {
    final prefs = await SharedPreferences.getInstance();
    await HomePopupGate.markShown(popup(5, 'once_per_session'), prefs);
    expect(HomePopupGate.shouldShow(popup(9, 'once_per_session'), prefs), true);
  });
}
```

- [ ] **Step 2: Run to verify failure**

Run: `flutter test test/home_popup_gate_test.dart` → FAIL (missing files).

- [ ] **Step 3: Implement the gate**

Create `Narzin-app/user/narzin/lib/core/home_popup_gate.dart`:

```dart
import 'package:shared_preferences/shared_preferences.dart';

import '../model_layer/home_blocks_model.dart';

class HomePopupGate {
  static final Set<int> _shownThisRun = {};

  static String _key(HomeBlock block) => 'home_popup_seen_${block.id}';

  static bool shouldShow(HomeBlock block, SharedPreferences prefs,
      {DateTime? now}) {
    final frequency = block.content['frequency'];
    final mode =
        frequency is Map ? frequency['mode'] ?? 'once_per_session' : 'once_per_session';

    if (mode == 'once_per_days') {
      final seenAtMs = prefs.getInt(_key(block));
      if (seenAtMs == null) return true;
      final days = frequency is Map ? (frequency['days'] ?? 0) as num : 0;
      final elapsed = (now ?? DateTime.now())
          .difference(DateTime.fromMillisecondsSinceEpoch(seenAtMs, isUtc: true));
      return elapsed.inMilliseconds >= days * 24 * 60 * 60 * 1000;
    }
    return !_shownThisRun.contains(block.id);
  }

  static Future<void> markShown(HomeBlock block, SharedPreferences prefs,
      {DateTime? now}) async {
    _shownThisRun.add(block.id);
    await prefs.setInt(
        _key(block), (now ?? DateTime.now()).toUtc().millisecondsSinceEpoch);
  }

  static void resetForTests() => _shownThisRun.clear();
}
```

- [ ] **Step 4: Implement the cubit**

Create `Narzin-app/user/narzin/lib/bussiness_logic/home_blocks_cubits/home_blocks_state.dart`:

```dart
part of 'home_blocks_cubit.dart';

@immutable
abstract class HomeBlocksState {}

class HomeBlocksInitial extends HomeBlocksState {}

class HomeBlocksLoading extends HomeBlocksState {}

class HomeBlocksLoaded extends HomeBlocksState {}

class HomeBlocksError extends HomeBlocksState {}
```

Create `Narzin-app/user/narzin/lib/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart`:

```dart
import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../../core/constants.dart';
import '../../model_layer/home_blocks_model.dart';

part 'home_blocks_state.dart';

class HomeBlocksCubit extends Cubit<HomeBlocksState> {
  HomeBlocksCubit() : super(HomeBlocksInitial());

  List<HomeBlock> blocks = [];

  Future<void> getHomeBlocks({required String locale}) async {
    final apiUrl =
        '${Constants.apiBaseUrl}home?platform=app&locale=$locale';
    emit(HomeBlocksLoading());
    try {
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {'Accept': 'application/json'},
      );
      if (response.statusCode == 200) {
        final model = HomeBlocksModel.fromJson(
            json.decode(response.body) as Map<String, dynamic>);
        if (model.status == true) {
          blocks = model.blocks;
          emit(HomeBlocksLoaded());
          return;
        }
      }
      emit(HomeBlocksError());
    } catch (e) {
      debugPrint('home blocks fetch failed: $e');
      emit(HomeBlocksError());
    }
  }
}
```

- [ ] **Step 5: Run gates**

Run: `flutter test test/home_popup_gate_test.dart test/home_blocks_model_test.dart` → all pass.
Run: `flutter analyze lib/bussiness_logic/home_blocks_cubits lib/core/home_popup_gate.dart` → no issues in these files.

- [ ] **Step 6: Commit**

```bash
git add "Narzin-app/user/narzin/lib/bussiness_logic/home_blocks_cubits" "Narzin-app/user/narzin/lib/core/home_popup_gate.dart" "Narzin-app/user/narzin/test/home_popup_gate_test.dart"
git commit -m "feat(app-home): home blocks cubit and popup frequency gate"
```

---

### Task 3: Renderer + simple blocks (announcement, info strip, promo tiles, category grid) + link handler

**Files:**
- Create: `Narzin-app/user/narzin/lib/presentation_layer/main_app_user/home_screens/blocks/block_link.dart`, `home_blocks_view.dart`, `announcement_bar_block.dart`, `info_strip_block.dart`, `promo_tiles_block.dart`, `category_grid_block.dart`
- Test: `Narzin-app/user/narzin/test/home_blocks_view_test.dart`

**Interfaces:**
- Consumes: `HomeBlock`, `BlockLink`, `parseHexColor` (Task 1).
- Produces:
  - `void handleBlockLink(BuildContext context, dynamic rawLink)` — product → `Navigator.push(... ProductDetailsScreen(productId: value))` guarding non-int values; category/url/null → no-op (this phase).
  - `HomeBlocksView({required List<HomeBlock> blocks})` — a `ListView`/`Column` of mapped widgets; announcement_bar and hero etc. mapped via a private `Widget? _buildBlock(HomeBlock)`; unknown types → null (skipped). Popup blocks are NOT rendered inline (Task 5 handles them).
  - The four simple block widgets, each taking `Map<String, dynamic> content`.

- [ ] **Step 1: Write the failing tests**

Create `Narzin-app/user/narzin/test/home_blocks_view_test.dart`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:narzin/model_layer/home_blocks_model.dart';
import 'package:narzin/presentation_layer/main_app_user/home_screens/blocks/home_blocks_view.dart';

Widget wrap(Widget child) => MaterialApp(home: Scaffold(body: child));

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

    await tester.pumpWidget(wrap(HomeBlocksView(blocks: blocks)));

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

    await tester.pumpWidget(wrap(HomeBlocksView(blocks: blocks)));
    await tester.pump();

    expect(find.text('Summer'), findsOneWidget);
    expect(find.text('Kleider'), findsOneWidget);
  });
}
```

Note: network images must not be fetched in widget tests — use `CachedNetworkImage` only behind null/empty guards, and in `promo_tiles_block.dart`/others always check `image` non-empty before creating it; the test above uses an https URL for the tile and does NOT call `pumpAndSettle`, so the label assertion normally passes before any fetch resolves. If `cached_network_image` async errors still fail a test in this suite, add the dev_dependency `network_image_mock` and wrap the pumps in `mockNetworkImagesFor(() => tester.pumpWidget(...))` — record that as a deviation in your report.

- [ ] **Step 2: Run to verify failure**

Run: `flutter test test/home_blocks_view_test.dart` → FAIL (missing files).

- [ ] **Step 3: Implement block_link.dart**

Create `.../blocks/block_link.dart`:

```dart
import 'package:flutter/material.dart';

import '../../../../model_layer/home_blocks_model.dart';
import '../../products_screens/product_details_screen.dart';

/// Phase-3 scope: only product links navigate. Category/url links are
/// rendered non-tappable until the category screen exposes a direct route.
void handleBlockLink(BuildContext context, dynamic rawLink) {
  final link = BlockLink.fromJson(rawLink);
  if (link == null) return;
  if (link.type == 'product' && link.value is int) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => ProductDetailsScreen(productId: link.value as int),
      ),
    );
  }
}
```

NOTE: open `product_details_screen.dart` first and match the real constructor signature (named vs positional `productId`, extra required params). If it requires more than `productId`, replicate how `home_screen.dart` line ~450+ navigates to it and use that exact call shape. Record what you found in your report.

- [ ] **Step 4: Implement the four simple widgets**

Create `.../blocks/announcement_bar_block.dart`:

```dart
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
```

Create `.../blocks/info_strip_block.dart`:

```dart
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
      color: const Color(0xFFC5A880).withOpacity(0.12),
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
```

Create `.../blocks/promo_tiles_block.dart`:

```dart
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
                          Positioned(
                            bottom: 6,
                            left: 6,
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
```

Create `.../blocks/category_grid_block.dart`:

```dart
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
```

- [ ] **Step 5: Implement home_blocks_view.dart**

Create `.../blocks/home_blocks_view.dart`:

```dart
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
```

- [ ] **Step 6: Run gates + commit**

Run: `flutter test` → all pass. `flutter analyze <the files you created>` → clean.

```bash
git add "Narzin-app/user/narzin/lib/presentation_layer/main_app_user/home_screens/blocks" "Narzin-app/user/narzin/test/home_blocks_view_test.dart"
git commit -m "feat(app-home): block renderer with announcement, info strip, promo tiles, category circles"
```

---

### Task 4: Hero slider, product rail, countdown banner

**Files:**
- Create: `.../blocks/hero_slider_block.dart`, `product_rail_block.dart`, `countdown_banner_block.dart`
- Modify: `.../blocks/home_blocks_view.dart` (extend the switch)
- Test: extend `Narzin-app/user/narzin/test/home_blocks_view_test.dart`

**Interfaces:**
- Consumes: Task 1 model helpers, `handleBlockLink` (Task 3).
- Produces: `HeroSliderBlock` (PageView, 2:1 aspect, auto-advance 4s via `Timer.periodic` cancelled in dispose, dot indicators, per-slide tap via handleBlockLink, overlay title/subtitle); `ProductRailBlock` (title row + horizontal ListView of compact cards: image 3/4 aspect, locale name via `Localizations.localeOf(context).languageCode == 'ar'`, `€{min_price}` + `{min_price_iqd} IQD`, tap → product details); `CountdownBannerBlock` (Timer-driven DD:HH:MM:SS, hides after expiry, colors from content). All registered in `_buildBlock`.

- [ ] **Step 1: Add failing tests**

Append to `test/home_blocks_view_test.dart` inside `main()`:

```dart
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
```

Note for the countdown widget: its periodic `Timer` MUST be created in `initState` and cancelled in `dispose`, or the test above fails with "Timer is still pending".

- [ ] **Step 2: Run to verify failure** — `flutter test test/home_blocks_view_test.dart` → new tests FAIL.

- [ ] **Step 3: Implement HeroSliderBlock**

Create `.../blocks/hero_slider_block.dart`:

```dart
import 'dart:async';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import 'block_link.dart';

class HeroSliderBlock extends StatefulWidget {
  final Map<String, dynamic> content;
  const HeroSliderBlock({super.key, required this.content});

  @override
  State<HeroSliderBlock> createState() => _HeroSliderBlockState();
}

class _HeroSliderBlockState extends State<HeroSliderBlock> {
  final PageController _controller = PageController();
  Timer? _timer;
  int _active = 0;
  late final List<Map> _slides;

  @override
  void initState() {
    super.initState();
    _slides = (widget.content['slides'] is List
            ? widget.content['slides'] as List
            : [])
        .whereType<Map>()
        .where((s) => s['image'] is String && (s['image'] as String).isNotEmpty)
        .toList();
    if (_slides.length > 1) {
      _timer = Timer.periodic(const Duration(seconds: 4), (_) {
        if (!mounted || !_controller.hasClients) return;
        final next = (_active + 1) % _slides.length;
        _controller.animateToPage(next,
            duration: const Duration(milliseconds: 400),
            curve: Curves.easeOut);
      });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (_slides.isEmpty) return const SizedBox.shrink();
    return AspectRatio(
      aspectRatio: 2,
      child: Stack(
        children: [
          PageView.builder(
            controller: _controller,
            itemCount: _slides.length,
            onPageChanged: (index) => setState(() => _active = index),
            itemBuilder: (context, index) {
              final slide = _slides[index];
              return GestureDetector(
                onTap: () => handleBlockLink(context, slide['link']),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    CachedNetworkImage(
                      imageUrl: slide['image'],
                      fit: BoxFit.cover,
                      errorWidget: (_, __, ___) =>
                          Container(color: const Color(0xFFF7F9FB)),
                    ),
                    if (slide['title'] is String || slide['subtitle'] is String)
                      Container(
                        alignment: AlignmentDirectional.bottomStart,
                        padding: const EdgeInsets.all(14),
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.bottomCenter,
                            end: Alignment.center,
                            colors: [Color(0xB3141923), Colors.transparent],
                          ),
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (slide['title'] is String)
                              Text(slide['title'],
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold)),
                            if (slide['subtitle'] is String)
                              Text(slide['subtitle'],
                                  style: const TextStyle(
                                      color: Color(0xFFC5A880), fontSize: 13)),
                          ],
                        ),
                      ),
                  ],
                ),
              );
            },
          ),
          if (_slides.length > 1)
            Positioned(
              bottom: 8,
              left: 0,
              right: 0,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  for (var i = 0; i < _slides.length; i++)
                    Container(
                      width: i == _active ? 16 : 6,
                      height: 6,
                      margin: const EdgeInsets.symmetric(horizontal: 2),
                      decoration: BoxDecoration(
                        color: i == _active
                            ? Colors.white
                            : Colors.white.withOpacity(0.5),
                        borderRadius: BorderRadius.circular(3),
                      ),
                    ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}
```

- [ ] **Step 4: Implement ProductRailBlock**

Create `.../blocks/product_rail_block.dart`:

```dart
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
                      if (product['min_price'] != null)
                        Text('€${product['min_price']}',
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
```

- [ ] **Step 5: Implement CountdownBannerBlock**

Create `.../blocks/countdown_banner_block.dart`:

```dart
import 'dart:async';

import 'package:flutter/material.dart';

import '../../../../model_layer/home_blocks_model.dart';
import 'block_link.dart';

class CountdownBannerBlock extends StatefulWidget {
  final Map<String, dynamic> content;
  const CountdownBannerBlock({super.key, required this.content});

  @override
  State<CountdownBannerBlock> createState() => _CountdownBannerBlockState();
}

class _CountdownBannerBlockState extends State<CountdownBannerBlock> {
  Timer? _timer;
  Duration _remaining = Duration.zero;
  DateTime? _endsAt;

  @override
  void initState() {
    super.initState();
    _endsAt = DateTime.tryParse('${widget.content['ends_at']}');
    _tick();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  void _tick() {
    if (_endsAt == null) return;
    final remaining = _endsAt!.difference(DateTime.now());
    if (!mounted) return;
    setState(() => _remaining = remaining.isNegative ? Duration.zero : remaining);
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  String _pad(int n) => n.toString().padLeft(2, '0');

  @override
  Widget build(BuildContext context) {
    final text = widget.content['text'];
    if (_endsAt == null ||
        _remaining == Duration.zero ||
        text is! String ||
        text.isEmpty) {
      return const SizedBox.shrink();
    }
    final days = _remaining.inDays;
    final hours = _remaining.inHours % 24;
    final minutes = _remaining.inMinutes % 60;
    final seconds = _remaining.inSeconds % 60;

    return GestureDetector(
      onTap: () => handleBlockLink(context, widget.content['link']),
      child: Container(
        width: double.infinity,
        color: parseHexColor(widget.content['bg_color'], const Color(0xFF141923)),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        child: Column(
          children: [
            Text(text,
                textAlign: TextAlign.center,
                style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: parseHexColor(
                        widget.content['text_color'], const Color(0xFFD4AF37)))),
            const SizedBox(height: 4),
            Directionality(
              textDirection: TextDirection.ltr,
              child: Text(
                '${_pad(days)}:${_pad(hours)}:${_pad(minutes)}:${_pad(seconds)}',
                style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    fontFeatures: const [FontFeature.tabularFigures()],
                    color: parseHexColor(
                        widget.content['text_color'], const Color(0xFFD4AF37))),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

(Import `dart:ui` if `FontFeature` is unresolved: `import 'dart:ui' show FontFeature;`.)

- [ ] **Step 6: Register all three in `home_blocks_view.dart`**

Add imports and switch cases:

```dart
      case 'hero_slider':
        return HeroSliderBlock(content: block.content);
      case 'product_rail':
        return ProductRailBlock(content: block.content);
      case 'countdown_banner':
        return CountdownBannerBlock(content: block.content);
```

- [ ] **Step 7: Gates + commit**

Run: `flutter test` → all pass. `flutter analyze <created/modified files>` → clean.

```bash
git add "Narzin-app/user/narzin/lib/presentation_layer/main_app_user/home_screens/blocks" "Narzin-app/user/narzin/test/home_blocks_view_test.dart"
git commit -m "feat(app-home): hero slider, product rail and countdown blocks"
```

---

### Task 5: HomePopup + home_screen integration

**Files:**
- Create: `.../blocks/home_popup.dart`
- Modify: `Narzin-app/user/narzin/lib/presentation_layer/main_app_user/home_screens/home_screen.dart`, `Narzin-app/user/narzin/lib/main.dart`

**Interfaces:**
- Consumes: `HomeBlocksCubit` (Task 2), `HomePopupGate` (Task 2), `HomeBlocksView` (Tasks 3–4), `handleBlockLink`.
- Produces:
  - `void maybeShowHomePopup(BuildContext context, List<HomeBlock> blocks)` — finds the first `popup` block; if `HomePopupGate.shouldShow`, waits `delay_seconds` then `showModalBottomSheet` with image/title/text/button (button uses handleBlockLink then pops); calls `markShown` when displayed; safe if the context is disposed meanwhile (`context.mounted` check).
  - home_screen integration: `HomeBlocksCubit` provided app-wide in `main.dart` (exactly where BannersCubit is provided — same MultiBlocProvider list); `getHomeBlocks(locale: <current app locale>)` dispatched in the same place `getBanners` is called in home_screen's init; the scrollable body renders `HomeBlocksView(blocks: cubit.blocks)` INSTEAD OF the legacy sections when `state is HomeBlocksLoaded && blocks.isNotEmpty`, and the legacy body otherwise (fallback preserved verbatim); popup trigger fired once after a successful load via `BlocListener`.

- [ ] **Step 1: Implement home_popup.dart**

Create `.../blocks/home_popup.dart`:

```dart
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
```

- [ ] **Step 2: Provide the cubit in main.dart**

Open `Narzin-app/user/narzin/lib/main.dart`, find where `BannersCubit` is provided (a `BlocProvider(create: (context) => BannersCubit())` inside the MultiBlocProvider list) and add alongside it:

```dart
        BlocProvider(create: (context) => HomeBlocksCubit()),
```

with the import `import 'package:narzin/bussiness_logic/home_blocks_cubits/home_blocks_cubit.dart';`.

- [ ] **Step 3: Integrate in home_screen.dart**

In `Narzin-app/user/narzin/lib/presentation_layer/main_app_user/home_screens/home_screen.dart`:

1. Locate the init block where `BlocProvider.of<BannersCubit>(context).getBanners(token: token);` is called (~line 47). Directly after it add:

```dart
    BlocProvider.of<HomeBlocksCubit>(context)
        .getHomeBlocks(locale: locale ?? 'ar');
```

Use the SAME locale variable the file already uses for `locale == 'ar'` checks (inspect how `locale` is obtained there — reuse it; if it's derived inside build, obtain the current language code the same way the file does, e.g. from the localization cubit or `Intl.getCurrentLocale()`; document your choice in the report).

2. Locate the top-level scrollable body of the screen (the widget that contains the banners carousel section and the product sections). Wrap the DECISION — not the whole screen — with a `BlocBuilder<HomeBlocksCubit, HomeBlocksState>`:

```dart
BlocBuilder<HomeBlocksCubit, HomeBlocksState>(
  builder: (context, state) {
    final blocksCubit = context.read<HomeBlocksCubit>();
    if (state is HomeBlocksLoaded && blocksCubit.blocks.isNotEmpty) {
      return HomeBlocksView(blocks: blocksCubit.blocks);
    }
    return <THE EXISTING LEGACY BODY WIDGET — unchanged>;
  },
)
```

Concretely: identify the single widget subtree that renders the legacy home content (banner carousel + categories + product sections) inside the scroll view, extract NOTHING — just wrap that subtree in the builder above with the legacy subtree as the else-branch. Keep pull-to-refresh/app bar/bottom nav outside the builder. This preserves the fallback exactly.

3. Add the popup trigger: wrap the same subtree (or the Scaffold body) in a `BlocListener<HomeBlocksCubit, HomeBlocksState>`:

```dart
BlocListener<HomeBlocksCubit, HomeBlocksState>(
  listener: (context, state) {
    if (state is HomeBlocksLoaded) {
      final blocks = context.read<HomeBlocksCubit>().blocks;
      maybeShowHomePopup(context, blocks);
    }
  },
  child: ...,
)
```

(`maybeShowHomePopup` + `HomePopupGate` guarantee once-per-run even if the listener fires again after locale refetches.)

4. Add the needed imports (HomeBlocksCubit, HomeBlocksView, home_popup).

- [ ] **Step 4: Gates**

Run: `flutter analyze lib/presentation_layer/main_app_user/home_screens lib/main.dart lib/presentation_layer/main_app_user/home_screens/blocks` — no NEW issues introduced by your edits (home_screen.dart may have pre-existing warnings; compare against `git stash`-free baseline by checking the analyzer output mentions only lines you touched — record pre-existing counts in the report).
Run: `flutter test` → all pass.

- [ ] **Step 5: Commit**

```bash
git add "Narzin-app/user/narzin/lib" 
git commit -m "feat(app-home): render server-driven home blocks with legacy fallback and popup"
```

---

### Task 6: Compile verification + wrap-up

**Files:** none new.

- [ ] **Step 1: Full test suite**

Run: `flutter test` → all pass (report totals).

- [ ] **Step 2: Debug build compiles**

Run: `flutter build apk --debug`
Expected: build succeeds (this is the compile-level gate widget tests can't give us). If the toolchain/SDK licenses block the build, record the exact error and return DONE_WITH_CONCERNS instead of fighting the Android SDK.

- [ ] **Step 3: Manual smoke checklist (document for the human)**

For the human with a device/emulator against the live API: home shows admin blocks (or legacy home if API off); hero swipes + auto-advances; rail scrolls; product tap opens details; countdown ticks; popup appears once after delay; Arabic locale shows Arabic names/RTL; pull-to-refresh unaffected.

- [ ] **Step 4: Commit anything pending; report totals.**

---

## Out of scope for this plan

- Category/url link navigation from blocks (needs a routable category-products screen / url_launcher dep) — Phase 4 or fast-follow.
- Removing BannersCubit/legacy home sections — they ARE the fallback; retire only after the feed has proven itself in production (Phase 4 decision).
- iOS build verification (no macOS host here); store submissions.
- Skeleton loaders (spec §7): intentionally superseded — while the feed loads, the app shows the existing legacy home body (real content with its own loading states) rather than skeletons, which is strictly better UX and keeps the fallback path exercised.

