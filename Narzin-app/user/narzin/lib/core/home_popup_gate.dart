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
